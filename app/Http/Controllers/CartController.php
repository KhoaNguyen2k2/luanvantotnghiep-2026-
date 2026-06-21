<?php

namespace App\Http\Controllers;

use App\Models\Address;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Surfsidemedia\Shoppingcart\Facades\Cart;

class CartController extends Controller
{
    private const SESSION_COUPONS = 'applied_coupons';

    /**
     * Chuẩn hoá giá VND từ form (hidden input có thể là "15500000", "15500000.00", "15.500.000"...).
     * Lưu ý: không được gộp mọi ký tự không-phải-số — chuỗi "15500000.00" sẽ thành "1550000000" (~×100).
     */
    private function normalizeVndPrice($value): float
    {
        if ($value === null) {
            return 0.0;
        }
        if (is_int($value) || is_float($value)) {
            return (float) $value;
        }

        $raw = trim((string) $value);
        if ($raw === '') {
            return 0.0;
        }

        $raw = str_replace(["\xc2\xa0"], '', str_replace(['₫', 'đ', 'Đ', ' '], '', $raw));

        if (preg_match('/^\d{1,3}(\.\d{3})+$/', $raw)) {
            return (float) str_replace('.', '', $raw);
        }

        $noCommaThousands = str_replace(',', '', $raw);
        if ($noCommaThousands !== '' && is_numeric($noCommaThousands)) {
            return (float) $noCommaThousands;
        }

        $digitsOnly = preg_replace('/\D+/', '', $raw);

        return $digitsOnly === '' ? 0.0 : (float) $digitsOnly;
    }

    /** Giá đơn vị khớp với trang chi tiết / giỏ: ưu tiên giá SP trong DB, fallback giá đã lưu trong cart. */
    private function unitPriceFromCartRow(object $item): float
    {
        $product = $item->model ?? null;
        if ($product instanceof Product) {
            $sale = $product->sale_price;
            $regular = $product->regular_price;
            if ($sale !== null && $sale !== '') {
                return (float) $sale;
            }
            if ($regular !== null && $regular !== '') {
                return (float) $regular;
            }
        }

        return (float) $item->price;
    }

    private function cartNumericSubtotal(): float
    {
        return (float) Cart::instance('cart')->content()->reduce(function ($carry, $item) {
            return $carry + ($this->unitPriceFromCartRow($item) * (int) $item->qty);
        }, 0.0);
    }

    private function cartSubtotalForCategory(int $categoryId): float
    {
        return (float) Cart::instance('cart')->content()->reduce(function ($carry, $item) use ($categoryId) {
            $product = $item->model;
            if (! $product instanceof Product) {
                return $carry;
            }
            if ((int) $product->category_id !== $categoryId) {
                return $carry;
            }

            return $carry + ($this->unitPriceFromCartRow($item) * (int) $item->qty);
        }, 0.0);
    }

    private function eligibleSubtotalForCoupon(Coupon $coupon): float
    {
        if ($coupon->scope === Coupon::SCOPE_CATEGORY && $coupon->category_id) {
            return $this->cartSubtotalForCategory((int) $coupon->category_id);
        }

        return $this->cartNumericSubtotal();
    }

    /** @return string|null Thông báo lỗi tiếng Việt hoặc null nếu hợp lệ */
    private function validateCouponAgainstCart(Coupon $coupon): ?string
    {
        $eligible = $this->eligibleSubtotalForCoupon($coupon);

        if ($coupon->scope === Coupon::SCOPE_CATEGORY && $coupon->category_id && $eligible <= 0) {
            return 'Giỏ hàng không có sản phẩm thuộc danh mục áp dụng cho mã này.';
        }

        $min = (float) $coupon->cart_value;
        if ($eligible + 0.00001 < $min) {
            return 'Đơn/giỏ chưa đạt giá trị tối thiểu để áp dụng mã này (theo phạm vi mã).';
        }

        return null;
    }

    private function migrateLegacyCouponSession(): void
    {
        if (Session::has('coupon') && ! Session::has(self::SESSION_COUPONS)) {
            $legacy = Session::get('coupon');
            if (is_array($legacy) && isset($legacy['code'])) {
                Session::put(self::SESSION_COUPONS, [$legacy]);
            }
            Session::forget('coupon');
        }
    }

