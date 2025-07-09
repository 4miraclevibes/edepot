<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = ['transaction_id', 'payment_status', 'amount'];

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }
}
