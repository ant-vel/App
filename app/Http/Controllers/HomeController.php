<?php

namespace app\Http\Controllers;

/*
 * Antvel - Home Controller
 *
 * @author  Gustavo Ocanto <gustavoocanto@gmail.com>
 */

use App\Order;
use App\Product;
use App\FreeProduct;
use App\Helpers\productsHelper;
use App\Http\Controllers\Controller;
use App\Http\Controllers\ProductsController;

class HomeController extends Controller
{
    public function index()
    {
        $panel = [
            'center' => [
                'width' => 10,
            ],
            'left' => [
                'width' => 2,
                'class' => 'home-no-padding',
            ],
        ];

        $helperProd = new productsHelper();

        $carousel = $helperProd->suggest('carousel');
        $viewed = $helperProd->suggest('viewed', 8);
        $categories = $helperProd->suggest('categories');
        $purchased = $helperProd->suggest('purchased');

        $suggestion = [
            'carousel'   => $carousel,
            'viewed'     => $viewed,
            'categories' => $categories,
            'purchased'  => $purchased,
        ];

        $helperProd->resetHaystack(); //reseting session id validator

        $events = [];
        if (config('app.offering_free_products')) {
            $events = FreeProduct::getNextEvents([
                'id',
                'description',
                'min_participants',
                'max_participants',
                'participation_cost',
                'start_date',
                'end_date',
            ], 4, date('Y-m-d'));
        }

        $tagsCloud = ProductsController::getTopRated(0, 20, true);

        $allWishes = '';
        $user = \Auth::user();
        if ($user) {
            $allWishes = Order::ofType('wishlist')->where('user_id', $user->id)->where('description', '<>', '')->get();
        }

        $i = 0; //carousel implementation
        $jumbotronClasses = ['jumbotron-box-left', 'jumbotron-box-right']; //carousel implementation

        $banner = [
            '/img/banner/01.png',
            '/img/banner/02.png',
            '/img/banner/03.png',
            '/img/banner/04.png',
        ];

        // $this->createTags();

        return view('home', compact('panel', 'suggestion', 'allWishes', 'events', 'tagsCloud', 'jumbotronClasses', 'i', 'banner'));
    }

    private function createTags()
    {
        $product = Product::select(['id', 'name'])->get();

        foreach ($product as $value) {
            $prod = Product::find($value->id);

            $prod->tags = str_replace(' ', ',', $value->name);

            $prod->save();
        }
    }

    //moved here while refactoring
    public function dashBoard()
    {
        $panel =  [
            'left'   => ['width' => '2', 'class' => 'user-panel'],
            'center' => ['width' => '10'],
        ];
        $query = Product::where('user_id', \Auth::id())->Free()->get();
        $products = ['active' => 0, 'inactive' => 0, 'lowStock' => 0, 'all' => $query->count()];
        foreach ($query as $row) {
            if ($row->status) {
                $products['active']++;
            } else {
                $products['inactive']++;
            }
            if ($row->stock <= $row->low_stock) {
                $products['lowStock']++;
            }
        }
        unset($query);
        $query = Order::auth()->ofType('order')->get();
        $orders = ['closed' => 0, 'open' => 0, 'cancelled' => 0, 'all' => $query->count(), 'total' => 0, 'nopRate' => 0];
        foreach ($query as $row) {
            if ($row->status == 'cancelled') {
                $orders['cancelled']++;
            } elseif ($row->status == 'closed') {
                $orders['closed']++;
            } else {
                $orders['open']++;
            }
            foreach ($row->details as $deta) {
                $orders['total'] += ($deta->quantity * $deta->price);
                if ($row->status == 'closed' && !$deta->rate) {
                    $orders['nopRate']++;
                }
            }
        }
        unset($query);
        $sales = null;
        if (\Auth::check() && \Auth::user()->hasRole(['business', 'admin'])) {
            $orders = Order::where('seller_id', \Auth::user()->id)->ofType('order')->get();
            $sales = ['closed' => 0, 'open' => 0, 'cancelled' => 0, 'all' => $orders->count(), 'total' => 0, 'rate' => 0, 'numRate' => 0, 'totalRate' => 0, 'nopRate' => 0];
            foreach ($orders as $row) {
                if ($row->status == 'cancelled') {
                    $sales['cancelled']++;
                } elseif ($row->status == 'closed') {
                    $sales['closed']++;
                } else {
                    $sales['open']++;
                }
                foreach ($row->details as $deta) {
                    $sales['total'] += ($deta->quantity * $deta->price);
                    if ($row->status == 'closed' && $deta->rate) {
                        $sales['numRate']++;
                        $sales['totalRate'] = $sales['totalRate'] + $deta->rate;
                    }
                    if ($row->status == 'closed' && !$deta->rate) {
                        $sales['nopRate']++;
                    }
                }
            }
            if ($sales['numRate']) {
                $sales['rate'] = $sales['totalRate'] / $sales['numRate'];
            }
        }

        return view('user.dashboard', compact('panel', 'products', 'orders', 'sales'));
    }
}