    /** @return list<array<string, mixed>> */
    private function getAppliedCoupons(): array
    {
        $this->migrateLegacyCouponSession();
        $list = Session::get(self::SESSION_COUPONS, []);

        return is_array($list) ? array_values($list) : [];
    }

    /** @param  list<array<string, mixed>>  $list */
    private function setAppliedCoupons(array $list): void
    {
        Session::put(self::SESSION_COUPONS, array_values($list));
    }

    private function couponPayloadFromModel(Coupon $coupon): array
    {
        return [
            'code' => $coupon->code,
            'type' => $coupon->type,
            'value' => $coupon->value,
            'cart_value' => $coupon->cart_value,
            'scope' => $coupon->scope ?: Coupon::SCOPE_ORDER,
            'category_id' => $coupon->category_id,
        ];
    }

    private function normalizeCouponCode(string $code): string
    {
        return strtoupper(trim($code));
    }

    private function isCouponAlreadyApplied(string $couponCode): bool
    {
        $needle = $this->normalizeCouponCode($couponCode);
        foreach ($this->getAppliedCoupons() as $row) {
            if ($this->normalizeCouponCode((string) ($row['code'] ?? '')) === $needle) {
                return true;
            }
        }

        return false;
    }

    private function refreshCouponIfPresent(): void
    {
        $list = $this->getAppliedCoupons();
        if ($list === []) {
            Session::forget(['discounts', 'coupon']);

            return;
        }

        $next = [];
        foreach ($list as $row) {
            $code = $row['code'] ?? null;
            if (! $code) {
                continue;
            }
            $coupon = Coupon::where('code', $code)
                ->whereDate('expiry_date', '>=', Carbon::today())
                ->first();
            if (! $coupon) {
                continue;
            }
            if ($this->validateCouponAgainstCart($coupon) !== null) {
                continue;
            }
            $next[] = $this->couponPayloadFromModel($coupon);
        }

        $this->setAppliedCoupons($next);
        if ($next === []) {
            Session::forget('discounts');

            return;
        }

        $this->calculateDiscount();
    }

    public function index()
    {
        $items = Cart::instance('cart')->content();
        $this->refreshCouponIfPresent();
        $appliedCoupons = $this->getAppliedCoupons();

        return view('cart', compact('items', 'appliedCoupons'));
    }

    public function add_to_cart(Request $request)
    {
        $price = $this->normalizeVndPrice($request->price);
        $qty = (int) $request->quantity;
        if ($qty < 1) {
            $qty = 1;
        }

        Cart::instance('cart')
            ->add($request->id, $request->name, $qty, $price)
            ->associate(Product::class);

        return redirect()->back();
    }

    public function increase_cart_quantity($rowId)
    {
        $productId = Cart::instance('cart')->get($rowId);
        $qty = $productId->qty + 1;
        Cart::instance('cart')->update($rowId, $qty);

        return redirect()->back();
    }

    public function decrease_cart_quantity($rowId)
    {
        $productId = Cart::instance('cart')->get($rowId);
        $qty = $productId->qty - 1;
        Cart::instance('cart')->update($rowId, $qty);

        return redirect()->back();
    }

    public function remove_item($rowId)
    {
        Cart::instance('cart')->remove($rowId);

        return redirect()->back();
    }

    public function empty_cart()
    {
        Session::forget(['coupon', self::SESSION_COUPONS, 'discounts']);
        Cart::instance('cart')->destroy();

        return redirect()->back();
    }

    public function apply_coupon_code(Request $request)
    {
        $coupon_code = trim((string) $request->input('coupon_code', ''));
        if ($coupon_code === '') {
            return redirect()->back()->with('error', 'Vui lòng nhập mã giảm giá.');
        }

        if ($this->isCouponAlreadyApplied($coupon_code)) {
            return redirect()->back()->with('error', 'Mã giảm giá này đã được áp dụng.');
        }

        $coupon = Coupon::where('code', $coupon_code)
            ->whereDate('expiry_date', '>=', Carbon::today())
            ->first();

        if (! $coupon) {
            return redirect()->back()->with('error', 'Mã giảm giá không hợp lệ hoặc đã hết hạn.');
        }

        $error = $this->validateCouponAgainstCart($coupon);
        if ($error !== null) {
            return redirect()->back()->with('error', $error);
        }

        $applied = $this->getAppliedCoupons();
        $applied[] = $this->couponPayloadFromModel($coupon);
        $this->setAppliedCoupons($applied);
        $this->calculateDiscount();

        return redirect()->back()->with('success', 'Đã áp dụng mã '.$coupon->code.'.');
    }

