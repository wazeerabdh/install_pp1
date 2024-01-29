<?php

namespace App\CentralLogics;

use App\Model\Order;
use App\Model\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class OrderLogic
{
    public static function track_order($order_id)
    {
        return Order::with(['details', 'delivery_man.rating'])->where(['id' => $order_id])->first();
    }

}
