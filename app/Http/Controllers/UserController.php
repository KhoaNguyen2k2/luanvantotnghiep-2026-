<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\UpdatesShippingProfile;
use App\Models\Address;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    use UpdatesShippingProfile;

    public function index()
    {
        return view('user.index');
    }

    public function account_details()
    {
        $user = Auth::user();
        $address = Address::where('user_id', $user->id)->where('isdefault', true)->first();

        return view('user.account-details', compact('user', 'address'));
    }

    public function account_details_update(Request $request)
    {
        $user = Auth::user();
        $request->validate($this->shippingProfileRules($user->id));

        $this->applyShippingProfile($user, $request);

        return redirect()->route('user.account.details')->with('success', 'Đã cập nhật thông tin tài khoản.');
    }

    public function orders()
    {
        $orders = Order::where('user_id',Auth::user()->id ) -> orderBy('created_at','desc') -> paginate(10);
        return view('user.orders', compact('orders'));
    }

    public function order_details($order_id)
    {
        $order = Order::where('user_id',Auth::user()->id ) -> where('id',$order_id) -> first();
        if($order){
        $orderItems = OrderItem::where('order_id', $order_id) -> orderBy('id')->paginate(12);
        $transaction = Transaction::where('order_id', $order_id) -> first();
        return view('user.order-details', compact('order', 'orderItems', 'transaction'));
        }
        else{
            return redirect()->route('login');
        }
       
    }

    public function order_cancel(Request $request)
    {
        $order = Order::where('id', $request->order_id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $order->status = 'canceled';
        $order->canceled_date = Carbon::now();
        $order->save();

        return back()->with('success', 'Đơn hàng đã được hủy thành công.');
    }
}
