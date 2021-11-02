<?php

namespace App\Http\Controllers\Dashboard;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\User;
use App\Traits\GeneralTrait;
use App\Models\Notifications;

class OrderController extends Controller
{
    use GeneralTrait;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $orders = Order::all();
        $orderCancel = Order::where('status', 'cancel')->get()->count();
        $orderShipped = Order::where('status', 'shipped')->get()->count();
        $orderDelivered = Order::where('status', 'delivered')->get()->count();
        return view('dashboard.order.index' , compact('orders', 'orderCancel', 'orderShipped', 'orderDelivered'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            $order = Order::create($request->all());
                return $this->sendSuccess('added order successfully');
        } catch (\Exception $ex) {
            return $this->sendError($ex->getMessage());
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $order = Order::findOrFail($id);
        $products = $order->products;
        $cartProducts = OrderProduct::where('order_id', $order->id)->get();
        // dd($cartProducts);
        return view('dashboard.order.show', compact('order', 'products', 'cartProducts'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $order = Order::findOrFail($id);
        return  view('dashboard.order.edit', compact('order'));
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
        $order = Order::findOrFail($id);
        $user = User::findOrFail($order->user_id);
        Order::findOrFail($id)->update(['delivery_status' => $request->delivery_status]);
        $token = $user->device_token;
        // $deviceToken = User::whereNotNull('device_token')->pluck('device_token')->all();
        $deviceToken = [0 => $token];
        $SERVER_API_KEY = 'AAAA-UW6o9Y:APA91bF3ADThr9m2vIxbSXZfcTvGWzUFvzKDBFnroO5fUz7ZuEszj58NclodRwkkktXry4bI4orB0-lPousbTFCwfAGIK-yg6seyIsareHiiDl5pyH9thng0W2_6sIxlJbBY340YIRLS';
        if($deviceToken == null) {
            Order::findOrFail($id)->update(['delivery_status' => $request->delivery_status]);
                return redirect()->route('order.index')->with('error', __('message.not_found'));
        }
        $delivery = '';
        if($request->dedelivery_status == 0) {
            $delivery = 'Order Placed';
        } else if($request->dedelivery_status == 1) {
            $delivery = 'Confirmed';
        } else if($request->dedelivery_status == 2) {
            $delivery = 'shipped';
        } else if($request->dedelivery_status == 3) {
            $delivery = 'Sent Out For Delivery';
        } else if($request->dedelivery_status == 4) {
            $delivery = 'deliverd';
        }
        $data = [
            "registration_ids" => $deviceToken,
            "notification" => [
                "title" => 'Your Order',
                "body" => 'Your Order has been ' . $delivery,
            ],
            "priority" => "high",
            "data" => [
                "clickaction" => "FLUTTERNOTIFICATIONCLICK",
                "status" => "done"
            ],
            // "to" => "/topics/all"
        ];
        $dataString = json_encode($data);

        $headers = [
            'Authorization: key=' . $SERVER_API_KEY,
            'Content-Type: application/json',
        ];

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $dataString);
        curl_setopt($ch, CURLOPT_FAILONERROR, true);

        $response = curl_exec($ch);

        if (curl_error($ch)) {
            $error_msg = curl_error($ch);
            return redirect()->back()->with('error', $error_msg);
        }
        Notifications::create([
            "user_id"   => $user->id,
            "title"     => 'Your Order',
            "body"      => 'Your Order has been ' . $delivery,
            "title_ar"  => 'Your Order',
            "body_ar"   => 'Your Order has been ' . $delivery,
            "image"     => 'order'
        ]);
        if($request->delivery_status == '4') {
            Order::findOrFail($id)->update(['status' => 'delivered']);
        }
            return redirect()->route('order.index')->with('success', __('message.update_success'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $order = Order::findOrFail($id);
        if(empty($order)) {
            return redirect()->route('order.index')->with('error', __('message.not_found'));
        }
        try{
            $order->delete();
            return redirect()->back()->with('success',  __('message.delete_success'));
        }
        catch(ModelNotFoundException $ModelNotFoundException) {
            return redirect()->route('order.index')->with('error', __('message.error_delete'));
        }
    }

    public function Multidestroy(Request $request) {

        try{
            $multiDeletes = $request->input('MultDelete');
            if($multiDeletes == null) {
                return redirect()->back()->with('error' , __('message.select_item'));
            }
            $count = count($multiDeletes);
            Order::whereIn('id' , $multiDeletes)->delete();
            return redirect()->back()->with('success' , __('message.multi_delete') . $count);
        }
        catch(ModelNotFoundException $ModelNotFoundException) {

            return redirect()->route('category.index')->with('error', __('message.not_found'));
        }
    }
}
