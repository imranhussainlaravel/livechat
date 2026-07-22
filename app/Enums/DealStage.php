<?php

namespace App\Enums;

enum DealStage: string
{
    case Quoted = 'quoted';
    case Negotiation = 'negotiation';
    case Won = 'won';
    case Lost = 'lost';

    public function getLabel(): string
    {
        return match ($this) {
            self::Quoted => 'Quoted',
            self::Negotiation => 'Negotiation',
            self::Won => 'Won',
            self::Lost => 'Lost',
        };
    }

    /** Semantic color token (mapped to Tailwind classes in the views). */
    public function getColor(): string
    {
        return match ($this) {
            self::Quoted => 'warning',
            self::Negotiation => 'warning',
            self::Won => 'success',
            self::Lost => 'danger',
        };
    }

    /** @return list<self> */
    public static function pipelineOrder(): array
    {
        return [self::Quoted, self::Negotiation, self::Won, self::Lost];
    }
}
