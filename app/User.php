<?php

namespace App;

/*
 * Antvel - Users Model
 *
 * @author  Gustavo Ocanto <gustavoocanto@gmail.com>
 */

use App\Log;
use App\Order;
use App\UserPoints;
use Antvel\User\Models\User as BaseUser;

class User extends BaseUser
{
    public function Product()
    {
        return $this->hasMany('App\Product');
    }

    //Cart Manage
    public function getCartCount()
    {
        $basicCart = Order::ofType('cart')->where('user_id', $this->id)->first();
        if (!($basicCart)) {
            return 0;
        } else {
            $totalItems = 0;
            foreach ($basicCart->details  as $orderDetail) {
                $totalItems += $orderDetail->quantity;
            }

            return $totalItems;
        }
    }

    public function getCartContent()
    {
        $basicCart = Order::ofType('cart')->where('user_id', $this->id)->first();
        if (!($basicCart)) {
            return [];
        } else {
            return $basicCart->details;
        }
    }

    public function modifyPoints($points, $actionTypeId, $sourceId)
    {
        $data = ['action_type_id' => $actionTypeId, 'source_id' => $sourceId, 'details' => $points, 'user_id' => $this->id];
        $log = Log::create($data);

        $userPoints = new UserPoints();
        $userPoints->user_id = $this->id;
        $userPoints->action_type_id = $actionTypeId;
        $userPoints->source_id = $sourceId;
        $userPoints->points = $points;
        if ($userPoints->save()) {
            $this->current_points = $this->current_points + $points;
            //Action type = 9 is for canceled orders, the user should not add to accumulated points
            if (($points > 0) && ($actionTypeId != 9)) {
                $this->accumulated_points = $this->accumulated_points + $points;
            }
            if ($this->save()) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }
}
