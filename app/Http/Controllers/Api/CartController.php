<?php

namespace App\Http\Controllers\Api;

use App\Models\Cart;
use Illuminate\Http\Request;
use App\Traits\GeneralTrait;
use App\Http\Requests\CartRequest;
use App\Http\Controllers\Controller;
use Validator;
use Illuminate\Validation\ValidationException;

class CartController extends Controller
{
    use GeneralTrait;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($id)
    {
        // 
    }

    public function itemCart($id) {
        $cartItems = Cart::where('user_id', $id)->with('product', 'color', 'size')->get();
        $total = 0.0;
        foreach ($cartItems as $cartItem) {
            if($cartItem->product['price_offer'] == null){
                $total += $cartItem->quantity * $cartItem->product['price'];
            } else {
                $total += $cartItem->quantity * $cartItem->product['price_offer'];
            }
        } 
        $response = [
            'status' => true,
            'cartItems' => $cartItems,
            "total" => (float)sprintf("%.2f", $total),
            'message' => 'All Carts Item'
        ];
        return response()->json($response , 200);
    }

    // public function getTotalPrice() {

    // }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $rules = [
            'quantity'      => 'required|numeric|min:1',
            'product_id'    => 'required',
            'user_id'       => 'required',
            'color_id'      => 'required',
            'size_id'       => 'required',
        ];
        $validator = Validator::make($request->all(), $rules);
        if($validator->fails()) {
            return $this->sendError($validator->errors());
        }
        $duplicates = Cart::where('product_id', $request->product_id)->where('user_id', $request->user_id)->get();
        if ($duplicates->isNotEmpty()) {
            return $this->sendError('Item is already in your cart!');
        }
        try {
            Cart::create($request->all());
                return $this->sendSuccess('Added Item To Cart successfully');
        } catch (\Exception $ex) {
            return $this->sendError($ex->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $cart = Cart::findOrFail($id);
        $rules = [
            'quantity'      => 'required|numeric|min:1',
        ];
        $validator = Validator::make($request->all(), $rules);
        if($validator->fails()) {
            return $this->sendError($validator->errors());
        }
        // if ($request->quantity > $request->productQuantity) {
        //     return $this->sendError('We currently do not have enough items in stock.');
        // }
        $cart = Cart::findOrFail($id);
        if($cart->isEmpty) {
            return $this->sendError('the element not found');
        }
        $cart->update($request->all());
        // Cart::where('user_id', $request->user_id)->where('product_id', $request->product_id)->update([
        //     'quantity' => $request->quantity,
        // ]);
            return $this->sendSuccess('Update Cart Item Successfully');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $cart = Cart::findOrFail($id);
        if(empty($cart)) {
            return $this->sendError('Cart Item Not Found');
        }
        try{
            $cart->delete();
            return $this->sendSuccess('Item has been removed!');
        }
        catch(\Exception $ex) {
            return $this->sendError($ex->getMessage());
        }
    }
}
