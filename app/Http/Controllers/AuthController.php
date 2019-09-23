<?php

namespace App\Http\Controllers;

use App\Notifications\SignupActivate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\User;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class AuthController extends Controller
{
  public $avatar = null;

  public function signup(Request $request)
  {
    $request->validate([
      'name' => 'required|string',
      'email' => 'required|string|email|unique:users',
      'password' => 'required|string|confirmed',
    ]);

    // validation is done
    if ($request->hasFile('avatar')) {
      $this->validate($request, ['avatar' => 'image|mimes:jpeg,png,jpg,gif,svg|max:5000',]);
      // resize the image if its bigger than 5mb?
      $file = $request->file('avatar');
      $this->avatar = rand(1111, 999999999) . Carbon::now()->timestamp . '.' . $file->getClientOriginalExtension();
      $request->file('avatar')->move("storage", $this->avatar);
    }
    $user = new User([
      'name' => $request->name,
      'email' => $request->email,
      'password' => bcrypt($request->password),
      'activation_token' => str_random(60)
    ]);
    $user->save();
    return $this->signin($request);
  }

  public function signin(Request $request)
  {
    $request->validate([
      'email' => 'required|string|email',
      'password' => 'required|string',
      'remember_me' => 'boolean'
    ]);

    $credentials = request(['email', 'password']);

    if (!Auth::attempt($credentials))
      return response()->json([
        'message' => 'Unauthorized'
      ], 400);

    $user = $request->user();

    $tokenResult = $user->createToken('Personal Access Token');

    $token = $tokenResult->token;

    if ($request->remember_me)
      $token->expires_at = Carbon::now()->addWeeks(1);
    $token->save();

    return response()->json([
      'user' => $user,
      'access_token' => $tokenResult->accessToken,
      'token_type' => 'Bearer',
      'expires_at' => Carbon::parse(
        $tokenResult->token->expires_at
      )->toDateTimeString()
    ]);
  }

  public function user(Request $request)
  {
    return response()->json($request->user());
  }

  public function logout(Request $request)
  {
    $request->user()->token()->revoke();
    $request->user()->save();
    return response()->json([
      'message' => 'Successfully logged out'
    ]);
  }
}