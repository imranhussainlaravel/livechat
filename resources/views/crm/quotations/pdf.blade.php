<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        * { font-family: DejaVu Sans, sans-serif; }
        body { color: #1f2937; font-size: 12px; margin: 0; padding: 32px; }
        .head { display: flex; justify-content: space-between; border-bottom: 3px solid #6366F1; padding-bottom: 16px; margin-bottom: 24px; }
        .brand { font-size: 22px; font-weight: bold; color: #6366F1; }
        .muted { color: #6b7280; font-size: 11px; }
        .title { font-size: 16px; font-weight: bold; margin: 0 0 4px; }
        table { width: 100%; border-collapse: collapse; margin-top: 16px; }
        th { background: #f3f4f6; text-align: left; padding: 8px 10px; font-size: 10px; text-transform: uppercase; letter-spacing: .05em; color: #6b7280; border-bottom: 2px solid #e5e7eb; }
        td { padding: 8px 10px; border-bottom: 1px solid #eee; }
        .right { text-align: right; }
        .totals { margin-top: 16px; width: 40%; float: right; }
        .totals td { border: none; padding: 4px 10px; }
        .grand { font-size: 15px; font-weight: bold; color: #111827; border-top: 2px solid #e5e7eb; }
        .foot { clear: both; margin-top: 60px; color: #9ca3af; font-size: 10px; text-align: center; border-top: 1px solid #eee; padding-top: 12px; }
    </style>
</head>
<body>
    @php
        $company = $quotation->deal?->lead?->contact?->company;
        $contact = $quotation->deal?->lead?->contact;
        $money = fn ($v) => 'PKR ' . number_format((float) $v, 2);
    @endphp
    <div class="head">
        <div>
            <div class="brand">Nexon Packaging</div>
            <div class="muted">Quotation</div>
        </div>
        <div style="text-align:right;">
            <div class="title">Quotation #{{ $quotation->id }}</div>
            <div class="muted">Version {{ $quotation->version }}</div>
            <div class="muted">Status: {{ $quotation->status?->getLabel() ?? ucfirst($quotation->status) }}</div>
        </div>
    </div>

    <table style="margin-top:0;">
        <tr>
            <td style="border:none; vertical-align:top; width:50%;">
                <strong>Billed to</strong><br>
                {{ $company?->name ?? '—' }}<br>
                @if($contact){{ $contact->name }}@if($contact->email) · {{ $contact->email }}@endif<br>@endif
                @if($contact?->phone){{ $contact->phone }}@endif
            </td>
            <td style="border:none; vertical-align:top; text-align:right;">
                <span class="muted">Deal #{{ $quotation->deal_id }}</span><br>
                <span class="muted">Prepared by {{ $quotation->createdBy?->name ?? 'System' }}</span>
            </td>
        </tr>
    </table>

    <table>
        <thead>
            <tr>
                <th>Product</th>
                <th class="right">Qty</th>
                <th class="right">Unit Price</th>
                <th class="right">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($quotation->items as $item)
            <tr>
                <td>{{ $item->product?->name ?? 'Product #'.$item->product_id }}</td>
                <td class="right">{{ number_format($item->quantity) }}</td>
                <td class="right">{{ $money($item->unit_price) }}</td>
                <td class="right">{{ $money($item->subtotal()) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <table class="totals">
        <tr>
            <td>Discount</td>
            <td class="right">{{ number_format((float) $quotation->discount_percent, 2) }}%</td>
        </tr>
        <tr>
            <td class="grand">Total</td>
            <td class="right grand">{{ $money($quotation->total_value) }}</td>
        </tr>
    </table>

    <div class="foot">
        This is a system-generated quotation from Nexon Packaging. Prices are subject to confirmation.
    </div>
</body>
</html>
