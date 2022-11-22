<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Stripe;

class StripePaymenController extends Controller
{
    //write method for stript payment post
    public function stripePost(Request $request)
    {
        try {
            $stripe = new \Stripe\StripeClient(env('STRIPE_SECRET'));
            $res = $stripe->tokens->create([
                'card' => [
                    'number' => $request->card_number,
                    'exp_month' => $request->exp_month,
                    'exp_year' => $request->exp_year,
                    'cvc' => $request->cvc,
                ],
            ]);

            \Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));

            $charge = \Stripe\Charge::create([
                'amount' => $request->amount,
                'currency' => 'usd',
                'description' => 'Example charge',
                'source' => $res->id,
            ]);

            return response()->json($charge);
        } catch (\Throwable $th) {
            return response()->json($th->getMessage());
        }
    }
}
