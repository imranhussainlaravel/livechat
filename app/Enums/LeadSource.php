<?php

namespace App\Enums;

enum LeadSource: string
{
    case ColdCall = 'cold_call';
    case Referral = 'referral';
    case Website = 'website';
    case Exhibition = 'exhibition';
    case Other = 'other';

    public function getLabel(): string
    {
        return match ($this) {
            self::ColdCall => 'Cold Call',
            self::Referral => 'Referral',
            self::Website => 'Website',
            self::Exhibition => 'Exhibition',
            self::Other => 'Other',
        };
    }
}
