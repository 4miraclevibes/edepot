<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Cart;
use App\Models\Product;

class CartController extends Controller
{
    public function index()
    {
        $carts = Cart::with(['product.user']) // ✅ ambil user (depot) juga
                    ->where('user_id', Auth::user()->id)
                    ->get();

        return response()->json([
            'code' => 200,
            'status' => 'success',
            'message' => 'Cart retrieved successfully',
            'data' => $carts
        ]);
    }


    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        try {
            $product = Product::find($request->product_id);
            $cart = Cart::create([
                'user_id' => Auth::user()->id,
                'product_id' => $product->id,
                'quantity' => $request->quantity,
                'price' => $product->price * $request->quantity,
            ]);
            return response()->json([
                'code' => 200,
                'status' => 'success',
                'message' => 'Cart created successfully',
                'data' => $cart
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'code' => 500,
                'status' => 'error',
                'message' => 'Cart creation failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        try {
            $cart = Cart::find($id);
            $cart->update([
                'quantity' => $request->quantity,
            ]);
            return response()->json([
                'code' => 200,
                'status' => 'success',
                'message' => 'Cart updated successfully',
                'data' => $cart
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'code' => 500,
                'status' => 'error',
                'message' => 'Cart update failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        $cart = Cart::find($id);
        if (!$cart) {
            return response()->json([
                'code' => 404,
                'status' => 'error',
                'message' => 'Cart not found',
            ], 404);
        }
        $cart->delete();
        return response()->json([
            'code' => 200,
            'status' => 'success',
            'message' => 'Cart deleted successfully',
        ]);
    }
}
