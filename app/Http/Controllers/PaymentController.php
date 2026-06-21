<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class PaymentController extends Controller
{
    public function index()
    {
        return view('checkout');
    }

     public function createPayment(Request $request)
    {
        $vnp_Url = env('VNP_URL');
        $vnp_Returnurl = env('VNP_RETURN_URL');
        $vnp_TmnCode = env('VNP_TMN_CODE');
        $vnp_HashSecret = env('VNP_HASH_SECRET');

        $vnp_TxnRef = time() . rand(1000, 9999);
        
        // Lấy order_id từ session
        $order_id = Session::get('order_id');
        if (!$order_id) {
            return redirect()->route('cart.checkout')->with('error', 'Không tìm thấy đơn hàng');
        }
        
        $order = Order::find($order_id);
        if (!$order) {
            return redirect()->route('cart.checkout')->with('error', 'Đơn hàng không tồn tại');
        }
        
        $total = (int) round($order->total);
        
        $inputData = [
            'vnp_Version' => '2.1.0',
            'vnp_Command' => 'pay',
            'vnp_TmnCode' => $vnp_TmnCode,
            'vnp_Locale' => 'vn',
            'vnp_CurrCode' => 'VND',
            'vnp_TxnRef' => $vnp_TxnRef,
            'vnp_OrderInfo' => 'Thanh toan don hang ' . $order_id,
            'vnp_OrderType' => 'other',
            'vnp_Amount' => $total * 100,
            'vnp_ReturnUrl' => $vnp_Returnurl,
            'vnp_IpAddr' => $request->ip(),
            'vnp_CreateDate' => date('YmdHis'),
        ];

        ksort($inputData);
        $hashdata = '';
        $query = '';
        $i = 0;
        foreach ($inputData as $key => $value) {
            if ($i == 1) {
                $hashdata .= '&' . urlencode($key) . '=' . urlencode($value);
                $query .= '&' . urlencode($key) . '=' . urlencode($value);
            } else {
                $hashdata .= urlencode($key) . '=' . urlencode($value);
                $query .= urlencode($key) . '=' . urlencode($value);
                $i = 1;
            }
        }

        $vnpSecureHash = hash_hmac('sha512', $hashdata, $vnp_HashSecret);
        $vnp_Url .= '?' . $query . '&vnp_SecureHash=' . $vnpSecureHash;

        return redirect($vnp_Url);
    }

    // Phương thức xử lý kết quả trả về từ VNPay
    public function vnpayReturn(Request $request)
    {
        $vnp_HashSecret = env('VNP_HASH_SECRET');
        $inputData = $request->all();
        $vnp_SecureHash = $inputData['vnp_SecureHash'];

        // Loại bỏ các tham số hash để tính toán lại và đối chiếu
        unset($inputData['vnp_SecureHash']);
        unset($inputData['vnp_SecureHashType']);

        ksort($inputData);
        $i = 0;
        $hashData = "";
        foreach ($inputData as $key => $value) {
            if ($i == 1) {
                $hashData = $hashData . '&' . urlencode($key) . "=" . urlencode($value);
            } else {
                $hashData = $hashData . urlencode($key) . "=" . urlencode($value);
                $i = 1;
            }
        }

        $secureHash = hash_hmac('sha512', $hashData, $vnp_HashSecret);

        if ($secureHash == $vnp_SecureHash) {
            if ($request->vnp_ResponseCode == '00') {
                // Thanh toán thành công
                $order_id = Session::get('order_id');
                if ($order_id) {
                    $transaction = Transaction::where('order_id', $order_id)->first();
                    if ($transaction) {
                        $transaction->status = 'approved';
                        $transaction->save();
                    }
                }
                return redirect()->route('cart.order.confirmation')->with('success', 'Thanh toán thành công!');
            } else {
                // Thanh toán thất bại
                $order_id = Session::get('order_id');
                if ($order_id) {
                    $transaction = Transaction::where('order_id', $order_id)->first();
                    if ($transaction) {
                        $transaction->status = 'declined';
                        $transaction->save();
                    }
                }
                return redirect()->route('cart.checkout')->with('error', 'Giao dịch thất bại: ' . $request->vnp_ResponseCode);
            }
        } else {
            return redirect()->route('cart.checkout')->with('error', 'Chữ ký không hợp lệ!');
        }
    }
}
