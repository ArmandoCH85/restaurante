<?php

namespace App\Enums;

enum ServiceTypeEnum: string
{
    case DINE_IN = 'dine_in';
    case TAKEOUT = 'takeout';
    case DELIVERY = 'delivery';
    case SELF_SERVICE = 'self_service';

    public function getLabel(): string
    {
        return match ($this) {
            self::DINE_IN => 'Mesa',
            self::TAKEOUT => 'Para Llevar',
            self::DELIVERY => 'Delivery',
            self::SELF_SERVICE => 'Autoservicio',
        };
    }
}