<?php

namespace App\Services\Notification;

use App\Models\User;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Collection;

class NotificationDispatcherService
{
    /**
     * Notify a single user if active.
     */
    public function notifyUser(User $user, Notification $notification): void
    {
        if ($user->isActive()) {
            $user->notify($notification);
        }
    }

    /**
     * Notify multiple users (Collection or array), skipping inactive users.
     *
     * @param  Collection<int, User>|array<int, User>  $users
     */
    public function notifyUsers(Collection|array $users, Notification $notification): void
    {
        foreach ($users as $user) {
            if ($user instanceof User && $user->isActive()) {
                $user->notify($notification);
            }
        }
    }

    /**
     * Notify all active administrators.
     */
    public function notifyAdmins(Notification $notification): void
    {
        $this->notifyUsers(User::active()->admins()->get(), $notification);
    }

    /**
     * Notify all active staff members.
     */
    public function notifyStaff(Notification $notification): void
    {
        $this->notifyUsers(User::active()->staff()->get(), $notification);
    }

    /**
     * Notify all active administrators holding a specific permission.
     */
    public function notifyAdminsWithPermission(string $permission, Notification $notification): void
    {
        $admins = User::active()
            ->admins()
            ->get()
            ->filter(fn (User $admin) => $admin->hasPermission($permission));

        $this->notifyUsers($admins, $notification);
    }
}
