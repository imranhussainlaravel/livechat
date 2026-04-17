<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Repositories\Contracts\TicketRepositoryInterface;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    public function __construct(private TicketRepositoryInterface $tickets) {}

    /**
     * GET /admin/tickets — List all tickets in the system.
     */
    public function index(Request $request)
    {
        $tickets = $this->tickets->paginate(20, $request->only('status'));
        return view('admin.tickets.index', compact('tickets'));
    }
}
