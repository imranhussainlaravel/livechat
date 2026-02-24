<?php

namespace Src\Api\Controllers;

use Illuminate\Http\Request;
use Src\Agent\AgentManagement\AgentManagement;

class AgentCrudController extends ApiController
{
    public function __construct(private readonly AgentManagement $agents) {}

    public function index()
    {
        return $this->success(\Src\Database\Models\Agent::all());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'            => 'required|string|max:255',
            'email'           => 'required|email|unique:agents',
            'password'        => 'required|string|min:6',
            'max_concurrency' => 'integer|min:1|max:50',
        ]);

        $agent = $this->agents->create($data);
        return $this->created($agent);
    }

    public function show(int $id)
    {
        return $this->success(\Src\Database\Models\Agent::findOrFail($id));
    }

    public function update(Request $request, int $id)
    {
        $data = $request->validate([
            'name'            => 'string|max:255',
            'email'           => "email|unique:agents,email,{$id}",
            'password'        => 'string|min:6',
            'max_concurrency' => 'integer|min:1|max:50',
        ]);

        $agent = $this->agents->update($id, $data);
        return $this->success($agent);
    }

    public function destroy(int $id)
    {
        $this->agents->delete($id);
        return $this->noContent();
    }
}
