<?php

namespace App\Enums;

enum QuotationStatus: string
{
    case Draft = 'draft';
    case Sent = 'sent';
    case Approved = 'approved';
    case Rejected = 'rejected';

    public function getLabel(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Sent => 'Sent',
            self::Approved => 'Approved by client',
            self::Rejected => 'Rejected',
        };
    }

    /** Semantic color token (mapped to Tailwind classes in the views). */
    public function getColor(): string
    {
        return match ($this) {
            self::Draft => 'gray',
            self::Sent => 'info',
            self::Approved => 'success',
            self::Rejected => 'danger',
        };
    }
}
