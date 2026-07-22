<?php

namespace App\Http\Controllers\Crm;

use App\Enums\ProductInterest;
use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::withCount('priceTiers')->orderBy('name')->paginate(15);

        return view('crm.products.index', compact('products'));
    }

    public function create()
    {
        return view('crm.products.create');
    }

    public function store(Request $request)
    {
        $data = $this->validateProduct($request);

        $product = Product::create([
            'name' => $data['name'],
            'type' => $data['type'],
            'material' => $data['material'] ?? null,
            'size_options' => $data['size_options'] ?? null,
            'moq' => $data['moq'],
            'base_price' => $data['base_price'],
        ]);

        $this->syncTiers($request, $product);

        return redirect()->route('crm.products.index')->with('success', 'Product created.');
    }

    public function show(Product $product)
    {
        $product->load('priceTiers');

        return view('crm.products.show', compact('product'));
    }

    public function edit(Product $product)
    {
        $product->load('priceTiers');

        return view('crm.products.edit', compact('product'));
    }

    public function update(Request $request, Product $product)
    {
        $data = $this->validateProduct($request);

        $product->update([
            'name' => $data['name'],
            'type' => $data['type'],
            'material' => $data['material'] ?? null,
            'size_options' => $data['size_options'] ?? null,
            'moq' => $data['moq'],
            'base_price' => $data['base_price'],
        ]);

        // Simple sync: drop existing tiers and recreate from submitted rows.
        $product->priceTiers()->delete();
        $this->syncTiers($request, $product);

        return redirect()->route('crm.products.index')->with('success', 'Product updated.');
    }

    public function destroy(Product $product)
    {
        $product->delete();

        return redirect()->route('crm.products.index')->with('success', 'Product deleted.');
    }

    private function validateProduct(Request $request): array
    {
        return $request->validate([
            'name' => 'required|string|max:255',
            'type' => ['required', Rule::in(array_column(ProductInterest::cases(), 'value'))],
            'material' => 'nullable|string|max:255',
            'size_options' => 'nullable|string|max:255',
            'moq' => 'required|integer|min:1',
            'base_price' => 'required|numeric|min:0',
            'tiers' => 'nullable|array',
            'tiers.*.min_quantity' => 'nullable|integer|min:1',
            'tiers.*.unit_price' => 'nullable|numeric|min:0',
        ]);
    }

    private function syncTiers(Request $request, Product $product): void
    {
        foreach ((array) $request->input('tiers', []) as $tier) {
            $min = $tier['min_quantity'] ?? null;
            $price = $tier['unit_price'] ?? null;

            if ($min === null || $min === '' || $price === null || $price === '') {
                continue;
            }

            $product->priceTiers()->create([
                'min_quantity' => (int) $min,
                'unit_price' => (float) $price,
            ]);
        }
    }
}
