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
use App\Eloquent\Model;
use Antvel\User\hasAntvel;
use Illuminate\Auth\Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Auth\Passwords\CanResetPassword;
use App\Notifications\Auth\ResetPasswordNotification;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;

class User extends Model implements AuthenticatableContract, CanResetPasswordContract
{
    use Authenticatable, CanResetPassword, SoftDeletes, Notifiable, hasAntvel;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'users';

    protected $fillable = [
        'facebook', 'mobile_phone', 'work_phone', 'description',
        'pic_url', 'language', 'website', 'twitter',
        'nickname', 'email', 'password', 'role',
        'disabled_at', 'confirmation_token'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = ['password', 'remember_token'];

    protected $dates = ['deleted_at'];

    // public function relationsToArray()
    // {
    //     return array_merge($this->attributesToArray(), $this->profile->attributesToArray());
    // }

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

    /**
     * Send the password reset notification.
     *
     * @param  string  $token
     * @return void
     */
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPasswordNotification($token));
    }
}
