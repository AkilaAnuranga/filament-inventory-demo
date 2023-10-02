<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

class TransactionProduct extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }


    protected static function booted(): void
    {
        static::created(function ($trans_product) {

            try {


                if($trans_product->transaction->transaction_type == 'sale')
                {
                    $product = Product::find($trans_product->product_id);
                    $product->qty -= $trans_product->qty;
                    $product->save();
                }
                elseif ($trans_product->transaction->transaction_type == 'purchase')
                {
                    $product = Product::find($trans_product->product_id);
                    $product->qty += $trans_product->qty;
                    $product->save();
                }




            }catch (\Exception $e){

            }


        });
    }


}
