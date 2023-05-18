<?php

namespace App\Repositories\Frontend\Api;

use App\Helpers\General\EarningHelper;
use App\Mail\OfflineOrderMail;
use App\Models\Bundle;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderRepository
{
    public function updateOrderStatus($order_id, $transaction_id, $payment_type, $status, $remarks = '')
    {
        /*Order::query()->findOrFail($request->order_confirmation_id)->update([
            "payment_type" => $request->payment_type ?? $request->payment_mode,
            "status" => in_array($request->status, ['successful', 1, 'success', '1', 'Success', 'Successful']) ? 1 : 0,
            "transaction_id" => $request->transaction_id,
            "remarks" => $request->remarks ?? '',
        ]);*/

        try {
            $counter = 0;
            $items = [];
            $order = Order::where('id', '=', (int)$order_id)->where('status', '=', 0)->first();
            if ($order) {
                $order->payment_type = $payment_type;
                $order->status = in_array($status, ['successful', 1, 'success', '1', 'Success', 'Successful']) ? 1 : 0;
                $order->remarks = $remarks;
                $order->transaction_id = $transaction_id;
                $order->save();
                if ($order->status == 1) {
                    (new EarningHelper())->insert($order);
                }
                if ((int)$payment_type == 3) {
                    foreach ($order->items as $key => $cartItem) {
                        $counter++;
                        array_push($items, ['number' => $counter, 'name' => $cartItem->item->name, 'price' => $cartItem->item->price]);
                    }
                    $content['items'] = $items;
                    $content['total'] = $order->amount;
                    $content['reference_no'] = $order->reference_no;
                    try {
                        \Mail::to(auth()->user()->email)->send(new OfflineOrderMail($content));
                    } catch (\Exception $e) {
                        \Log::info($e->getMessage() . ' for order ' . $order->id);
                    }
                } else {
                    foreach ($order->items as $orderItem) {
                        //Bundle Entries
                        if ($orderItem->item_type == Bundle::class) {
                            foreach ($orderItem->item->courses as $course) {
                                $course->students()->attach($order->user_id);
                            }
                        }
                        $orderItem->item->students()->attach($order->user_id);
                    }
                    //Generating Invoice
                    generateInvoice($order);
                }
                return response()->json(['status' => 200, 'result' => null]);
            } else {
                return response()->json(['status' => 100, 'result' => null, 'message' => 'No order found']);
            }
        } catch (\Exception $e) {
            return response()->json(['status' => 100, 'result' => null, 'message' => $e->getMessage()]);
        }
    }
}
