<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    /** GET /admin/categories?q=landscape — returns JSON list for the search dropdown */
    public function index(Request $request): JsonResponse
    {
        $query = Category::orderBy('name');

        if ($q = $request->query('q')) {
            $query->where('name', 'like', '%' . $q . '%');
        }

        return response()->json(
            $query->limit(30)->get(['id', 'name', 'slug'])
        );
    }

    /** POST /admin/categories — create a new category on-the-fly */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:categories,name'],
        ]);

        $category = Category::create([
            'name' => $data['name'],
            'slug' => Str::slug($data['name']),
        ]);

        return response()->json([
            'id'   => $category->id,
            'name' => $category->name,
            'slug' => $category->slug,
        ], 201);
    }
}
