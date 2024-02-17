<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Refund;
use App\Models\StripePayment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Stripe;
use Stripe\PaymentIntent;

class StripePaymentController extends Controller
{
    public function stripe(Request $req)
    {
        $product = Product::find($req->id);
        return view('stripe', compact('product'));
    }
    public function stripePost(Request $req)
    {
        // print_r($req->all());
        // die;
        $price = $req->price;
        $customerData = User::find($req->user_id);
        $sizeOptions = ['Small', 'Medium', 'Large'];
        $colorOptions = ['Red', 'Blue', 'Green'];
        Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));
        $customer = Stripe\Customer::create([
            'name' => $customerData->name,
            'email' => $customerData->email,
            "source" => $req->stripeToken,
        ]);
        $ch = Stripe\Charge::create([
            "amount" => 100 * $req->price,
            "currency" => "USD",
            // "source" => $req->stripeToken,
            "customer" => $customer["id"],
            "description" => "This is test payment",
        ]);
        $invoice = Stripe\Invoice::create([
            'customer' => $customer["id"],
            'auto_advance' => true,
        ]);
        Stripe\InvoiceItem::create([
            'customer' => $customer["id"],
            'amount' => $req->price,
            'currency' => 'usd',
            'description' => 'Your Invoice Description',
        ]);
        $product = Stripe\Product::create([
            'name' => $req->title,
            'type' => 'service',

            'description' => 'Premium subscription plan',
            'attributes' => ['M', 'blue'],
            'metadata' => [
                'size_options' => json_encode($sizeOptions),
                'color_options' => json_encode($colorOptions),
            ],
        ]);
        if ($ch->status == "succeeded") {
            $productData = new StripePayment();
            $productData->userId = 1;
            $productData->payment = $req->price;
            $productData->payment_method = $ch->payment_method_details->type;
            $productData->payment_status = $ch->status;
            $productData->charge = $ch->id;
            $productData->save();
            Session::flash('success', 'Payment Successful !');
        } else {
            Session::flash('danger', 'Payment Failed !');
        }

        return back();
    }
    public function product()
    {
        $product = Product::all();
        return view('product', compact('product'));
    }
    public function refund()
    {
        $refund = StripePayment::select('stripe_payments.*', 'users.name')
            ->join('users', 'users.id', '=', 'stripe_payments.userId')
            ->orderBy('stripe_payments.id', 'DESC')
            ->get();

        return view('refund', compact('refund'));
    }
    public function refundBack(Request $req)
    {
        Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));
        $data = StripePayment::find($req->id);
        $chargeId = $data->charge;
        $charge = \Stripe\Charge::retrieve($chargeId);

        $refundData = Stripe\Refund::create([
            'charge' => $chargeId,
            'amount' => 100 * $data->payment, // Refund amount in cents
        ]);
        echo $refundedAmount = $data->payment - ($refundData->amount_refunded / 100);
        if ($refundData->status == "succeeded") {
            $refund = new Refund();
            $refund->user_id = $data->userId;
            $refund->refund_amount = $refundedAmount;
            $refund->charge_id = $chargeId;
            $refund->refund_id =  $refundData->id;
            $refund->save();
            echo "refunded";
        }
        echo "<pre>";
        print_r($refundData);
        print_r($charge);
        echo "</pre>";
        // return back();        
    }
    public function refund_view()
    {
        $r = Refund::all();
        return view('refund_cancel', compact('r'));
    }
    public function refund_cancel(Request $req)
    {
        Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));
        $stripe = new \Stripe\StripeClient(env('STRIPE_SECRET'));
        $refundSuccess = $stripe->refunds->cancel($req->refund_id);
        if ($refundSuccess->status == 'succeeded') {
            echo "Refund Canceled\n<pre>";
            print_r($refundSuccess);
            echo "</pre>";
        }
    }
}

// Testing Card Credential
// Card Name: Test // any other name you can use here
// Card Number: 4242424242424242
// Month: Any Future Month
// Year: Any Future Year
// CVV: 123
