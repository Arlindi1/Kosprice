<?php

namespace App\Features\Products\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductController
{
    public function index(Request $request)
    {
        $category = $request->query('category');

        $query = DB::table('products')->select('id','name','category')
            ->when($category, fn($q) => $q->where('category', $category))
            ->orderBy('category')->orderBy('name');

        $items = $query->get();

        return response()->json([
            'data' => $items,
            'meta' => ['count' => $items->count()],
        ]);
    }
}
