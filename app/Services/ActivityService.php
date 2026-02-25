<?php

namespace App\Services;

use App\Repositories\Contracts\ActivityRepositoryInterface;

class ActivityService
{
    public function __construct(
        private ActivityRepositoryInterface $activities,
    ) {}

    public function log(
        ?int    $userId,
        string  $action,
        ?string $referenceType = null,
        ?int    $referenceId = null,
        ?array  $metadata = null,
    ): void {
        $this->activities->log([
            'user_id'        => $userId,
            'action'         => $action,
            'reference_type' => $referenceType,
            'reference_id'   => $referenceId,
            'metadata'       => $metadata,
        ]);
    }
}
