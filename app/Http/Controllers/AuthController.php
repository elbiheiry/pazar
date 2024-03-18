<?php

namespace App\Http\Controllers;

use App\Member;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Auth;
use Hash;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class AuthController extends Controller
{
    public function postRegister(Request $request)
    {
        // dd(json_decode($request->getContent(), true));
        $v = validator($request->all(),[
            'email'=>'required|unique:member,email',
            'name'=>'required',
            'username'=>'required|unique:member,username',
            'password'=>'required|min:6'
        ],[
                'email.required' => 'Please enter your email',
                'email.unique' =>  'Email is already taken',
                'email.email' => 'Please enter a valid email',
                'password.required' =>  'Please enter your password',
                'password.min' =>  'Password should be more than 6 digits',
                'name.required' => 'Please enter your name',
                'username.required' => 'Please enter your username',
                'username.unique' => 'Username is already taken'
            ]
        );
        if ($v->fails())
        {
            return ['status'=>'error','message'=>implode("<br />",$v->errors()->all())];
        }

        $member = new  Member();
        $member->name = $request->name;
        $member->username = $request->username;
        $member->email = $request->email;
        $member->password = bcrypt($request->password);
        $member->api_token = str_random(60);
        $member->city_id = $request->city_id;
        $member->phone = $request->phone;
        $member->address = $request->address;
        $member->desc = $request->desc;

        if ($member->save())
        {
            $token = JWTAuth::fromUser($member);

            $user = $member->with(['orders' , 'wishlists:member_id,post_id','posts'])->first();
            // $single_member = Member::with(['orders' , 'wishlists:member_id,post_id','posts'])->find($member->member_id);

            return response()->json(compact('user','token'),201);
        }
        return ['status'=>'error' ,'message' => 'Error please try again later'];
    }

    public function postLogin(Request $request)
    {
        $credentials = $request->only('email', 'password');

        try {
            if (! $token = JWTAuth::attempt($credentials)) {
                return response()->json(['error' => 'invalid_credentials'], 400);
            }
        } catch (JWTException $e) {
            return response()->json(['error' => 'could_not_create_token'], 500);
        }
        
        $user = Auth::user();

        return response()->json(compact('user','token'));
    }

    public function postResetPassword(Request $request)
    {
        try {

            if (! $user = JWTAuth::parseToken()->authenticate()) {
                return response()->json(['user_not_found'], 404);
            }

        } catch (Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {

            return response()->json(['token_expired'], $e->getStatusCode());

        } catch (Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {

            return response()->json(['token_invalid'], $e->getStatusCode());

        } catch (Tymon\JWTAuth\Exceptions\JWTException $e) {

            return response()->json(['token_absent'], $e->getStatusCode());

        }
        $v =  validator($request->all(), [
            'email' => 'required',
        ],[
            'email.required' => 'Please enter your email'
        ]);
        if ($v->fails()){
            return ['status'=>'error','message'=>implode("\n",$v->errors()->all())];
        }


        $member = Member::where('email',$request->email)->with(['orders' , 'wishlists:member_id,post_id','posts'])->first();

        if (!$member)
        {
            return ['status'=>'error','message'=> 'User isn\'t found' ,'code' => '30'];
        }

        return ['status'=>'success' , 'data' => $member];
    }

    public function postChangePassword(Request $request)
    {
        if (! $user = JWTAuth::parseToken()->authenticate()) {
            return response()->json(['user_not_found'], 404);
        }

        if (!Hash::check($request->old_password, $user->password)){
            return ['status' => 'error' , 'message' => 'Old password is incorrect'];
        }

        $v = validator($request->all() ,[
            'old_password'=>'required',
            'password'=>'required|min:6',
            'password_confirmation' => 'same:password'
        ] ,[
            'old_password.required' => 'Please enter old password',
            'password.required' => 'Please enter new password',
            'password.min' => 'Password should be more than 6 digits',
            'password_confirmation.same' =>  'Password mismatch'
        ]);

        if ($v->fails())
        {
            return ['status' => 'error' ,'message' => implode('<br />' ,$v->errors()->all())];
        }

        $user->password = bcrypt($request->input('password'));

        $user->update([
            'password' => bcrypt($request->password)
        ]);
        
        $member = $user->with(['orders' , 'wishlists:member_id,post_id','posts'])->first();

        return ['status'=>'success' , 'data' => $member];
    }
}