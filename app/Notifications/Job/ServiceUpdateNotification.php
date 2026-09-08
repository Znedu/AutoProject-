<?php

namespace App\Notifications\Job;

use App\Enums\NotificationType;
use App\Models\ServiceUpdate;
use App\Notifications\BaseNotification;

class ServiceUpdateNotification extends BaseNotification
{
    public function __construct(
        public ServiceUpdate $update,
        public ?string $stageName = null,
        public ?int $progressPercent = null
    ) {
        // Auto-resolve stageName and progressPercent from job order if not explicitly passed
        if ($this->stageName === null && $this->update->jobOrder) {
            $currentProgress = $this->update->jobOrder->stageProgress()
                ->where('is_current', true)
                ->with('serviceStage')
                ->first();
            $this->stageName = $currentProgress?->serviceStage?->name;
        }

        if ($this->progressPercent === null && $this->update->jobOrder) {
            $this->progressPercent = $this->update->jobOrder->progress_percent;
        }
    }

    public function toArray(mixed $notifiable): array
    {
        $bookingId = $this->update->jobOrder?->booking_id;
        $statusSuffix = $this->stageName ? " - {$this->stageName}" : '';

        $statusPrefix = '';
        if ($this->stageName) {
            $statusPrefix = "Status: {$this->stageName}" . ($this->progressPercent !== null ? " ({$this->progressPercent}%)" : '') . '. ';
        }

        return [
            'type' => NotificationType::SERVICE_UPDATE->value,
            'title' => 'Service Update' . $statusSuffix,
            'message' => "{$statusPrefix}{$this->update->message}",
            'action_url' => route('customer.track', ['booking_id' => $bookingId]),
            'icon' => 'file-text',
            'entity_type' => 'job',
            'entity_id' => $this->update->job_order_id,
        ];
    }

    public function toSms(mixed $notifiable): ?string
    {
        $booking = $this->update->jobOrder?->booking;
        $bookingRef = $booking?->booking_number ? " #{$booking->booking_number}" : '';

        $statusPart = '';
        if ($this->stageName) {
            $statusPart = "Status: {$this->stageName}";
            if ($this->progressPercent !== null) {
                $statusPart .= " ({$this->progressPercent}%)";
            }
        }

        $notePart = ! empty($this->update->message) ? "Note: {$this->update->message}" : '';

        $details = implode(' | ', array_filter([$statusPart, $notePart]));

        if (empty($details)) {
            $details = $this->update->message;
        }

        return "[AutoProject+] Service Update{$bookingRef}: {$details}";
    }
}
