<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

/**
 * Temporary placeholder for the CRM modules.
 *
 * Phase 3+ replaces these actions with real Leads / Deals / Contacts /
 * Companies / Products / Orders controllers. The route names (crm.*.index)
 * stay stable so the sidebar links do not change.
 */
class CrmController extends Controller
{
    public function leads(Request $request)
    {
        return $this->soon('Leads', 'Capture and qualify sales leads from chats and other sources.');
    }

    public function deals(Request $request)
    {
        return $this->soon('Deals', 'Track opportunities through your sales pipeline.');
    }

    public function contacts(Request $request)
    {
        return $this->soon('Contacts', 'People you do business with, linked to companies and leads.');
    }

    public function companies(Request $request)
    {
        return $this->soon('Companies', 'Organisations and their contacts.');
    }

    public function products(Request $request)
    {
        return $this->soon('Products', 'Your product catalogue and price tiers.');
    }

    public function orders(Request $request)
    {
        return $this->soon('Orders', 'Confirmed orders created from won deals, through to dispatch.');
    }

    private function soon(string $module, string $blurb)
    {
        return view('crm.placeholder', compact('module', 'blurb'));
    }
}
