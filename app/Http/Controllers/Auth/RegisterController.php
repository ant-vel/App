<?php

namespace App\Http\Controllers\Auth;

use Antvel\User\Auth\Register;
use App\Http\Controllers\Controller;
use Antvel\User\Requests\RegisterRequest;
use Illuminate\Foundation\Auth\RegistersUsers;

class RegisterController extends Controller
{
    use RegistersUsers;

    /**
     * Where to redirect users after login / registration.
     *
     * @var string
     */
    protected $redirectTo = '/login';

    /**
     * The Antvel sessions driver.
     *
     * @var Antvel\Components\Customer\Register
     */
    protected $register = null;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(Register $register)
    {
        $this->middleware('guest');

        $this->register = $register;
    }

    /**
     * Show the registration form.
     *
     * @return void
     */
    protected function showRegistrationForm()
    {
        return view('auth.register', [
            'email' => session()->has('email') ? session()->get('email') : ''
        ]);
    }

    /**
     * Handles the user registration.
     *
     * @param RegisterRequest $request
     * @return void
     */
    protected function register(RegisterRequest $request)
    {
        $this->register
            ->store($request)
            ->withEmail($this->emailBody())
            ->withMessage($this->message($request));

        return redirect($this->redirectTo);
    }

    /**
     * Returns the registration success message.
     *
     * @param  RegisterRequest $request
     * @return string
     */
    protected function message($request) : string
    {
        $fullName = $request['first_name'] . ' ' . $request['last_name'];

        return trans('user.signUp_message', ['_name' => $fullName]);
    }

    /**
     * Returns the registration email body.
     *
     * @return array
     */
    protected function emailBody() : array
    {
        return [
            'subject' => trans('user.emails.verification_account.subject'),
            'view' => 'emails.accountVerification',
        ];
    }

    /**
     * Confirms the user subscription.
     *
     * @param  string $token
     * @param  string $email
     * @return void
     */
    protected function confirm($token, $email)
    {
        $confirm = $this->register->confirm($token, $email);

        if ($confirm->response() == 'ok') {
            return redirect('/');
        }

        $confirm->flashError(trans('user.account_verified_error_message'));

        return redirect($this->redirectTo);
    }
}
