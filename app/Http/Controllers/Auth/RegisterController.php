<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use App\Models\Admin;
use App\Models\User;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Auth\Events\Registered;
use Illuminate\Routing\Router;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = '/';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(Router $router)
    {
        $routeUri = $router->getCurrentRoute()->uri;
        if (strpos($routeUri, 'admin') !== false) {
            $this->middleware('auth:admin');
        } else {
            $this->middleware('guest')->except('logout');
        }
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\Models\User
     */
    protected function create(array $data)
    {
        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);
    }
    /**
     * サイト管理者
     */
    protected function adminValidator(array $data)
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:admins'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ];
        $message = [ //フロント側でもほとんどチェックかかるが念の為
            'name.required' => '名前を入力してください',
            'email.required' => 'メールアドレスを入力してください',
            'email.email' => 'メールアドレスの形式で入力してください',
            'password.required' => 'パスワードを入力してください',
            'password.required' => 'パスワードを入力してください',
            'password.confirmed' => 'パスワードが一致しません',
        ];

        return Validator::make($data, $rules, $message);
    }
    public function showAdminRegisterForm()
    {
        return view('auth.register', ['authgroup' => 'admin']);
    }
    public function registerAdmin(Request $request)
    {
        $this->adminValidator($request->all())->validate();

        event(new Registered($user = $this->createAdmin($request->all())));

        Auth::guard('admin')->login($user);

        if ($response = $this->registeredAdmin($request, $user)) {
            return $response;
        }

        return $request->wantsJson()
            ? new JsonResponse([], 201)
            : redirect(route('admin-home'));
    }
    protected function createAdmin(array $data)
    {
        return Admin::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);
    }
    protected function registeredAdmin(Request $request, $user)
    {
        //
    }
}