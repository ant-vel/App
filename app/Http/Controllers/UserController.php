<?php

namespace app\Http\Controllers;

/*
 * Antvel - Users Controller
 *
 * @author  Gustavo Ocanto <gustavoocanto@gmail.com>
 */

use App\Helpers\File;
use App\Helpers\userHelper;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class UserController extends Controller
{
    /**
     * Save the user preferences.
     *
     * @param [String] $index user preference key array
     * @param [Array]  $tags  products tags
     */
    public static function setPreferences($index = '', $tags = [])
    {
        $user = \Auth::user();
        if ($user) {
            $userHelper = new UserHelper();
            $categories = ProductsController::getTagsCategories($tags);
            $user->preferences = $userHelper->preferencesToJson($user->preferences, $index, $tags, $categories);
            $user->save();
        }
    }

    /**
     * Return the users preferences taking in account the key requered.
     *
     * @param [interger] $user_id         user id
     * @param [string]   $preferences_key user preferences array key
     *
     * @return [Array] info to evaluate user products suggestion
     */
    public static function getPreferences($preferences_key = '')
    {
        $preferences = (\Auth::user()) ? \Auth::user()->preferences : '';

        //getting the needle
        $userHelper = new UserHelper();

        return $userHelper->getPreferencesNeedle($preferences, $preferences_key);
    }

    public function getPoints()
    {
        $points = ['points' => '0'];
        $user = \Auth::user();
        if ($user) {
            $points = ['points' => $user->current_points];
        }

        return \Response::json($points);
    }
}
