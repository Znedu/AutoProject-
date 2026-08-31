<?php

namespace App\Http\Controllers\Mechanic;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InventoryController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->query('search');
        $category = $request->query('category');
        $stockStatus = $request->query('stock_status');

        $query = Product::query()
            ->search($search);

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

        $products = $query->orderBy('location')->orderBy('name')->paginate(15)->withQueryString();

        $categories = Product::query()
            ->distinct()
            ->pluck('category')
            ->sort()
            ->values();

        $stats = [
            'total_items' => Product::query()->count(),
            'total_units' => (int) Product::query()->sum('stock_quantity'),
            'low_stock_count' => Product::query()->lowStock()->count(),
            'out_of_stock_count' => Product::query()->outOfStock()->count(),
        ];

        return view('mechanic.inventory.index', compact(
            'products',
            'categories',
            'search',
            'category',
            'stockStatus',
            'stats'
        ));
    }
}
