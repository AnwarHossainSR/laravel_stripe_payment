<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $products = Product::all();
        return view('product.index', compact('products'));
    }

    public function createProduct()
    {
        \Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));
        $product = \Stripe\Price::create([
            'unit_amount' => 2000,
            'currency' => 'jpy',
            'recurring' => [
                'interval' => 'month',
            ],
            'lookup_key' => 'standard_monthly',
            'transfer_lookup_key' => true,
            'product_data' => [
                'name' => 'Standard Monthly',
            ],
        ]);
        return $product;
    }

    public function checkout()
    {
        \Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));

        $products = Product::all();
        $totalPrice = 1000;
        $prices = \Stripe\Price::all([
            // retrieve lookup_key from form data POST body
            'lookup_keys' => ['standard_monthly'],
            'expand' => ['data.product'],
        ]);

        $lineItems = [[
            'price' => $prices->data[0]->id,
            'quantity' => 1,
        ]];

        $session = \Stripe\Checkout\Session::create([
            'payment_method_types' => ['card'],
            // 'payment_method_options' => [
            //     'wechat_pay' => [
            //         'client' => "web"
            //     ],
            // ],
            //"customer_creation" => 'always',
            // 'phone_number_collection' => [
            //     'enabled' => true,
            // ],
            'line_items' => $lineItems,
            'mode' => 'subscription',
            'success_url' => route('checkout.success', [], true) . "?session_id={CHECKOUT_SESSION_ID}",
            'cancel_url' => route('checkout.cancel', [], true),

        ]);
        $order = new Order();
        $order->status = 'unpaid';
        $order->total_price = $totalPrice;
        $order->session_id = $session->id;
        $order->save();
        return redirect($session->url);
    }

    public function success(Request $request)
    {
        $stripe = new \Stripe\StripeClient(env('STRIPE_SECRET'));
        $sessionId = $request->get('session_id');
        try {

            $session = $stripe->checkout->sessions->retrieve($sessionId);
            //dd($session);
            //dd($session);
            if (!$session) {
                throw new NotFoundHttpException;
            }
            //$customer = \Stripe\Customer::retrieve($session->customer);
            $customer = $stripe->customers->retrieve($session->customer);
            //dd($session);
            //dd($customer);
            $order = Order::where('session_id', $session->id)->first();
            if (!$order) {
                throw new NotFoundHttpException();
            }
            if ($order->status === 'unpaid') {
                $order->status = 'paid';
                $order->save();
            }

            return view('product.checkout-success', compact('customer'));
        } catch (\Exception $e) {
            throw new NotFoundHttpException();
        }
    }

    public function cancel()
    {
    }

    public function webhook()
    {
        // This is your Stripe CLI webhook secret for testing your endpoint locally.
        $endpoint_secret = env('STRIPE_WEBHOOK_SECRET');

        $payload = @file_get_contents('php://input');
        $sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'];
        $event = null;

        try {
            $event = \Stripe\Webhook::constructEvent(
                $payload,
                $sig_header,
                $endpoint_secret
            );
        } catch (\UnexpectedValueException $e) {
            // Invalid payload
            return response('', 400);
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            // Invalid signature
            return response('', 400);
        }

        // Handle the event
        switch ($event->type) {
            case 'checkout.session.completed':
                $session = $event->data->object;

                $order = Order::where('session_id', $session->id)->first();
                if ($order && $order->status === 'unpaid') {
                    $order->status = 'paid';
                    $order->save();
                    // Send email to customer
                }

                // ... handle other event types
            default:
                echo 'Received unknown event type ' . $event->type;
        }

        return response('');
    }
}
















// namespace App\Http\Controllers;

// use App\Models\Order;
// use App\Models\Product;
// use Illuminate\Http\Request;
// use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

// class ProductController extends Controller
// {
//     public function index(Request $request)
//     {
//         $products = Product::all();

//         return view('product.index', compact('products'));
//     }

//     public function checkout()
//     {
//         \Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));

//         $products = Product::all();
//         $lineItems = [];
//         $totalPrice = 0;
//         foreach ($products as $product) {
//             $totalPrice += $product->price;
//             $lineItems[] = [
//                 'price_data' => [
//                     'currency' => 'jpy',
//                     'product_data' => [
//                         'name' => $product->name,
//                         'images' => [$product->image]
//                     ],
//                     'unit_amount' => $product->price * 100,
//                 ],
//                 'quantity' => 1,
//             ];
//         }
//         $session = \Stripe\Checkout\Session::create([
//             'payment_method_types' => ['card', 'wechat_pay', 'alipay'],
//             'payment_method_options' => [
//                 'wechat_pay' => [
//                     'client' => "web"
//                 ],
//             ],
//             "customer_creation" => 'always',
//             // 'phone_number_collection' => [
//             //     'enabled' => true,
//             // ],
//             'line_items' => $lineItems,
//             'mode' => 'subscription',
//             'success_url' => route('checkout.success', [], true) . "?session_id={CHECKOUT_SESSION_ID}",
//             'cancel_url' => route('checkout.cancel', [], true),

//         ]);
//         $order = new Order();
//         $order->status = 'unpaid';
//         $order->total_price = $totalPrice;
//         $order->session_id = $session->id;
//         $order->save();
//         return redirect($session->url);
//     }

//     public function success(Request $request)
//     {
//         $stripe = new \Stripe\StripeClient(env('STRIPE_SECRET'));
//         $sessionId = $request->get('session_id');
//         try {

//             $session = $stripe->checkout->sessions->retrieve($sessionId);
//             //dd($session);
//             //dd($session);
//             if (!$session) {
//                 throw new NotFoundHttpException;
//             }
//             //$customer = \Stripe\Customer::retrieve($session->customer);
//             $customer = $stripe->customers->retrieve($session->customer);
//             //dd($session);
//             //dd($customer);
//             $order = Order::where('session_id', $session->id)->first();
//             if (!$order) {
//                 throw new NotFoundHttpException();
//             }
//             if ($order->status === 'unpaid') {
//                 $order->status = 'paid';
//                 $order->save();
//             }

//             return view('product.checkout-success', compact('customer'));
//         } catch (\Exception $e) {
//             throw new NotFoundHttpException();
//         }
//     }

//     public function cancel()
//     {
//     }

//     public function webhook()
//     {
//         // This is your Stripe CLI webhook secret for testing your endpoint locally.
//         $endpoint_secret = env('STRIPE_WEBHOOK_SECRET');

//         $payload = @file_get_contents('php://input');
//         $sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'];
//         $event = null;

//         try {
//             $event = \Stripe\Webhook::constructEvent(
//                 $payload,
//                 $sig_header,
//                 $endpoint_secret
//             );
//         } catch (\UnexpectedValueException $e) {
//             // Invalid payload
//             return response('', 400);
//         } catch (\Stripe\Exception\SignatureVerificationException $e) {
//             // Invalid signature
//             return response('', 400);
//         }

//         // Handle the event
//         switch ($event->type) {
//             case 'checkout.session.completed':
//                 $session = $event->data->object;

//                 $order = Order::where('session_id', $session->id)->first();
//                 if ($order && $order->status === 'unpaid') {
//                     $order->status = 'paid';
//                     $order->save();
//                     // Send email to customer
//                 }

//                 // ... handle other event types
//             default:
//                 echo 'Received unknown event type ' . $event->type;
//         }

//         return response('');
//     }
// }
