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
            ->with(['transactionDetails.product', 'user', 'payment']) // Tambahkan user
            ->get();
        } else {
            $transactions = Transaction::where('user_id', $user->id)
                ->with(['transactionDetails.product', 'payment', 'user']) // Tambahkan user
                ->get();
        }

        return response()->json([
            'code' => 200,
            'status' => 'success',
            'message' => 'Transaction retrieved successfully',
            'data' => $transactions
        ]);
    }


    public function store()
    {
        $carts = Cart::where('user_id', Auth::user()->id)->get();
        if ($carts->isEmpty()) {
            return response()->json([
                'code' => 404,
                'status' => 'error',
                'message' => 'Cart is empty',
            ], 404);
        }
        $transaction = Transaction::create([
            'user_id' => Auth::user()->id,
            'total_price' => $carts->sum('price'),
        ]);

        Payment::create([
            'transaction_id' => $transaction->id,
            'payment_status' => 'pending',
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
            'data' => $transaction->load('transactionDetails.product', 'payment')
        ]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'payment_status' => 'required|in:processing,completed,cancelled',
        ]);

        try {
        $transaction = Transaction::find($id);
        $transaction->update([
            'status' => $request->status,
        ]);

        return response()->json([
            'code' => 200,
            'status' => 'success',
                'message' => 'Transaction updated successfully',
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
