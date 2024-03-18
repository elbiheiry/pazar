<?php

namespace App\Http\Controllers;

use App\Blacklist;
use App\Comment;
use App\Member;
use App\Order;
use App\Post;
use Carbon\Carbon;
use Illuminate\Http\Request;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class MainController extends Controller
{
    //
    public function getBlackList()
    {
        $lists = Blacklist::all();

        return $lists;
    }

    public function getSearchBlacklist(Request $request)
    {
        $search = $request->search;

        $list = Blacklist::where('number' , 'like' , '%'.$search.'%')->get();

        return $list;
    }

    public function getSingleOrder($id)
    {
        $order = Order::find($id);

        return $order;
    }

    public function getBlog()
    {
        $posts = Post::where('module_title' , 'blog')->with(['details','comments','seller','seller_city', 'fields' , 'gallery' , 'fieldValue' , 'category','comments'])->get();

        return $posts;
    }

    public function postAddComment(Request $request)
    {
        try {

            if (! $member = JWTAuth::parseToken()->authenticate()) {
                return response()->json(['user_not_found'], 404);
            }

        } catch (Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {

            return response()->json(['token_expired'], $e->getStatusCode());

        } catch (Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {

            return response()->json(['token_invalid'], $e->getStatusCode());

        } catch (Tymon\JWTAuth\Exceptions\JWTException $e) {

            return response()->json(['token_absent'], $e->getStatusCode());

        }
        $v = validator($request->all(),[
            'message'=>'required'
        ],[
                'message.required' => 'You must write your comment'
            ]
        );
        if ($v->fails())
        {
            return ['status'=>'error','message'=>implode("<br />",$v->errors()->all())];
        }

        $comment = new Comment();

        $comment->post_id = $request->post_id;
        $comment->member_id = $member->member_id;
        $comment->content = $request->message;
        $comment->price = $request->price;
        $comment->date = Carbon::now();

        if ($comment->save()){
            return ['status' => 'success' ,'data' => $comment];
        }
    }
    
    public function getComment($post_id){
        try {
            $comments = Comment::where('post_id' , $post_id)->with('member:member_id,name')->get();
            
            return $comments;
        }
        catch (\Exception $e) {
            return ['status' => 'error' , 'data' => $e->getMessage()];
        }
    }
}
