<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Contact;
use App\Models\Deal;
use App\Models\Lead;
use App\Models\LeadActivity;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductPriceTier;
use App\Models\Quotation;
use App\Models\User;
use Illuminate\Database\Seeder;

class DemoCrmSeeder extends Seeder
{
    public function run(): void
    {
        $agent = User::where('email', 'agent@livechat.com')->first();
        $admin = User::where('email', 'admin@livechat.com')->first();
        $agentId = $agent?->id ?? $admin?->id;

        // Give the demo agent a work scope so lead creation is allowed.
        if ($agent) {
            $agent->update(['work_scope' => 'full_cycle', 'account_status' => 'active']);
        }

        // ── Products (+ price tiers) ────────────────────────────────
        $products = [
            ['name' => 'Corrugated Box (Small)', 'type' => 'boxes', 'material' => '3-ply Kraft', 'size_options' => '6x6x6 in', 'moq' => 500, 'base_price' => 22.50,
             'tiers' => [[500, 22.50], [2000, 19.00], [5000, 16.50]]],
            ['name' => 'Stand-up Pouch', 'type' => 'pouches', 'material' => 'Matte PET/PE', 'size_options' => '100g / 250g / 500g', 'moq' => 1000, 'base_price' => 14.00,
             'tiers' => [[1000, 14.00], [5000, 11.50]]],
            ['name' => 'Product Label Roll', 'type' => 'labels', 'material' => 'Vinyl', 'size_options' => 'Custom', 'moq' => 250, 'base_price' => 6.75,
             'tiers' => [[250, 6.75], [1000, 5.25]]],
            ['name' => 'Retail Carton', 'type' => 'cartons', 'material' => 'SBS Board', 'size_options' => 'Custom', 'moq' => 300, 'base_price' => 31.00, 'tiers' => []],
        ];

        $productModels = [];
        foreach ($products as $p) {
            $tiers = $p['tiers'];
            unset($p['tiers']);
            $product = Product::firstOrCreate(['name' => $p['name']], $p);
            foreach ($tiers as [$min, $price]) {
                ProductPriceTier::firstOrCreate(
                    ['product_id' => $product->id, 'min_quantity' => $min],
                    ['unit_price' => $price]
                );
            }
            $productModels[] = $product;
        }

        // ── Companies + Contacts ────────────────────────────────────
        $seed = [
            ['company' => 'Fresh Foods Ltd', 'city' => 'Karachi', 'contact' => 'Ayesha Khan', 'email' => 'ayesha@freshfoods.pk', 'phone' => '+92 300 1112233', 'designation' => 'Procurement Lead', 'interest' => 'pouches', 'status' => 'qualified'],
            ['company' => 'BrightMart Retail', 'city' => 'Lahore', 'contact' => 'Bilal Ahmed', 'email' => 'bilal@brightmart.pk', 'phone' => '+92 321 4455667', 'designation' => 'Owner', 'interest' => 'cartons', 'status' => 'quoted'],
            ['company' => 'GreenLeaf Organics', 'city' => 'Islamabad', 'contact' => 'Sara Malik', 'email' => 'sara@greenleaf.pk', 'phone' => '+92 333 7788990', 'designation' => 'Brand Manager', 'interest' => 'labels', 'status' => 'new'],
            ['company' => 'Metro Distributors', 'city' => 'Faisalabad', 'contact' => 'Hamza Raza', 'email' => 'hamza@metro.pk', 'phone' => '+92 301 2223344', 'designation' => 'Supply Chain', 'interest' => 'boxes', 'status' => 'won'],
        ];

        foreach ($seed as $row) {
            $company = Company::firstOrCreate(['name' => $row['company']], ['city' => $row['city'], 'industry_notes' => 'Demo seed record.']);
            $contact = Contact::firstOrCreate(
                ['company_id' => $company->id, 'name' => $row['contact']],
                ['email' => $row['email'], 'phone' => $row['phone'], 'designation' => $row['designation']]
            );

            $lead = Lead::firstOrCreate(
                ['contact_id' => $contact->id],
                [
                    'source' => 'website',
                    'status' => $row['status'],
                    'product_interest' => $row['interest'],
                    'assigned_agent_id' => $agentId,
                    'follow_up_date' => now()->addDays(3),
                    'follow_up_note' => 'Demo follow-up.',
                ]
            );

            LeadActivity::firstOrCreate(
                ['lead_id' => $lead->id, 'type' => 'note'],
                ['user_id' => $agentId, 'note' => 'Lead seeded for demo.']
            );

            // For quoted/won leads, create a deal (and an order for won).
            if (in_array($row['status'], ['quoted', 'won'], true) && ! $lead->deal) {
                $deal = Deal::create([
                    'lead_id' => $lead->id,
                    'sales_rep_id' => $agentId,
                    'stage' => $row['status'] === 'won' ? 'won' : 'quoted',
                    'value' => $row['status'] === 'won' ? 185000 : 92000,
                    'expected_close_date' => now()->addDays(14),
                    'probability' => $row['status'] === 'won' ? 100 : 60,
                ]);

                // A quotation with two line items.
                $quotation = Quotation::create([
                    'deal_id' => $deal->id,
                    'version' => 1,
                    'status' => $row['status'] === 'won' ? 'approved' : 'sent',
                    'created_by' => $agentId,
                ]);
                $quotation->items()->create(['product_id' => $productModels[0]->id, 'quantity' => 2000, 'unit_price' => 19.00]);
                $quotation->items()->create(['product_id' => $productModels[1]->id, 'quantity' => 5000, 'unit_price' => 11.50]);
                $quotation->recalculateTotals();

                if ($row['status'] === 'won') {
                    Order::firstOrCreate(
                        ['deal_id' => $deal->id],
                        ['status' => 'in_production', 'deadline' => now()->addDays(21), 'special_instructions' => 'Rush order — demo.']
                    );
                }
            }
        }
    }
}
