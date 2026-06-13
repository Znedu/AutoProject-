<?php

namespace App\Enums;

enum Permission: string
{
    // Customer portal
    case ViewCustomerDashboard = 'customer.dashboard.view';
    case BookingsView = 'bookings.view';
    case BookingsCreate = 'bookings.create';
    case BookingsCancel = 'bookings.cancel';
    case PaymentsSubmit = 'payments.submit';
    case TrackingView = 'tracking.view';
    case SupportView = 'support.view';
    case SupportCreate = 'support.create';
    case SupportReply = 'support.reply';
    case ProfileView = 'profile.view';
    case ProfileUpdate = 'profile.update';

    // Staff operations
    case ViewStaffDashboard = 'staff.dashboard.view';
    case BookingQueueView = 'bookings.queue.view';
    case BookingsVerifyPayment = 'bookings.verify-payment';
    case BookingsApprove = 'bookings.approve';
    case BookingsReject = 'bookings.reject';
    case BookingsSchedule = 'bookings.schedule';
    case SupportAssign = 'support.assign';
    case SupportResolve = 'support.resolve';

    // Mechanic workshop
    case ViewMechanicDashboard = 'mechanic.dashboard.view';
    case JobsView = 'jobs.view';
    case JobsStart = 'jobs.start';
    case JobsPause = 'jobs.pause';
    case JobsComplete = 'jobs.complete';
    case JobsUpdateProgress = 'jobs.update-progress';
    case ServiceNotesView = 'service-notes.view';
    case ServiceNotesCreate = 'service-notes.create';

    // Administrator
    case ViewAdminDashboard = 'admin.dashboard.view';
    case UsersManage = 'users.manage';
    case ServicesManage = 'services.manage';
    case ApprovalsManage = 'approvals.manage';
    case ApprovalsAdjustCost = 'approvals.adjust-cost';
    case ReportsView = 'reports.view';
    case ReportsExport = 'reports.export';

    public function group(): string
    {
        return match ($this) {
            self::ViewCustomerDashboard,
            self::BookingsView,
            self::BookingsCreate,
            self::BookingsCancel,
            self::PaymentsSubmit,
            self::TrackingView,
            self::SupportView,
            self::SupportCreate,
            self::SupportReply,
            self::ProfileView,
            self::ProfileUpdate => 'customer',

            self::ViewStaffDashboard,
            self::BookingQueueView,
            self::BookingsVerifyPayment,
            self::BookingsApprove,
            self::BookingsReject,
            self::BookingsSchedule,
            self::SupportAssign,
            self::SupportResolve => 'staff',

            self::ViewMechanicDashboard,
            self::JobsView,
            self::JobsStart,
            self::JobsPause,
            self::JobsComplete,
            self::JobsUpdateProgress,
            self::ServiceNotesView,
            self::ServiceNotesCreate => 'mechanic',

            self::ViewAdminDashboard,
            self::UsersManage,
            self::ServicesManage,
            self::ApprovalsManage,
            self::ApprovalsAdjustCost,
            self::ReportsView,
            self::ReportsExport => 'admin',
        };
    }

    /**
     * @return list<Permission>
     */
    public static function forRole(RoleSlug $role): array
    {
        return match ($role) {
            RoleSlug::Customer => [
                self::ViewCustomerDashboard,
                self::BookingsView,
                self::BookingsCreate,
                self::BookingsCancel,
                self::PaymentsSubmit,
                self::TrackingView,
                self::SupportView,
                self::SupportCreate,
                self::SupportReply,
                self::ProfileView,
                self::ProfileUpdate,
            ],
            RoleSlug::Staff => [
                self::ViewStaffDashboard,
                self::BookingQueueView,
                self::BookingsVerifyPayment,
                self::BookingsApprove,
                self::BookingsReject,
                self::BookingsSchedule,
                self::SupportView,
                self::SupportReply,
                self::SupportAssign,
                self::SupportResolve,
            ],
            RoleSlug::Mechanic => [
                self::ViewMechanicDashboard,
                self::JobsView,
                self::JobsStart,
                self::JobsPause,
                self::JobsComplete,
                self::JobsUpdateProgress,
                self::ServiceNotesView,
                self::ServiceNotesCreate,
            ],
            RoleSlug::Administrator => self::cases(),
        };
    }
}
