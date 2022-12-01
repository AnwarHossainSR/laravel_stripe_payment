<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class SubscriptionApiController extends Controller
{
    public function index(Request $request)
    {
        $products = Product::all();

        return response()->json([
            'products' => $products,
        ]);
    }

    public function createProduct()
    {
        \Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));

        $product = \Stripe\Price::create([
            'unit_amount' => 999 * 100,
            'currency' => 'usd',
            'recurring' => [
                'interval' => 'year',
                'trial_period_days' => 1,
            ],
            'lookup_key' => 'standard_yearly',
            'transfer_lookup_key' => true,
            'product_data' => [
                'name' => 'Standard Yearly',
            ],
        ]);
        return response()->json([
            'product' => $product,
        ]);
    }

    public function checkout()
    {
        \Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));

        // return all customers from stripe
        // $customers = \Stripe\Subscription::all([
        //     'customer' => 'cus_MtZLUAL1L2X38o',
        // ]);
        //dd($customers->data[0]);
        $totalPrice = 1000;
        $prices = \Stripe\Price::all([
            // retrieve lookup_key from form data POST body
            'lookup_keys' => ['standard_yearly'],
            'expand' => ['data.product'],
        ]);
        //dd($prices);
        $lineItems = [[
            'price' => $prices->data[0]->id,
            'quantity' => 1,
        ]];

        $session = \Stripe\Checkout\Session::create([
            'payment_method_types' => ['card'],
            // 'phone_number_collection' => [
            //     'enabled' => true,
            // ],
            'customer_email' => 'test1@gmail.com',
            'line_items' => $lineItems,
            'mode' => 'subscription',
            'subscription_data' => [
                'trial_from_plan' => true,
            ],
            'success_url' => route('checkout.subscription.success.api', [], true) . "?session_id={CHECKOUT_SESSION_ID}",
            'cancel_url' => route('checkout.subscription.failure.api', [], true),
        ]);

        $order = new Order();
        $order->status = 'unpaid';
        $order->total_price = $totalPrice;
        $order->session_id = $session->id;
        $order->save();
        //return redirect($session->url);
        return response()->json([
            'url' => $session->url,
        ]);
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

            // return response()->json([
            //     'session' => $session,
            //     'customer' => $customer,
            //     'order' => $order,
            // ]);
            // redirect to http://localhost:3000/subscription/success
            return redirect()->away('http://localhost:3000/subscription/success');
        } catch (\Exception $e) {
            throw new NotFoundHttpException();
        }
    }

    public function failure()
    {
        return response()->json(['message' => 'Payment failed']);
    }

    public function subscriptionCancel($subscription_id)
    {
        \Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));
        $subscription = \Stripe\Subscription::retrieve($subscription_id);
        $subscription->cancel();
        return response()->json(['message' => 'Subscription cancelled']);
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
