<?php

namespace App\Http\Controllers\Crm;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::query()
            ->with(['deal.lead.contact.company', 'dispatch'])
            ->latest();

        $this->scopeToUser($query);

        if ($request->filled('status')) {
            $status = OrderStatus::tryFrom($request->query('status'));
            if ($status) {
                $query->where('status', $status->value);
            }
        }

        $orders = $query->paginate(15)->withQueryString();

        return view('crm.orders.index', [
            'orders' => $orders,
            'statuses' => OrderStatus::cases(),
            'activeStatus' => $request->query('status'),
        ]);
    }

    public function show(Order $order)
    {
        $this->authorizeAccess($order);

        $order->load(['deal.lead.contact.company', 'deal.salesRep', 'dispatch']);

        return view('crm.orders.show', compact('order'));
    }

    public function edit(Order $order)
    {
        $this->authorizeAccess($order);

        $order->load(['deal.lead.contact.company', 'dispatch']);

        return view('crm.orders.edit', [
            'order' => $order,
            'statuses' => OrderStatus::cases(),
        ]);
    }

    public function update(Request $request, Order $order)
    {
        $this->authorizeAccess($order);

        $data = $request->validate([
            'status' => ['required', 'string', 'in:' . implode(',', array_column(OrderStatus::cases(), 'value'))],
            'deadline' => ['nullable', 'date'],
            'special_instructions' => ['nullable', 'string'],
            'delivered_at' => ['nullable', 'date'],
            'vehicle_info' => ['nullable', 'string', 'max:255'],
            'dispatch_date' => ['nullable', 'date'],
            'delivery_address' => ['nullable', 'string'],
            'invoice_no' => ['nullable', 'string', 'max:255'],
        ]);

        $order->update([
            'status' => $data['status'],
            'deadline' => $data['deadline'] ?? null,
            'special_instructions' => $data['special_instructions'] ?? null,
            'delivered_at' => $data['delivered_at'] ?? null,
        ]);

        $dispatchFields = [
            'vehicle_info' => $data['vehicle_info'] ?? null,
            'dispatch_date' => $data['dispatch_date'] ?? null,
            'delivery_address' => $data['delivery_address'] ?? null,
            'invoice_no' => $data['invoice_no'] ?? null,
        ];

        $hasDispatchData = collect($dispatchFields)->filter(fn ($v) => $v !== null && $v !== '')->isNotEmpty();

        if ($hasDispatchData) {
            $order->dispatch()->updateOrCreate([], $dispatchFields);
        }

        return redirect()->route('crm.orders.show', $order)->with('success', 'Order updated.');
    }

    /**
     * Agents only see orders for their own deals; admins and production see all.
     */
    protected function scopeToUser($query): void
    {
        $user = auth()->user();

        if ($user->isAdmin() || $user->isProduction()) {
            return;
        }

        $query->whereHas('deal', fn ($q) => $q->where('sales_rep_id', $user->id));
    }

    /**
     * Abort with 403 if an agent tries to access an order that is not on one of their deals.
     */
    protected function authorizeAccess(Order $order): void
    {
        $user = auth()->user();

        if ($user->isAdmin() || $user->isProduction()) {
            return;
        }

        if ($order->deal?->sales_rep_id !== $user->id) {
            abort(403);
        }
    }
}
