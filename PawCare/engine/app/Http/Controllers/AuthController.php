<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\Events\Registered;
use Illuminate\Database\QueryException;

class AuthController extends Controller
{
    public function index()
    {
        return view('auth.login');
    }

    public function create()
    {
        return view('auth.register');
    }

    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required',
            'password' => 'required',
        ]);
        $data = [
            'email' => $request->input('email'),
            'password' => $request->input('password'),
        ];
        if(Auth::attempt($data)){
            return redirect('/home');
        }else{
            Session()->flash('error', 'Email atau Password Salah');
            return back();
        }
    }

   public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required',
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if ($request->password !== $request->confirmPassword) {
            Session()->flash('error', 'Konfirmasi Password Tidak Sama');
            return back();
        }

        try {
            $user = new User();
            $user->name = $request->name;
            $user->email = $request->email;
            $user->password = Hash::make($request->password);
            $user->level = 'user';
            $user->country = $request->country;
            $user->phoneNumber = $request->phoneNumber;
            $user->save();

            Auth::login($user);

            event(new Registered($user));

            return redirect('/email-verify');
        } catch (QueryException $e) {
            if ($e->errorInfo[1] == 1062) { 
                Session()->flash('error', 'Email sudah terdaftar. Silakan gunakan email lain.');
                return back();
            } else {
                
                Session()->flash('error', 'Terjadi kesalahan saat mendaftar.');
                return back();
            }
        }
    }

    public function logout()
    {
        Auth::logout();
        return redirect()->route('pawcare.home');
    }
}
