<?php

namespace App\Enums;

enum RoleSlug: string
{
    case Customer = 'customer';
    case Staff = 'staff';
    case Mechanic = 'mechanic';
    case Administrator = 'admin';

    public function label(): string
    {
        return match ($this) {
            self::Customer => 'Customer',
            self::Staff => 'Staff',
            self::Mechanic => 'Mechanic',
            self::Administrator => 'Administrator',
        };
    }

    public function dashboardPath(): string
    {
        return match ($this) {
            self::Customer => '/customer',
            self::Staff => '/staff',
            self::Mechanic => '/mechanic',
            self::Administrator => '/admin',
        };
    }

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
