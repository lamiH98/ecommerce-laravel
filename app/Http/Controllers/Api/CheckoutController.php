<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Cartalyst\Stripe\Exception\CardErrorException;
use App\Http\Requests\CheckoutRequest;
use App\Models\Order;
use App\Models\Product;
use App\Models\Cart;
use App\Models\OrderProduct;
use App\Traits\GeneralTrait;

class CheckoutController extends Controller
{
    use GeneralTrait;

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Check race condition when there are less item available to purchase
        $cartItems = Cart::where('user_id', $request->user_id)->with('product', 'color', 'size')->get();
        if($this->productAreNotLongerAvailable($cartItems)) {
            return $this->sendError('Product is No Longer Available');
        }

        // dd($request->user_id);
        // $contents = $request->cart->map(function ($item) {
        //     return $item->name.', '.$item->quantity;
        // })->values()->toJson();

        try {

            // Insert into orders table & Insert into order_product table
            $this->addToOrdersTable($request, null);

            // Decrease the quantities of all the products in the cart
            $this->decreaseQuantities($cartItems);
            foreach($cartItems as $cartItem) {
                $cartItem->delete();
            }

            return $this->sendSuccess('added successfuly');
        } catch (CardErrorException $e) {
            $this->addToOrdersTable($request, $e->getMessage());
            return $this->sendError('there are error');
        }
    }

    public function addToOrdersTable($request, $error) {
        // Insert into orders table
        $cartItems = Cart::where('user_id', $request->user_id)->with('product', 'color', 'size')->get();
        $order = Order::create([
            'user_id'           => $request->user_id,
            'name'              => $request->name,
            'email'             => $request->email,
            'city'              => $request->city,
            'neighourhood'      => $request->neighourhood,
            'street'            => $request->street,
            'phone'             => $request->phone,
            'discount'          => $request->discount,
            'status'            => $request->status,
            'discount_code'     => $request->code,
            'delivery_status'   => $request->delivery_status,
            'total'             => $request->total,
            'newTotal'          => $request->newTotal,
            'error'             => $error,
        ]);

        // Insert into order_product table
        foreach($cartItems as $cartItem) {
            OrderProduct::create([
                'order_id'      => $order->id,
                'product_id'    => $cartItem['product']['id'],
                'quantity'      => $cartItem['quantity'],
                'color'         => $cartItem['color']['color_name'],
                'size'          => $cartItem['size']['size'],
            ]);
        }
    }

    protected function decreaseQuantities($cartItems) {
        foreach($cartItems as $cartItem) {
            $product = Product::find($cartItem['product_id']);
            $product->update(['quantity' => $product->quantity - $cartItem['quantity']]);
        }
    }

    protected function productAreNotLongerAvailable($cartItems) {
        foreach($cartItems as $cartItem) {
            $product = Product::find($cartItem['product_id']);
            if($product->quantity < $cartItem['quantity']) {
                return true;
            }
        }
        return false;
    }

}
