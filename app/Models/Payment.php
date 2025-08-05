<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = ['transaction_id', 'status', 'amount', 'code', 'user_id'];

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }
}
