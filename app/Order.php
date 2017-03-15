<?php

namespace app;

/*
 * Antvel - Order Model
 *
 * @author  Gustavo Ocanto <gustavoocanto@gmail.com>
 */

use App\Address;
use App\Eloquent\Model;
use App\Http\Controllers\ProductsController as ProductsController;
use App\Http\Controllers\UserController as UserController;
use App\Log;
use App\Notice;
use App\OrderDetail;
use App\Product;
use App\VirtualProduct;
use App\VirtualProductOrder;
use Illuminate\Support\Facades\Mail;

class Order extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'orders';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'address_id',
        'status',
        'type',
        'description',
        'end_date',
        'seller_id',
    ];

    protected $appends = ['translatedStatus'];

    public function user()
    {
        return $this->belongsTo('App\User');
    }

    public function details()
    {
        return $this->hasMany('App\OrderDetail');
    }

    public function freeproducts()
    {
        return $this->belongsToMany('App\FreeProduct')->withTimestamps();
    }

    // public static function create(array $options = [])
    // {
    //     //separate order details
    //     $details = [];
    //     if (isset($options['details'])) {
    //         $details = $options['details'];
    //     }
    //     if (isset($options['detail'])) {
    //         $details[] = $options['detail'];
    //     }
    //     unset($options['detail'], $options['details']);
    //     $order = parent::create($options);
    //     if (count($details)) {
    //         $order->inDetail()->saveMany(OrderDetail::createModels($details));
    //     }

    //     return $order;
    // }

    public function save(array $options = [])
    {
        $status_changed = (isset($this->original['status']) && $this->attributes['status'] != $this->original['status']) || (isset($options['status']) && $this->attributes['status'] != $options['status']);
        $saved = parent::save($options);
        if ($saved) {
            $this->createLog();
            if ($status_changed) {
                $this->sendNotice();
            }
        }

        return $saved;
    }

    public function getDetailsAttribute()
    {
        return $this->hasMany('App\OrderDetail')->get();
    }

    public function getTranslatedStatusAttribute()
    {
        return trans('globals.order_status.'.$this->status);
    }

    public function inDetail()
    {
        return $this->hasMany('App\OrderDetail');
    }

    public function createLog()
    {
        $actions = [];
        foreach (trans('globals.action_types') as $value) {
            if ($value['source_type'] == 'order') {
                $actions[$value['action']] = $value['id'];
            }
        }

        if (isset($actions[$this->status])) {
            Log::create([
            'action_type_id' => $actions[$this->status],
            'source_id'      => $this->id,
            'user_id'        => $this->user_id,
            'details'        => "Order #$this->id $this->status",
        ]);
        }

        return $this;
    }

    public function sendNotice()
    {
        if (!empty($this->seller_id) && !empty($this->user_id) && $this->type == 'order') {
            switch ($this->status) {
            case 'open':
                Notice::create([
                    'action_type_id' => 1,
                    'source_id'      => $this->id,
                    'user_id'        => $this->seller_id,
                    'sender_id'      => $this->user_id,
                ]);
            break;
            case 'pending':
                Notice::create([
                    'action_type_id' => 2,
                    'source_id'      => $this->id,
                    'user_id'        => $this->user_id,
                    'sender_id'      => $this->seller_id,
                ]);
            break;
            case 'closed':
                Notice::create([
                    'action_type_id' => 8,
                    'source_id'      => $this->id,
                    'user_id'        => $this->seller_id,
                    'sender_id'      => $this->user_id,
                ]);
            break;
            case 'cancelled':
                Notice::create([
                    'action_type_id' => 9,
                    'source_id'      => $this->id,
                    'users'          => [$this->seller_id, $this->user_id],
                ]);
            break;
            case 'sent':
                Notice::create([
                    'action_type_id' => 11,
                    'source_id'      => $this->id,
                    'user_id'        => $this->user_id,
                    'sender_id'      => $this->seller_id,
                ]);
            break;
        }
        }
        // $this->sendMail();
        return $this;
    }

    public function sendMail()
    {
        switch ($this->status) {
            case 'pending':
                //Sends the user a mail to nitify that the
                $template = 'emails.order_status_changed';
                $buyer_user = User::findOrFail($this->user_id);
                $email = $buyer_user->email;
                $email_message = trans('email.status_changed.changed_to_pending');
                $subject = trans('email.status_changed.subject1').$this->id.' '.trans('email.status_changed.subject2').' '.trans('store.pending');
            break;
            case 'closed':
                //Sends the buyer a mail to notify that the Order is Closed
                $template = 'emails.order_status_changed';
                $seller_user = User::findOrFail($this->seller_id);
                $email = $seller_user->email;
                $email_message = trans('email.status_changed.changed_to_closed');
                $subject = trans('email.status_changed.subject1').$this->id.' '.trans('email.status_changed.subject2').' '.trans('store.closed');
            break;
            case 'sent':
                //Sends the user a mail to notify that the order is Sent
                $template = 'emails.order_status_changed';
                $buyer_user = User::findOrFail($this->user_id);
                $email = $buyer_user->email;
                $email_message = trans('email.status_changed.changed_to_sent');
                $subject = trans('email.status_changed.subject1').$this->id.' '.trans('email.status_changed.subject2').' '.trans('store.sent');
            break;
        }
        $data = [
            'order_id'      => $this->id,
            'email'         => $email,
            'email_message' => $email_message,
            'new_status'    => $this->status,
            'subject'       => $subject,
        ];
        if (isset($template)) {
            Mail::queue($template, $data, function ($message) use ($data) {
                $message->to($data['email'])->subject($data['subject']);
            });
        }
    }

    /**
     * Start the checkout process for any type of order.
     *
     * @param int $type_order Type of order to be processed
     *
     * @return Response
     */
    public static function placeOrders($type_order)
    {
        $cart = self::ofType($type_order)->auth()->whereStatus('open')->orderBy('id', 'desc')->first();

        $show_order_route = ($type_order == 'freeproduct') ? 'freeproducts.show' : 'orders.show_cart';

        $cartDetail = OrderDetail::where('order_id', $cart->id)->get();

        $address_id = 0;

        //When address is invalid, it is because it comes from the creation of a free product. You must have a user direction (Default)
        if (is_null($cart->address_id)) {
            $useraddress = Address::auth()->orderBy('default', 'DESC')->first();
            if ($useraddress) {
                $address_id = $useraddress->address_id;
            } else {
                return trans('address.no_registered');
            }
        } else {
            $address_id = $cart->address_id;
        }

        $address = Address::where('id', $address_id)->first();

        //Checks if the user has points for the cart price and the store has stock
        //and set the order prices to the current ones if different
        //Creates the lists or sellers to send mail to
        $total_points = 0;
        $seller_email = [];
        foreach ($cartDetail as $orderDetail) {
            $product = Product::find($orderDetail->product_id);
            $seller = User::find($product->user_id);
            if (!in_array($seller->email, $seller_email)) {
                $seller_email[] = $seller->email;
            }
            $total_points += $orderDetail->quantity * $product->price;
            if ($orderDetail->price != $product->price) {
                $orderDetail->price = $product->price;
                $orderDetail->save();
            }
            if ($product->type != 'item') {
                $virtual = VirtualProduct::where('product_id', $orderDetail->product_id)->get();
                $first = $virtual->first();
                //$first=null;
                //foreach ($virtual as $row){
                    //$first=$row;
                    //break;
                //}
                switch ($product->type) {
                    case 'key': case 'software_key':
                        $virtualOrder = VirtualProductOrder::where('virtual_product_id', $first->id)
                                                         ->where('order_id', $orderDetail->order_id)
                                                         ->where('status', 1)->get();
                        if ((count($virtual) - 1) < count($virtualOrder)) {
                            return trans('store.insufficientStock');
                        }
                    break;
                    default: break;
                }
            } elseif ($product->stock < $orderDetail->quantity) {
                return trans('store.insufficientStock');
            }
        }

        //Checks if the user has points for the cart price
        $user = \Auth::user();
        if ($user->current_points < $total_points && config('app.payment_method') == 'Points') {
            return trans('store.cart_view.insufficient_funds');
        }

        if (config('app.payment_method') == 'Points') {
            $negativeTotal = -1 * $total_points;
            //7 is the action type id for order checkout
            $pointsModified = $user->modifyPoints($negativeTotal, 7, $cart->id);
        } else {
            $pointsModified = true;
        }

        if ($pointsModified) {
            //Separate the order for each seller
            //Looks for all the different sellers in the cart
            $sellers = [];
            foreach ($cartDetail as $orderDetail) {
                if (!in_array($orderDetail->product->user_id, $sellers)) {
                    $sellers[] = $orderDetail->product->user_id;
                }
            }
            foreach ($sellers as $seller) {
                //Creates a new order and address for each seller
                $newOrder = new self();
                $newOrder->user_id = $user->id;
                $newOrder->address_id = $address->id;
                $newOrder->status = ($type_order == 'freeproduct') ? 'paid' : 'open';
                $newOrder->type = ($type_order == 'freeproduct') ? 'freeproduct' : 'order';
                $newOrder->seller_id = $seller;
                $newOrder->save();
                $newOrder->sendNotice();
                //moves the details to the new orders
                foreach ($cartDetail as $orderDetail) {
                    if ($orderDetail->product->user_id == $seller) {
                        $orderDetail->order_id = $newOrder->id;
                        $orderDetail->save();
                    }

                    //Increasing product counters.
                    ProductsController::setCounters($orderDetail->product, ['sale_counts' => trans('globals.product_value_counters.sale')], 'orders');

                    //saving tags in users preferences
                    if (trim($orderDetail->product->tags) != '') {
                        UserController::setPreferences('product_purchased', explode(',', $orderDetail->product->tags));
                    }
                }
            }

            //virtual products
            //Changes the stock of each product in the order
            foreach ($cartDetail as $orderDetail) {
                $product = Product::find($orderDetail->product_id);
                $product->stock = $product->stock - $orderDetail->quantity;
                $product->save();
                if ($product->type != 'item') {
                    $virtual = VirtualProduct::where('product_id', $orderDetail->product_id)->where('status', 'open')->get();
                    switch ($product->type) {
                        case 'key':
                            $first = VirtualProduct::where('product_id', $orderDetail->product_id)->where('status', 'cancelled')->first();
                            foreach ($virtual as $row) {
                                $virtualOrder = VirtualProductOrder::where('order_id', $cart->id)->where('virtual_product_id', $first->id)->where('status', 1)->first();
                                if ($virtualOrder) {
                                    $virtualOrder->virtual_product_id = $row->id;
                                    $virtualOrder->order_id = $orderDetail->order_id;
                                    $virtualOrder->status = 2;
                                    $virtualOrder->save();
                                    $row->status = 'paid';
                                    $row->save();
                                } else {
                                    break;
                                }
                            }
                        break;
                        default: break;
                    }
                }
            }
            foreach ($seller_email as $email) {
                $mailed_order = self::where('id', $newOrder->id)->with('details')->get()->first();

                //Send a mail to the user: Order has been placed
                $data = [
                    'orderId' => $newOrder->id,
                    'order'   => $mailed_order,
                ];
                //dd($data['order']->details,$newOrder->id);
                $title = trans('email.new_order_for_user.subject')." (#$newOrder->id)";
                Mail::queue('emails.neworder', compact('data', 'title'), function ($message) use ($user) {
                    $message->to($user->email)->subject(trans('email.new_order_for_user.subject'));
                });
                //Send a mail to the seller: Order has been placed
                $title = trans('email.new_order_for_seller.subject')." (#$newOrder->id)";
                Mail::queue('emails.sellerorder', compact('data', 'title'), function ($message) use ($email) {
                    $message->to($email)->subject(trans('email.new_order_for_seller.subject'));
                });
            }

            return;
        } else {
            return trans('store.insufficientFunds');
        }
    }

    public function scopeOfType($query, $type)
    {
        return $query->whereType($type);
    }

    public function scopeOfStatus($query, $status)
    {
        return $query->where('orders.status', $status);
    }

    public function scopeOfDates($query, $from, $to = '')
    {
        if (trim($from) == '' && trim($to) == '') {
            return;
        }

        if (trim($from) != '' && trim($to) != '') {
            return $query->whereBetween(\DB::raw('DATE(orders.created_at)'), [$from, $to]);
        } elseif (trim($from) != '' && trim($to) == '') {
            return $query->where(\DB::raw('DATE(orders.created_at)'), $from);
        } elseif (trim($from) == '' && trim($to) != '') {
            return $query->where(\DB::raw('DATE(orders.created_at)'), $to);
        }
    }
}
