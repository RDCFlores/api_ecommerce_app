<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\CheckoutRequest;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Stripe\Stripe;
use Stripe\PaymentIntent;

class OrderController extends Controller
{
   public function checkout(CheckoutRequest $request)
    {
        $stripeSecret = config('services.stripe.secret') ?? env('STRIPE_SECRET');

    if (empty($stripeSecret)) {
        return response()->json([
            'error' => 'La clave STRIPE_SECRET no se encuentra cargada en el servidor. Verifica el archivo .env.'
        ], 500);
    }

    Stripe::setApiKey($stripeSecret);
        DB::beginTransaction();

        try {
            $user = auth('api')->user();
            $totalAmount = 0;
            $itemsData = [];

            foreach ($request->items as $item) {
                $product = Product::findOrFail($item['product_id']);

                if ($product->stock < $item['quantity']) {
                    return response()->json([
                        'error' => "Stock insuficiente para el producto: {$product->name}"
                    ], 400);
                }

                $subtotal = $product->price * $item['quantity'];
                $totalAmount += $subtotal;

                $itemsData[] = [
                    'product'  => $product,
                    'quantity' => $item['quantity'],
                    'price'    => $product->price,
                ];
            }

           $order = Order::create([
                'user_id'      => $user->id,
                'total_amount' => $totalAmount,
                'status'       => 'pending',
            ]);

            foreach ($itemsData as $itemData) {
                OrderItem::create([
                    'order_id'   => $order->id,
                    'product_id' => $itemData['product']->id,
                    'quantity'   => $itemData['quantity'],
                    'price'      => $itemData['price'],
                ]);

                $itemData['product']->decrement('stock', $itemData['quantity']);
            }

            $paymentIntent = PaymentIntent::create([
                'amount'         => (int) ($totalAmount * 100), // En centavos
                'currency'       => 'usd',
                'payment_method' => $request->payment_method_id,
                'confirm'        => true,
                'automatic_payment_methods' => [
                    'enabled' => true,
                    'allow_redirects' => 'never',
                ],
            ]);

            Payment::create([
                'order_id'                 => $order->id,
                'stripe_payment_intent_id' => $paymentIntent->id,
                'amount'                   => $totalAmount,
                'status'                   => $paymentIntent->status,
            ]);

            if ($paymentIntent->status === 'succeeded') {
                $order->update(['status' => 'paid']);
            }

            DB::commit();

            return response()->json([
                'message' => 'Compra realizada exitosamente',
                'order'   => $order->load('items', 'payment'),
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error'   => 'Error en el proceso de pago',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function myOrders()
    {
        $user = auth('api')->user();
        $orders = Order::where('user_id', $user->id)
            ->with('items.product', 'payment')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($orders);
    }
}
