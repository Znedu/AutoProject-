<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InventoryAdjustment;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class InventoryController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->query('search');
        $category = $request->query('category');
        $stockStatus = $request->query('stock_status');
        $sortBy = $request->query('sort', 'updated_at');
        $sortDir = $request->query('dir', 'desc');

        $query = Product::query()->search($search);

        if ($category && $category !== 'all') {
            $query->where('category', $category);
        }

        if ($stockStatus === 'in_stock') {
            $query->inStock();
        } elseif ($stockStatus === 'low_stock') {
            $query->lowStock();
        } elseif ($stockStatus === 'out_of_stock') {
            $query->outOfStock();
        }

        $validSorts = ['name', 'sku', 'category', 'unit_price', 'cost_price', 'stock_quantity', 'min_stock_threshold', 'location', 'updated_at'];
        if (in_array($sortBy, $validSorts, true)) {
            $query->orderBy($sortBy, strtolower($sortDir) === 'asc' ? 'asc' : 'desc');
        } else {
            $query->latest();
        }

        $products = $query->paginate(15)->withQueryString();

        $categories = [
            Product::CATEGORY_ENGINE_OIL,
            Product::CATEGORY_TIRES,
            Product::CATEGORY_BRAKES,
            Product::CATEGORY_ACCESSORIES,
            Product::CATEGORY_PAINT,
            Product::CATEGORY_ELECTRICAL,
            Product::CATEGORY_GENERAL,
        ];

        // Critical alerts
        $outOfStockItems = Product::query()->outOfStock()->get();
        $lowStockItems = Product::query()->lowStock()->get();

        $stats = [
            'total_sku' => Product::query()->count(),
            'total_stock_value' => Product::query()->get()->sum(fn ($p) => $p->stock_quantity * $p->unit_price),
            'out_of_stock' => $outOfStockItems->count(),
            'low_stock' => $lowStockItems->count(),
        ];

        return view('admin.inventory.index', compact(
            'products',
            'categories',
            'search',
            'category',
            'stockStatus',
            'sortBy',
            'sortDir',
            'stats',
            'outOfStockItems',
            'lowStockItems'
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'sku' => ['nullable', 'string', 'max:50', 'unique:products,sku'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'category' => ['required', 'string', 'max:100'],
            'cost_price' => ['required', 'numeric', 'min:0'],
            'unit_price' => ['required', 'numeric', 'min:0'],
            'stock_quantity' => ['required', 'integer', 'min:0'],
            'min_stock_threshold' => ['required', 'integer', 'min:0'],
            'location' => ['nullable', 'string', 'max:100'],
            'unit_label' => ['required', 'string', 'max:20'],
            'status' => ['required', Rule::in([Product::STATUS_ACTIVE, Product::STATUS_INACTIVE])],
        ]);

        if (empty($validated['sku'])) {
            $validated['sku'] = 'PRD-' . strtoupper(substr(md5(uniqid('', true)), 0, 8));
        }

        $product = Product::create($validated);

        if ($product->stock_quantity > 0) {
            InventoryAdjustment::create([
                'product_id' => $product->id,
                'user_id' => auth()->id(),
                'type' => 'in',
                'quantity_change' => $product->stock_quantity,
                'quantity_before' => 0,
                'quantity_after' => $product->stock_quantity,
                'reason' => 'Initial Inventory Setup',
                'notes' => 'Product created with initial quantity.',
            ]);
        }

        return redirect()->back()->with('toast', [
            'type' => 'success',
            'message' => "Product '{$product->name}' created successfully!",
        ]);
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $validated = $request->validate([
            'sku' => ['required', 'string', 'max:50', Rule::unique('products', 'sku')->ignore($product->id)],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'category' => ['required', 'string', 'max:100'],
            'cost_price' => ['required', 'numeric', 'min:0'],
            'unit_price' => ['required', 'numeric', 'min:0'],
            'min_stock_threshold' => ['required', 'integer', 'min:0'],
            'location' => ['nullable', 'string', 'max:100'],
            'unit_label' => ['required', 'string', 'max:20'],
            'status' => ['required', Rule::in([Product::STATUS_ACTIVE, Product::STATUS_INACTIVE])],
        ]);

        $product->update($validated);

        return redirect()->back()->with('toast', [
            'type' => 'success',
            'message' => "Product '{$product->name}' updated successfully!",
        ]);
    }

    public function adjustStock(Request $request, Product $product): RedirectResponse
    {
        $validated = $request->validate([
            'type' => ['required', Rule::in(['in', 'out', 'adjustment'])],
            'quantity' => ['required', 'integer', 'min:1'],
            'reason' => ['required', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $qtyBefore = $product->stock_quantity;
        $change = (int) $validated['quantity'];

        if ($validated['type'] === 'out') {
            if ($change > $qtyBefore) {
                return redirect()->back()->with('toast', [
                    'type' => 'error',
                    'message' => "Cannot remove {$change} units. Only {$qtyBefore} available in stock.",
                ]);
            }
            $qtyAfter = $qtyBefore - $change;
            $delta = -$change;
        } elseif ($validated['type'] === 'in') {
            $qtyAfter = $qtyBefore + $change;
            $delta = $change;
        } else {
            // Audit direct set
            $qtyAfter = $change;
            $delta = $qtyAfter - $qtyBefore;
        }

        $product->update(['stock_quantity' => $qtyAfter]);

        InventoryAdjustment::create([
            'product_id' => $product->id,
            'user_id' => auth()->id(),
            'type' => $validated['type'],
            'quantity_change' => $delta,
            'quantity_before' => $qtyBefore,
            'quantity_after' => $qtyAfter,
            'reason' => $validated['reason'],
            'notes' => $validated['notes'],
        ]);

        return redirect()->back()->with('toast', [
            'type' => 'success',
            'message' => "Stock adjusted for '{$product->name}'. New balance: {$qtyAfter} {$product->unit_label}.",
        ]);
    }

    public function destroy(Product $product): RedirectResponse
    {
        $name = $product->name;
        $product->delete();

        return redirect()->back()->with('toast', [
            'type' => 'success',
            'message' => "Product '{$name}' was deleted successfully.",
        ]);
    }
}
