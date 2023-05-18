<?php

namespace App\Http\Controllers\v2;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Repositories\Frontend\Api\OrderRepository;
use Exception;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;

class InstamojoController extends Controller
{
    const API_VERSION = '1.1';
    const TEST_BASE_URL = 'https://test.instamojo.com/api/' . self::API_VERSION;
    const PRODUCTION_BASE_URL = 'https://www.instamojo.com/api/' . self::API_VERSION;
    private $url;
    private $http_code;
    private $error_message;

    /**
     * InstamojoWrapper constructor.
     */
    public function __construct()
    {
        $this->url = (config('services.instamojo.mode') == 'sandbox') ? self::TEST_BASE_URL : self::PRODUCTION_BASE_URL;
    }

    /**
     * @throws Exception
     */
    public function instamojoPayment($order_id)
    {
        $order = Order::query()->find($order_id);
        if (is_null($order))
            throw new Exception("Invalid Request");
        if ($order->status == 1)
            throw new Exception("Payment Already Done");

        // $cartTotal = number_format($order->amount, 2);
        if ($order->amount <= 9)
            return redirect()->route('instamojo-payment.declined', ['message' => 'Order Amount Cannot be less then 10']);
        $cartdata = [
            "purpose" => "Buy Course/Bundle",
            "amount" => $order->amount,
            "buyer_name" => $order->user->first_name,
            "send_email" => true,
            //"send_sms" => true,
            // "phone" => $order->user->phone??99900999,
            "email" => $order->user->email,
            "redirect_url" => route('instamojo-payment.status'),
        ];
        Session::put('instamojo_payment', ['order_id' => $order->id]);
        return $this->pay($cartdata);
    }

    public function getInstamojoStatus()
    {
        $requestData = request()->all();
        $payment_sess = session()->get('instamojo_payment');
        $order_id = $payment_sess['order_id'];
        \Session::forget('failure');
        \Session::forget('instamojo_payment');
        if (request()->get('payment_status') == 'Credit') {
            /*Order::query()->findOrFail($order_id)->update([
                "payment_type" => 5,
                "status" => 1,
                "transaction_id" => $requestData['payment_id'],
                "remarks" => '',
            ]);*/
            $orderRepository = new OrderRepository();
            $orderRepository->updateOrderStatus($order_id, $requestData['payment_id'], 5, 1);
            $order = Order::query()->findOrFail($order_id);
            clearCartById($order->user_id);
            $message = 'Payment Successfully Done';
            return redirect()->route('instamojo-payment.success', compact('message'));
        } else if (request()->get('payment_status') == 'Failed') {
            $message = trans('labels.frontend.cart.payment_failed');
            return redirect()->route('instamojo-payment.declined', compact('message'));
        } else {
            $message = trans('labels.frontend.cart.payment_failed');
            return redirect()->route('instamojo-payment.declined', compact('message'));
        }
    }


    public function pay($cartdata)
    {
        try {
            $user = auth()->user() ?? request()->user()->id ?? null;
            $response = $this->api_request($cartdata);
            if ($response->success == true) {
                return Redirect::away($response->payment_request->longurl);
            } else {
                \Log::info(json_encode($this->error_message, true) . ' for id = ' . ($user->id ?? ''));
                \Session::put('failure', trans('labels.frontend.cart.unknown_error'));
                $message = trans('labels.frontend.cart.unknown_error');
                return Redirect::route('instamojo-payment.declined', compact('message'));
            }
        } catch (\Exception $e) {
            \Log::info($e->getMessage() . ' for id = ' . ($user->id ?? null));
        }
    }

    public function status()
    {
        return view('web_view.status');
    }

    private function api_request($payload)
    {
        $headers = array("X-Api-Key:" . config('services.instamojo.key'),
            "X-Auth-Token:" . config('services.instamojo.secret'));

        $request_url = $this->url . '/payment-requests/';
        $options = array();
        $options[CURLOPT_URL] = $request_url;
        $options[CURLOPT_HEADER] = false;
        $options[CURLOPT_RETURNTRANSFER] = true;
        $options[CURLOPT_FOLLOWLOCATION] = true;
        $options[CURLOPT_HTTPHEADER] = $headers;
        $options[CURLOPT_POST] = true;
        $options[CURLOPT_POSTFIELDS] = http_build_query($payload);
        $options[CURLOPT_SSL_VERIFYPEER] = false;
        $curl_request = curl_init();
        $setopt = curl_setopt_array($curl_request, $options);
        $response = curl_exec($curl_request);

        $this->http_code = curl_getinfo($curl_request, CURLINFO_HTTP_CODE);

        $this->error_message = curl_error($curl_request);
        return json_decode($response);
    }

}
