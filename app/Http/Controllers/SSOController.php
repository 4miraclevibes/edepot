<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class SSOController extends Controller
{
    protected $ssoUrl;

    public function __construct()
    {
        // Mengambil URL SSO dari environment variables
        $this->ssoUrl = env('SSO_API_URL');
    }

    public function dashboard(){
        $data = session('user');
        $wallet = $this->getUser();
        session(['wallet' => $wallet]);
        return view('dashboard', [
            'data' => $data,
            'wallet' => $wallet,
        ]);
    }

    public function login(Request $request)
    {
        // Permintaan login ke server SSO
        $response = Http::post("{$this->ssoUrl}/login", [
            'email' => $request->input('email'),
            'password' => $request->input('password')
        ]);

        // Memeriksa respons dari server SSO
        if ($response->successful()) {
            if($response['message'] == 'Unauthorized'){
                return back()->with('error', 'Login failed. Please check your credentials.');
            }
            if($response['user']['wallet']['status'] == 'suspend'){
                return back()->with('error', 'Your account is suspended. Please contact support.');
            }
            session(['sso_token' => $response['access_token']]);
            session(['user' => $response['user']]);
            session(['wallet' => $response['user']['wallet']['balance']]);
            // Mendapatkan data pengguna dan menyimpannya dalam session
            return redirect('/dashboard');
        } else {
            // Jika gagal, kembalikan ke halaman login dengan pesan error
            return back()->with('error', 'Login failed. Please check your credentials.');
        }
    }

    public function getUser()
    {
        // Mendapatkan token dari session
        $token = session('sso_token');
        // $id = session('user.id');
        // Memeriksa apakah token ada di session
        if (!$token) {
            // Jika tidak ada, kembalikan response error
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Permintaan untuk mendapatkan data pengguna dari server SSO
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->get("{$this->ssoUrl}/user/show");

        // Memeriksa respons dari server SSO
        if ($response->successful()) {
            // Jika sukses, kembalikan data pengguna
            // dd($response->json());
            return $response['data']['wallet']['balance'];
        } else {
            // Jika gagal, kembalikan response error
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }

    public function logout(Request $request)
    {
        // Mendapatkan token dari session
        $token = session('sso_token');
        if (!$token) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // Permintaan logout ke server SSO
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->post("{$this->ssoUrl}/logout");

        if ($response->successful()) {
            // Hapus token dari session
            $request->session()->forget('sso_token');

            // Hapus data pengguna dari session (jika perlu)
            $request->session()->forget('user');
            $request->session()->forget('wallet');
            return redirect()->route('login');
            // return response()->json(['message' => 'Successfully logged out']);
        } else {
            return response()->json(['message' => 'Logout failed'], 500);
        }
    }
}
