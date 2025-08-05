<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Product;
class ProductController extends Controller
{
    public function index()
    {
        $products = Product::with('user')
            ->whereHas('user', function ($query) {
                $query->where('role', 'merchant')
                    ->where('is_open', true);
            })
            ->get();

        return response()->json([
            'code' => 200,
            'status' => 'success',
            'message' => 'List produk dari depot yang sedang buka',
            'data' => $products,
        ]);
    }


    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'price' => 'required',
        ]);

        try {
            $product = Product::create(
                [
                    'name' => $request->name,
                    'price' => $request->price,
                    'user_id' => Auth::user()->id,
                ]
            );
            return response()->json([
                'code' => 200,
                'status' => 'success',
                'message' => 'Product created successfully',
                'data' => $product
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'code' => 500,
                'status' => 'error',
                'message' => 'Product creation failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
{
    $product = Product::with('user')->find($id);

    if (!$product) {
        return response()->json([
            'code' => 404,
            'status' => 'error',
            'message' => 'Produk tidak ditemukan',
        ], 404);
    }

    return response()->json([
        'code' => 200,
        'status' => 'success',
        'data' => $product,
    ], 200);
}


    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required',
            'price' => 'required|numeric|min:0',
        ]);

        try {
            $product = Product::find($id);
            $product->update($request->all());
            return response()->json($product);
        } catch (\Exception $e) {
            return response()->json([
                'code' => 500,
                'status' => 'error',
                'message' => 'Product update failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        $product = Product::find($id);
        $product->delete();
        return response()->json([
            'code' => 200,
            'status' => 'success',
            'message' => 'Product deleted successfully',
        ]);
    }
}
