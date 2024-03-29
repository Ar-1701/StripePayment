<?php

namespace App\Http\Controllers;

use App\Models\AddToCart;
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
        $req->user_id;
        $product = AddToCart::whereRaw('user_id=?', $req->user_id)->get();
        return view('stripe', compact('product'));
    }
    public function stripePost(Request $req)
    {
        // print_r($req->all());
        // die;
        $price = $req->price;
        $customerData = User::find($req->user_id);
        $AddtoCart = AddtoCart::whereRaw('user_id=?', $customerData->id)->get();
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
        foreach ($AddtoCart as $item) {
            $product = Stripe\Product::create([
                'name' => $item->product_name,
                'type' => 'service',
                'description' => 'Premium subscription plan',
                'attributes' => ['qty', 'price'],
                'metadata' => [
                    'qty' => json_encode($item->qty),
                    'price' => json_encode($item->price),
                ],
            ]);
        }
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
    public function cart_view()
    {
        $product =  AddToCart::get();
        return view('add-to-cart', compact('product'));
    }
    public function update_qty(Request $req)
    {
        $add = AddToCart::find($req->id);
        $add->product_id = $req->id;
        $add->qty = $req->qty;
        $add->save();
        return back();
    }
    public function dataSend(Request $req)
    {
        $product = Product::find($req->id);
        $add = new AddToCart();
        $add->user_id = 1;
        $add->product_id = $req->id;
        $add->price = $product->price;
        $add->product_name =  $product->title;
        $add->qty =  1;
        $add->save();
        return back();
    }
}

// Testing Card Credential
// Card Name: Test // any other name you can use here
// Card Number: 4242424242424242
// Month: Any Future Month
// Year: Any Future Year
// CVV: 123
// Payment succeeds-4242 4242 4242 4242
// Payment requires authentication - 4000 0025 0000 3155
// Payment is declined- 4000 0000 0000 9995