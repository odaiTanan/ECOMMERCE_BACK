<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\MakePaymentRequest;
use App\Models\Product;
use Stripe\Checkout\Session;
use Stripe\Stripe;

class PaymentController extends Controller
{
    public function makePayment(MakePaymentRequest $request)
    {
        // الحصول على الـ product_ids والكميات من الطلب
        $productIds = $request->product_ids;
        $quantities = $request->quantities;

        // الحصول على المنتجات من قاعدة البيانات
        $products = Product::whereIn('id', $productIds)->get();
        $lineItems = [];

        foreach ($products as $product) {
            // الحصول على الكمية المحددة لهذا المنتج
            $quantity = $quantities[$product->id] ; // إذا لم يتم تحديد كمية، استخدم 1 كقيمة افتراضية

            // حساب السعر بعد الخصم
            $discountedPrice = $product->price - $product->discount;
            $priceInCents = $discountedPrice * 100; // تحويل السعر إلى سنت

            // إضافة المنتج إلى lineItems
            $lineItems[] = [
                'price_data' => [
                    'currency' => 'usd',
                    'unit_amount' => $priceInCents,
                    'product_data' => [
                        'name' => $product->title,
                        'description' => $product->description,
                    ],
                ],
                'quantity' => $quantity, // استخدام الكمية المحددة
            ];
        }

        Stripe::setApiKey(config('stripe.secret_key'));

        // إنشاء جلسة دفع في Stripe
        $session = Session::create([
            'payment_method_types' => ['card'],
            'line_items' => $lineItems,
            'mode' => 'payment',
            'success_url' => 'http://localhost:5173/payment/success',
            'cancel_url' => 'http://localhost:5173/payment/cancel',
        ]);

        return response()->json(['url' => $session->url]);
    }
}