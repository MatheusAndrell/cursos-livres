<?php

namespace App\Http\Controllers\v2;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Repositories\Frontend\Api\OrderRepository;
use Exception;
use Illuminate\Support\Facades\Http;

class PayUMoneyController extends Controller
{
    protected $salt;
    protected $merchant_key;
    protected $endpoint;
    protected $formData;

    public function __construct()
    {
        $this->merchant_key = config('services.payu.key');
        $this->salt = config('services.payu.salt');
    }

    /**
     * @throws \Exception
     */
    public function handlePayment($order_id)
    {
        $order = Order::query()->find($order_id);
        if (is_null($order))
            throw new Exception("Invalid Request");
        if ($order->status == 1)
            throw new Exception("Payment Already Done");

        $user = $order->user;
        $this->formData = [
            'key' => $this->merchant_key,
            'hash' => null,
            'txnid' => substr(hash('sha256', mt_rand() . microtime()), 0, 20),
            'amount' => $order->amount,
            'firstname' => $user->name,
            // 'firstname' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone ?? '9166865987',
            'productinfo' => 'Course / Bundle Purchase',
            'surl' => route('payu-payment.success'),
            'furl' => route('payu-payment.declined'),
            'service_provider' => 'payu_paisa',
            'udf1' => $order_id,
        ];
        $action = config('services.payu.mode') == 'sandbox' ? "https://sandboxsecure.payu.in/_payment" : "https://secure.payu.in";
        $this->formData['hash'] = $this->getHash();
        // return Http::asForm()->post($this->endpoint . '/_payment', $this->formData);
        $formData = $this->formData;
        // dd($formData);
        return view('web_view.payu_payment', compact('action', 'formData'));
    }

    public function paymentStatus()
    {
        $requestData = request()->all();
        if ($requestData['status'] == 'success') {
            $order_id = $requestData['udf1'];
            /*Order::query()->findOrFail($order_id)->update([
                "payment_type" => 7,
                "status" => 1,
                "transaction_id" => $requestData['txnid'],
                "remarks" => '',
            ]);*/
            $orderRepository = new OrderRepository();
            $orderRepository->updateOrderStatus($order_id, $requestData['txnid'], 7, 1);
            $order = Order::query()->findOrFail($order_id);
            clearCartById($order->user_id);
        }
        return view('web_view.status');
    }

    /**
     * @throws \Exception
     */
    protected function getHash()
    {
        // Hash Sequence
        $hashSequence = "key|txnid|amount|productinfo|firstname|email|udf1|udf2|udf3|udf4|udf5|udf6|udf7|udf8|udf9|udf10";
        if (
            empty($this->formData['key'])
            || empty($this->formData['txnid'])
            || empty($this->formData['amount'])
            || empty($this->formData['firstname'])
            || empty($this->formData['email'])
            || empty($this->formData['phone'])
            || empty($this->formData['productinfo'])
            || empty($this->formData['surl'])
            || empty($this->formData['furl'])
            || empty($this->formData['udf1'])
            || empty($this->formData['service_provider'])
        ) {
            throw new \Exception('Please Enter All Required Information!');
        } else {
            //$posted['productinfo'] = json_encode(json_decode('[{"name":"tutionfee","description":"","value":"500","isRequired":"false"},{"name":"developmentfee","description":"monthly tution fee","value":"1500","isRequired":"false"}]'));
            $hashVarsSeq = explode('|', $hashSequence);
            $hash_string = '';
            foreach ($hashVarsSeq as $hash_var) {
                $hash_string .= $this->formData[$hash_var] ?? '';
                $hash_string .= '|';
            }
            $hash_string .= $this->salt;
            return strtolower(hash('sha512', $hash_string));
        }
    }
}
