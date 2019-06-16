<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\User;
use Auth;

class AuthController extends Controller
{
    /**
     * Get a JWT token via given credentials.
     *
     * @param  \Illuminate\Http\Request  $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request) {
        $this->validate($request, [
            'email'    => 'required',
            'password' => 'required'
        ]);

        $user = User::where('email', $request->input('email'))->first();
        if(Hash::check($request->input('password'), $user->password)){
            // you can use any logic to create the apikey. You can use md5 with other hash function, random shuffled string characters also
            $apikey = base64_encode(str_random(32));
            User::where('email', $request->input('email'))->update(['api_key' => $apikey]);
            return response()->json(['status' => 'success','api_key' => $apikey]);
        } else {
            return response()->json(['status' => 'fail'],401);
        }
    }

    /**
     * Get the authenticated User
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        $user = Auth::user();
        return response()->json($user);
    }
}
