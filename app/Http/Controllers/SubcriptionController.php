<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Notifications\ProductNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;


class SubcriptionController extends Controller
{
    public function __construct() {
        $this->middleware('auth');
    }

    public function retrievePlans() {
        $key = \config('services.stripe.secret');
        $stripe = new \Stripe\StripeClient($key);
        $plansraw = $stripe->plans->all();
        $plans = $plansraw->data;
        
        foreach($plans as $plan) {
            $prod = $stripe->products->retrieve(
                $plan->product,[]
            );
            $plan->product = $prod;
        }
        return $plans;
    }

    public function index() {
        $user = auth()->user();
        
        return view('dashboard', [
            'intent' => $user->createSetupIntent(),
        ],compact('user'));
    }

    public function processSubscription(Request $request)
    {
        $amount = $request->amount;
        $payment_method = $request->payment_method;
        $user = auth()->user();
        $user->createOrGetStripeCustomer();
        $payment_method = $user->addPaymentMethod($payment_method);
        $options = [
            'return_url' => route('dashboard'), 
        ];
    
        $user->charge($amount, $payment_method->id, $options); 
    
        return redirect()->route('dashboard'); 
    }


    public function Store(Request $request)
    {
        $user = auth()->user();
        $request->validate([
            'name' => 'required',
        ]);
        Notification::send($user,new ProductNotification($request->name));
        Product::create($request->all());
        return back()->with(['status' => 'Product added Successfully!']);
    }

    public function Maskasread($id)
    {
        if($id){
            auth()->user()->unreadNotifications->where('id',$id)->markAsRead();
        } 
        return back();
    }
}
 