    public function calculateDiscount(): void
    {
        $coupons = $this->getAppliedCoupons();
        if ($coupons === []) {
            Session::forget('discounts');

            return;
        }

        $cartTotal = $this->cartNumericSubtotal();
        $lines = [];

        foreach ($coupons as $c) {
            $discountBase = ($c['scope'] ?? Coupon::SCOPE_ORDER) === Coupon::SCOPE_CATEGORY && ! empty($c['category_id'])
                ? $this->cartSubtotalForCategory((int) $c['category_id'])
                : $cartTotal;

            $d = 0.0;
            if (($c['type'] ?? '') === 'fixed') {
                $d = min((float) $c['value'], $discountBase);
            } else {
                $d = ($discountBase * (float) $c['value']) / 100;
            }

            $lines[] = [
                'code' => $c['code'],
                'amount' => round($d, 2),
            ];
        }

        $sumLines = array_sum(array_column($lines, 'amount'));
        $totalDiscount = min($sumLines, $cartTotal);

        if ($sumLines > $cartTotal && $sumLines > 0) {
            $ratio = $cartTotal / $sumLines;
            foreach ($lines as &$ln) {
                $ln['amount'] = round($ln['amount'] * $ratio, 2);
            }
            unset($ln);
            $totalDiscount = array_sum(array_column($lines, 'amount'));
        }

        $subtotalAfterDiscount = max(0.0, $cartTotal - $totalDiscount);
        $taxRate = (float) config('cart.tax');
        $taxAfterDiscount = ($subtotalAfterDiscount * $taxRate) / 100;
        $totalAfterDiscount = $subtotalAfterDiscount + $taxAfterDiscount;

        Session::put('discounts', [
            'discount' => number_format($totalDiscount, 2, '.', ''),
            'discount_lines' => $lines,
            'subtotal' => number_format($subtotalAfterDiscount, 2, '.', ''),
            'tax' => number_format($taxAfterDiscount, 2, '.', ''),
            'total' => number_format($totalAfterDiscount, 2, '.', ''),
        ]);
    }

    public function remove_coupon_code(Request $request)
    {
        $code = trim((string) $request->input('code', ''));
        if ($code === '') {
            return redirect()->back()->with('error', 'Thiếu mã cần gỡ.');
        }

        $needle = $this->normalizeCouponCode($code);
        $list = $this->getAppliedCoupons();
        $filtered = array_values(array_filter($list, function (array $row) use ($needle) {
            return $this->normalizeCouponCode((string) ($row['code'] ?? '')) !== $needle;
        }));

        if (count($filtered) === count($list)) {
            return redirect()->back()->with('error', 'Không tìm thấy mã này trong danh sách đã áp dụng.');
        }

        $this->setAppliedCoupons($filtered);
        Session::forget('coupon');

        if ($filtered === []) {
            Session::forget('discounts');
        } else {
            $this->calculateDiscount();
        }

        return redirect()->back()->with('success', 'Đã gỡ mã '.$code.'.');
    }

    public function checkout()
    {
        if(!Auth::check()) {
            return redirect()->route('login')->with('error', 'Vui lòng đăng nhập để tiếp tục thanh toán.');
        }

        $address = Address::where('user_id', Auth::user()->id)->where('isdefault', 1)->first();
        return view('checkout', compact('address'));
    }

