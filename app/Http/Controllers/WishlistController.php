<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Surfsidemedia\Shoppingcart\Facades\Cart;

class WishlistController extends Controller
{
    private function normalizeVndPrice($value): float
    {
        if ($value === null) {
            return 0.0;
        }
        $raw = trim((string) $value);
        if ($raw === '') {
            return 0.0;
        }
        $digits = preg_replace('/\D+/', '', $raw);

        return $digits === '' ? 0.0 : (float) $digits;
    }

    public function add_to_wishlist(Request $request)
    {
        $wishlist = Cart::instance('wishlist');
        foreach ($wishlist->content() as $item) {
            if ((int) $item->id === (int) $request->id) {
                return redirect()->back();
            }
        }

        $wishlist->add(
            $request->id,
            $request->name,
            $request->quantity,
            $request->price
        )->associate('App\Models\Product');

        return redirect()->back();
    }

    public function remove_from_wishlist(Request $request)
    {
        $request->validate(['id' => 'required']);
        $id = (int) $request->input('id');
        $wishlist = Cart::instance('wishlist');
        foreach ($wishlist->content() as $item) {
            if ((int) $item->id === $id) {
                $wishlist->remove($item->rowId);
                break;
            }
        }

        return redirect()->back();
    }

    public function clear()
    {
        Cart::instance('wishlist')->destroy();

        return redirect()->route('wishlist.index');
    }

    public function move_to_cart(Request $request)
    {
        $request->validate(['id' => 'required']);
        $id = (int) $request->input('id');

        // Phải lấy dòng wishlist khi instance đang là wishlist; không gọi instance('cart') trước —
        // Cart facade là singleton, đổi instance sẽ khiến content()/remove() đọc nhầm giỏ hàng.
        Cart::instance('wishlist');
        $toMove = null;
        foreach (Cart::instance('wishlist')->content() as $item) {
            if ((int) $item->id === $id) {
                $toMove = $item;
                break;
            }
        }

        if ($toMove === null) {
            return redirect()->back();
        }

        $qty = max(1, (int) $toMove->qty);
        $price = $this->normalizeVndPrice($toMove->price);

        Cart::instance('cart')->add($toMove->id, $toMove->name, $qty, $price)
            ->associate('App\Models\Product');

        Cart::instance('wishlist')->remove($toMove->rowId);

        return redirect()->back();
    }

    public function move_all_to_cart()
    {
        Cart::instance('wishlist');
        $items = Cart::instance('wishlist')->content()->values();

        foreach ($items as $item) {
            $qty = max(1, (int) $item->qty);
            $price = $this->normalizeVndPrice($item->price);

            Cart::instance('cart')->add($item->id, $item->name, $qty, $price)
                ->associate('App\Models\Product');
        }

        Cart::instance('wishlist')->destroy();

        return redirect()->route('wishlist.index');
    }

    public function index()
    {
        $items = Cart::instance('wishlist')->content();

        return view('wishlist', compact('items'));
    }
}
