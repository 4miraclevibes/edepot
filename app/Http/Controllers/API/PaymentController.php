<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    public function index()
    {
        $payments = Payment::where('user_id', Auth::user()->id)->get();
        return response()->json($payments);
    }

    public function store(Request $request)
    {
        $payment = Payment::create([
            'transaction_id' => $request->transaction_id,
            'status' => 'pending',
            'amount' => $request->amount,
            'code' => $request->code,
            'user_id' => Auth::user()->id
        ]);
        return response()->json($payment);
    }

    public function updatePayment($code)
    {
        try {
            $payment = Payment::where('code', $code)->firstOrFail();

            // Tambahkan validasi tambahan jika diperlukan
            if ($payment->status !== 'pending') {
                return response()->json(['message' => 'Pembayaran tidak dapat diupdate'], 400);
            }

            $payment->update(['status' => 'success']);

            // Logging
            Log::info("Payment updated: {$code}");

            return response()->json([
                'message' => 'Pembayaran berhasil diperbarui',
                'data' => $payment
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::warning("Payment not found: {$code}");
            return response()->json(['message' => 'Pembayaran tidak ditemukan'], 404);
        } catch (\Exception $e) {
            Log::error("Error updating payment: {$code}", ['error' => $e->getMessage()]);
            return response()->json([
                'message' => 'Terjadi kesalahan saat memperbarui pembayaran'
            ], 500);
        }
    }
}
