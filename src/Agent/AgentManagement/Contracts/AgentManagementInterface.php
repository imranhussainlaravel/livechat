<?php

namespace Src\Agent\AgentManagement\Contracts;

use Src\Database\Models\Agent;

interface AgentManagementInterface
{
    public function create(array $data): Agent;
    public function update(int $agentId, array $data): Agent;
    public function setStatus(int $agentId, string $status): Agent;
    public function delete(int $agentId): void;
    public function findAvailable(): \Illuminate\Database\Eloquent\Collection;
}
