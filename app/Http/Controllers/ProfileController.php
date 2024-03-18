<?php

namespace App\Http\Controllers;

use App\Member;
use Illuminate\Http\Request;
use Auth;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class ProfileController extends Controller
{

    public function getIndex()
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
        
        $member = $user->with(['orders' , 'wishlists:member_id,post_id','posts'])->first();

        return response()->json(compact('member'));
    }

    public function postIndex(Request $request)
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
        $user->name = $request->name;
        $user->desc = $request->desc;
        if ($request->passord){
            $user->password = bcrypt($request->password);
        }
        $user->email = $request->email;
        $user->phone = $request->phone;
        $user->address = $request->address;
        $user->city_id = $request->city_id;

        if ($user->save()){
            
            $member = $user->with(['orders' , 'wishlists:member_id,post_id','posts'])->first();
            
            return ['status' => 'success' ,'data' => $member];
        }else{
            return ['status' => 'error'];
        }

    }

    public function postTestToken()
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

        return ['status' => 'success' , 'data' => 'token is valid'];
    }
}
