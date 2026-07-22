<?php

namespace App\Http\Controllers\Crm;

use App\Enums\QuotationStatus;
use App\Http\Controllers\Controller;
use App\Models\Deal;
use App\Models\Product;
use App\Models\Quotation;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class QuotationController extends Controller
{
    public function create()
    {
        $deal = Deal::with('lead.contact.company')->findOrFail(request('deal'));
        $products = Product::orderBy('name')->get();
        $version = $deal->quotations()->count() + 1;

        return view('crm.quotations.create', compact('deal', 'products', 'version'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'deal_id' => 'required|exists:deals,id',
            'status' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        $deal = Deal::findOrFail($data['deal_id']);
        $version = $deal->quotations()->count() + 1;

        $quotation = Quotation::create([
            'deal_id' => $deal->id,
            'version' => $version,
            'status' => 'draft',
            'total_value' => 0,
            'discount_percent' => 0,
            'created_by' => auth()->id(),
        ]);

        foreach ($data['items'] as $item) {
            $quotation->items()->create([
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
            ]);
        }

        $quotation->recalculateTotals();

        return redirect()->route('crm.quotations.show', $quotation)->with('success', 'Quotation created.');
    }

    public function show(Quotation $quotation)
    {
        $quotation->load('deal.lead.contact.company', 'items.product', 'createdBy', 'discountApprovedBy');

        return view('crm.quotations.show', compact('quotation'));
    }

    public function edit(Quotation $quotation)
    {
        $quotation->load('deal.lead.contact.company', 'items.product');
        $products = Product::orderBy('name')->get();

        return view('crm.quotations.edit', compact('quotation', 'products'));
    }

    public function update(Request $request, Quotation $quotation)
    {
        $data = $request->validate([
            'status' => 'required|string',
            'items' => 'nullable|array',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        $quotation->update(['status' => $data['status']]);

        if (! empty($data['items'])) {
            $quotation->items()->delete();

            foreach ($data['items'] as $item) {
                $quotation->items()->create([
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                ]);
            }
        }

        $quotation->recalculateTotals();

        return redirect()->route('crm.quotations.show', $quotation)->with('success', 'Quotation updated.');
    }

    public function destroy(Quotation $quotation)
    {
        $dealId = $quotation->deal_id;
        $quotation->delete();

        return redirect()->route('crm.deals.show', $dealId)->with('success', 'Quotation deleted.');
    }

    public function approveDiscount(Quotation $quotation)
    {
        if (! auth()->user()->isAdmin()) {
            abort(403);
        }

        $quotation->update([
            'discount_approved_by' => auth()->id(),
            'discount_approved_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Discount approved.');
    }

    public function pdf(Quotation $quotation)
    {
        return Pdf::loadView('crm.quotations.pdf', [
            'quotation' => $quotation->load('deal.lead.contact.company', 'items.product', 'createdBy'),
        ])->stream("quotation-{$quotation->id}.pdf");
    }
}
