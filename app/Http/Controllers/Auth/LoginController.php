<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use Antvel\User\Auth\Sessions;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * The Antvel sessions driver.
     *
     * @var Sessions
     */
    protected $antvel = null;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(Sessions $antvel)
    {
        $this->middleware('guest', ['except' => 'logout']);

        $this->antvel = $antvel;
    }

    /**
     * Process the user login.
     *
     * @param  Request $request
     * @return void
     */
    public function login(Request $request)
    {
        return $this->antvel->authenticate($request);
    }
}
