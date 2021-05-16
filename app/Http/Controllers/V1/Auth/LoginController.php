<?php

namespace Crater\Http\Controllers\V1\Auth;

use Crater\Http\Controllers\Controller;
use Crater\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Crater\Models\User;

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
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');
        
        if (Auth::attempt($credentials)) {
            $user = User::where('email', $credentials['email'])->first();
            // Authentication passed...

            if ( !$user->isVerified() ) {
                Auth::logout();
                return response()->json([
                    'success' => false,
                    'message' => 'Debes verificar tu email para poder iniciar sesion'
                ]);
            }

            return response()->json([
                'success' => true,
                'redirectTo' => $this->redirectTo,
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Email y/o password incorrecto(s).'
            ]);
        }
    }
}
