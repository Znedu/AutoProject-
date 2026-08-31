<?php

namespace App\Http\Controllers\Customer;

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
            ->active()
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

        $products = $query->orderBy('name')->paginate(12)->withQueryString();

        $categories = Product::query()
            ->active()
            ->distinct()
            ->pluck('category')
            ->sort()
            ->values();

        $stats = [
            'total' => Product::query()->active()->count(),
            'in_stock' => Product::query()->active()->inStock()->count(),
            'low_stock' => Product::query()->active()->lowStock()->count(),
            'out_of_stock' => Product::query()->active()->outOfStock()->count(),
        ];

        return view('customer.inventory.index', compact(
            'products',
            'categories',
            'search',
            'category',
            'stockStatus',
            'stats'
        ));
    }
}