    public function place_an_order(Request $request)
    {
        $user_id = Auth::user()->id;
        $address = Address::where('user_id', $user_id)->where('isdefault', true)->first();

        if (! $address) {
            $request->validate([
                'name' => 'required|string|max:255',
                'phone' => 'required|numeric|digits:10',
                'zip' => 'required|numeric|digits:6',
                'state' => 'required|string|max:255',
                'city' => 'required|string|max:255',
                'address' => 'required|string|max:500',
                'locality' => 'required|string|max:255',
                'landmark' => 'nullable|string|max:255',
            ]);

            // Chỉ dùng cho đơn hàng — không lưu vào bảng addresses (lần sau checkout trống).
            $address = (object) [
                'name' => $request->name,
                'phone' => $request->phone,
                'zip' => $request->zip,
                'state' => $request->state,
                'city' => $request->city,
                'address' => $request->address,
                'locality' => $request->locality,
                'landmark' => $request->landmark,
                'country' => 'Việt Nam',
            ];
        }
        $this->setAmountforCheckout($request);

        $checkout = Session::get('checkout', []);
        $order = new Order();
        $order->user_id = $user_id;
        $order->subtotal = $this->normalizeVndPrice($checkout['subtotal'] ?? 0);
        $order->discount = $this->normalizeVndPrice($checkout['discount'] ?? 0);
        $order->tax = $this->normalizeVndPrice($checkout['tax'] ?? 0);
        $order->total = $this->normalizeVndPrice($checkout['total'] ?? 0);
        $order->name = $address->name;
        $order->phone = $address->phone;
        $order->locality = $address->locality;
        $order->address = $address->address;
        $order->city = $address->city;
        $order->state = $address->state;
        $order->country = $address->country;
        $order->landmark = $address->landmark;
        $order->zip = $address->zip;
        $order->save();

        foreach (Cart::instance('cart')->content() as $item) {
            $orderItem = new OrderItem();
            $orderItem->product_id = $item->id;
            $orderItem->order_id = $order->id;
            $orderItem->price = $this->unitPriceFromCartRow($item);
            $orderItem->quantity = $item->qty;
            $orderItem->save();

            $product = Product::find($item->id);
            if ($product) {
                $product->quantity = max(0, $product->quantity - $item->qty);
                if ($product->quantity === 0) {
                    $product->stock_status = 'out_of_stock';
                }
                $product->save();
            }
        }
        if($request->mode =='card'){
            //
        }
        else if($request->mode == 'paypal'){
            //
        }
        else if($request->mode == 'vnpay'){
            $transaction = new Transaction();
            $transaction->user_id = $user_id;
            $transaction->order_id = $order->id;
            $transaction->mode = $request->mode;
            $transaction->status = 'pending';
            $transaction->save();
            
            Cart::instance('cart')->destroy();
            Session::forget('checkout');
            Session::forget('coupon');
            Session::forget('discounts');
            Session::put('order_id', $order->id);
            Address::where('user_id', $user_id)->delete();
            
            return redirect()->route('vnpay.create');
        }
        else if($request->mode == 'cod' ){
            $transaction = new Transaction();
            $transaction->user_id = $user_id;
            $transaction->order_id = $order->id;
            $transaction->mode = $request->mode;
            $transaction->status = 'pending';
            $transaction->save();
        }

        Cart::instance('cart')->destroy();
        Session::forget('checkout');
        Session::forget('coupon');
        Session::forget('discounts');
        Session::put('order_id', $order->id);
        Address::where('user_id', $user_id)->delete();

        return redirect()->route('cart.order.confirmation');


    }


    public function setAmountforCheckout(Request $request)
    {
        if(!Cart::instance('cart')->content()->count() > 0) {
            Session::forget('checkout');
            return;
        }

        if (Session::has('discounts')) {
            $d = Session::get('discounts', []);
            Session::put('checkout', [
                'discount' => $this->normalizeVndPrice($d['discount'] ?? 0),
                'subtotal' => $this->normalizeVndPrice($d['subtotal'] ?? 0),
                'tax' => $this->normalizeVndPrice($d['tax'] ?? 0),
                'total' => $this->normalizeVndPrice($d['total'] ?? 0),
            ]);
        } else {
            Session::put('checkout', [
                'discount' => 0.0,
                'subtotal' => $this->normalizeVndPrice(Cart::instance('cart')->subtotal()),
                'tax' => $this->normalizeVndPrice(Cart::instance('cart')->tax()),
                'total' => $this->normalizeVndPrice(Cart::instance('cart')->total()),
            ]);
        }
    }

    public function order_confirmation()
    {
        if (Session::has('order_id')) {
            $order = Order::with(['orderItems.product', 'transaction'])
                ->find(Session::get('order_id'));
            if (! $order) {
                Session::forget('order_id');

                return redirect()->route('cart.index');
            }

            return view('order-confirmation', compact('order'));
        }
        return redirect()->route('cart.index');
    }
   
}
