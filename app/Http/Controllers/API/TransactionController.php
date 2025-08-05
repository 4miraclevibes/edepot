<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\TransactionDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Cart;
use App\Models\Payment;
use App\Models\Transaction;

class TransactionController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if ($user->role === 'merchant') {
            $transactions = Transaction::whereHas('transactionDetails.product', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->with([
                'transactionDetails.product.user', // << tambahkan ini
                'user',
                'payment'
            ])
            ->get();
        } else {
            $transactions = Transaction::where('user_id', $user->id)
                ->with([
                    'transactionDetails.product.user', // << tambahkan ini
                    'payment',
                    'user'
                ])
                ->get();
        }

        return response()->json([
            'code' => 200,
            'status' => 'success',
            'message' => 'Transaction retrieved successfully',
            'data' => $transactions
        ]);
    }



    public function store(Request $request)
    {
        $request->validate([
            'shipping_fee' => 'nullable|integer|min:0',
        ]);
        $carts = Cart::where('user_id', Auth::user()->id)->get();
        if ($carts->isEmpty()) {
            return response()->json([
                'code' => 404,
                'status' => 'error',
                'message' => 'Cart is empty',
            ], 404);
        }

        $subtotal = $carts->sum('price');
        $shippingFee = $request->shipping_fee ?? 0;

        $transaction = Transaction::create([
            'user_id' => Auth::user()->id,
            'total_price' => $subtotal + $shippingFee,
            'shipping_fee' => $shippingFee,
        ]);
        Payment::create([
            'transaction_id' => $transaction->id,
            'status' => 'pending',
            'amount' => $subtotal + $shippingFee,
            'code' => 'TRX' . rand(100000, 999999),
            'user_id' => Auth::user()->id
        ]);

        foreach ($carts as $cart) {
            TransactionDetail::create([
                'transaction_id' => $transaction->id,
                'product_id' => $cart->product_id,
                'quantity' => $cart->quantity,
                'price' => $cart->price * $cart->quantity,
            ]);
        }

        Cart::where('user_id', Auth::user()->id)->delete();

        return response()->json([
            'code' => 200,
            'status' => 'success',
            'message' => 'Transaction created successfully',
            'data' => $transaction->load('transactionDetails.product.user', 'payment')
        ]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:processing,delivery,completed,cancelled',
        ]);

        try {
            $transaction = Transaction::find($id);

            if (!$transaction) {
                return response()->json([
                    'code' => 404,
                    'status' => 'error',
                    'message' => 'Transaction not found',
                ], 404);
            }

            $transaction->update([
                'status' => $request->status,
            ]);

            return response()->json([
                'code' => 200,
                'status' => 'success',
                'message' => 'Transaction status updated successfully',
                'data' => $transaction
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'code' => 500,
                'status' => 'error',
                'message' => 'Transaction update failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

}
