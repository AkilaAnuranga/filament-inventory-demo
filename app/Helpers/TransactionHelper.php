<?php

namespace App\Helpers;

use App\Models\Product;

class TransactionHelper
{

    public static function calculate_sub_total(Product $product, $qty, $discount = 0)
    {
        //

        if ($product->special_price != null ||
            $product->special_price != 0) {
            $price = number_format($product->special_price, '2', '.', '');
        } else {
            $price = number_format($product->price, '2', '.', '');
        }

        $subTotal = number_format($price * $qty * (1 - $discount / 100), '2', '.', '');

        return $subTotal;

    }

    public static function product_selling_price(Product $product)
    {


        if ($product->special_price != null ||
            $product->special_price != 0) {
            $price = number_format($product->special_price, '2', '.', '');
        } else {
            $price = number_format($product->price, '2', '.', '');
        }


        return $price;

    }

    public static function grandTotal(array $components = null)
    {

        $grandTotal = 0;
        if ($components != null) {


            foreach ($components as $component) {


                $grandTotal += (float) str_replace(' ', '', $component['sub_total']);
            }

            return number_format($grandTotal, '2', '.', '');

        }
    }

}
