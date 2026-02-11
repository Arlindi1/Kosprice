<?php

namespace App\Features\Fuel\Enums;

enum FuelType: string
{
    case DIESEL = 'diesel';
    case PETROL_95 = 'petrol95';
    case PETROL_98 = 'petrol98';
    case LPG = 'lpg';

    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_map(
            static fn (self $type): string => $type->value,
            self::cases()
        );
    }
}
