<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function search(Request $request): JsonResponse
    {
        $q = trim((string) $request->query('q', ''));
        $category = $request->query('category');

        $products = Product::query()
            ->active()
            ->when($category && !in_array(strtolower($category), ['product', 'material', 'all'], true), function ($query) use ($category) {
                $query->where('category', $category);
            })
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('name', 'like', "%{$q}%")
                        ->orWhere('sku', 'like', "%{$q}%")
                        ->orWhere('category', 'like', "%{$q}%")
                        ->orWhere('description', 'like', "%{$q}%");
                });
            })
            ->orderBy('name')
            ->limit(100)
            ->get(['id', 'sku', 'name', 'category', 'unit_price', 'unit_label', 'stock_quantity']);

        return response()->json($products);
    }
}
