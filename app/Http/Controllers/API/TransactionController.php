<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\TransactionDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Cart;
use App\Models\Payment;
use App\Models\Transaction;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\Product;

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
                'transactionDetails.product.user',
                'user',
                'payment'
            ])
            ->get();
        } else {
            $transactions = Transaction::where('user_id', $user->id)
                ->with([
                    'transactionDetails.product.user',
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

        try {
            DB::beginTransaction();

            $transaction = Transaction::create([
                'user_id' => Auth::user()->id,

                'total_price' => $subtotal + $shippingFee,
                'shipping_fee' => $shippingFee,

            ]);

            $payment = Payment::create([
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
            $merchantEmail = Product::where('id', $carts->product_id)->first()->user->email;
            dd($merchantEmail);

            $totalPrice = $transaction->total_price;

            $edupayResponse = $this->edupayCreatePayment(
                $payment->code,
                $totalPrice,
                $merchantEmail
            );

            if (!$edupayResponse) {

                DB::rollBack();

                return response()->json([
                    'message' => 'Gagal membuat payment di EduPay. Silakan coba lagi.',
                    'error' => 'EDUPAY_API_ERROR',
                    'hint' => 'Terjadi kesalahan saat menghubungi payment gateway'
                ], 500);
            }

            Cart::where('user_id', Auth::user()->id)->delete();

            DB::commit();

            return response()->json([
                'code' => 200,
                'status' => 'success',
                'message' => 'Transaction created successfully',
                'data' => $transaction->load('transactionDetails.product', 'payment')
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'code' => 500,
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat membuat transaksi',
                'error' => $e->getMessage()
            ], 500);
        }
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

    private function edupayCreatePayment($code, $total, $email)
    {
        try {
            $response = Http::post('https://edupay.justputoff.com/api/service/storePayment', [
                'service_id' => 9, // Service ID untuk Sportlodek
                'total' => $total,
                'code' => $code,
                'email' => $email,
            ]);

            if ($response->successful()) {
                return $response->json();
            } else {
                Log::error('EduPay API Error', [
                    'status' => $response->status(),
                    'response' => $response->json(),
                    'request' => [
                        'service_id' => 9,
                        'total' => $total,
                        'code' => $code,
                        'email' => $email,
                    ]
                ]);

                return null;
            }
        } catch (\Exception $e) {
            Log::error('EduPay API Exception', [
                'message' => $e->getMessage(),
                'request' => [
                    'service_id' => 9,
                    'total' => $total,
                    'code' => $code,
                    'email' => $email,
                ]
            ]);

            return null;
        }
    }
}
