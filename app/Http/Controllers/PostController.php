<?php

namespace App\Http\Controllers;

use App\FieldOption;
use App\Gallery;
use App\Member;
use App\Post;
use Illuminate\Http\Request;
use App\Wishlist;
use App\PostLang;
use App\Order;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use JWTAuth;
use Auth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Image;

class PostController extends Controller
{
    //
    public function getIndex(Request $request)
    {
        if($request->id){
            $post = Post::with(['details','seller','seller_city','comments' , 'fields' , 'gallery' , 'fieldValue' , 'category'])->find($request->id);
            
            return $post;
        }else{
            $name = $request->name;
            $type = $request->type;
            $category = $request->category_id;
            $city = $request->city_id;
            $token = $request->token;
            $start_price = $request->start_price;
            $end_price = $request->end_price;
    
            $posts = Post::select('*')->where('module_title' , 'products')->whereHas('details' ,function ($query) use ($name){
                $query->where('title' , 'like', '%'.$name.'%');
            });
    
            if ($request->member_id){
    
                $posts->where('member_id' , $request->member_id);
            }
    
            if($type){
                $posts->where('type' , $type);
            }
            
            if($category){
                $posts->where('category_id' , $category);
            }
            
            if($city){
                $posts->where('city_id' , $city);
            }
    
            if ($start_price || $end_price){
                $posts->whereBetween('price' , [$start_price , $end_price]);
            }
    
            if ($request->created_order_type){
                $posts->orderBy('created_time' , $request->created_order_type);
            }
    
            if ($request->order_price_type){
                $posts->orderBy('price' , $request->order_price_type);
            }
    
            if ($request->order_views_type){
                $posts->orderBy('views' , $request->order_views_type);
            }
            
            $posts = $posts->with(['details:post_id,title,content,lang,slug','comments','seller:member_id,name,username','seller_city:city_id,name'])->paginate(10)->items();
    
            return $posts;
        }
    }

    public function getField()
    {
        $fields = FieldOption::with('options')->get();

        return $fields;
    }

    public function postIndex(Request $request)
    {
        // dd($request->file('image'));
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
        
        $rules = [
            'category_id' => 'not_in:0',
            'type' => 'not_in:0',
            'title' => 'required',
            'price' => 'required',
            'desc' => 'required'
        ];

        $messages = [
            'categroy_id.not_in' => 'Please select post category',
            'type.not_in' => 'Please select post type',
            'title.required' => 'Please enter post title',
            'price.required' => 'Please enter post price',
            'desc.required' => 'Please enter post content'
        ];

        $v = validator($request->all() , $rules , $messages);

        if ($v->fails())
        {
            return ['status' => 'error' ,'message' => implode('<br />' ,$v->errors()->all())];
        }

        $post = new Post();

        $post->category_id = $request->category_id;
        $post->type = $request->type;
        $post->youtube = $request->youtube;
        $post->is_auction = $request->is_auction;
        $post->member_id = $user->member_id;
        $post->module_title = 'products';
        $post->city_id = '0';
        $post->created_user_id = '1';
        $post->updated_user_id = '0';
        $post->module_title = 'products';
        
        if(isset($request->image)){
            $photo = $request->image;  // your base64 encoded
            $photo = str_replace('data:image/png;base64,', '', $photo);
            $photo = str_replace('data:image/jpg;base64,', '', $photo);
            $photo = str_replace('data:image/jpeg;base64,', '', $photo);
            $photo = str_replace('data:image/bmp;base64,', '', $photo);
            $photo = str_replace(' ', '+', $photo);
            $imageName = str_random(10).'.'.'png';
            \File::put(storage_path( 'app/posts/').$imageName, base64_decode($photo));
            
            // $file = \File::get(storage_path('app/posts/'."{$imageName}"));
            
            $output[] = [
                'name'     => 'image',
                'contents' => fopen(storage_path('app/posts/'.$imageName), 'r' ),
                'filename' => $imageName
            ];

            $client = new \GuzzleHttp\Client();
            $url = "http://pazar.com.sa/home/elbiheiry";

            $response = $client->request( 'POST', $url, [
                'headers' => [
                    'Content-Type => multipart/form-data'
                ],
                'multipart' => $output
            ] );
            $mydata = response()->json($response->getBody()->getContents());
            $post->image = $mydata->getData();
            @unlink(storage_path( 'app/posts/').$imageName);
        }

        if ($post->save()){
            

            $post->details()->create([
                'title' => $request->title,
                'desc' => $request->desc,
                'slug' => str_slug($request->title),
                'lang' => 'ar'
            ]);
            
            $photos[] = $request->photos;
                
            if(isset($photos)){
                
                foreach($photos as $key => $image)
                {
                    $profileImg= Image::make(base64_decode(preg_replace('#^data:image/\w+;base64,#i', '',$image)))->stream();
                    Storage::put(storage_path( 'app/posts/'), $profileImg, 'public');

                    $image = str_replace('data:image/png;base64,', '', $image);
                    $image = str_replace('data:image/jpg;base64,', '', $image);
                    $image = str_replace('data:image/jpeg;base64,', '', $image);
                    $image = str_replace('data:image/bmp;base64,', '', $image);
                    $image = str_replace(' ', '+', $image);
                    $imageName1 = str_random(10).'.'.'png';
                    \File::put(storage_path( 'app/posts/').$imageName1, base64_decode($image));
                    
                    $output1[] = [
                        'name'     => 'image',
                        'contents' => fopen( storage_path('app/posts/'.$imageName1), 'r' ),
                        'filename' => $imageName1
                    ];

                    $client1 = new \GuzzleHttp\Client();
                    $url1 = "http://pazar.com.sa/home/elbiheiry";

                    $response1 = $client1->request( 'POST', $url1, [
                        'headers' => [
                            'Content-Type => multipart/form-data'
                        ],
                        'multipart' => $output1
                    ] );
                    $mydata1 = response()->json($response1->getBody()->getContents());
                    $post->gallery()->create([
                        'image' => $mydata1->getData(),
                        'title_ar' =>' ',
                        'title_en' =>' '
                    ]);
                    @unlink(storage_path( 'app/posts/').$imageName1);
                    $output1 = [];
                }
            }
            return ['status' => 'success' , 'data' => Post::where('post_id' , $post->post_id)->with(['details:post_id,title,content,lang,slug','gallery','comments','seller:member_id,name,username','seller_city:city_id,name'])->first()];
        }
    }
    
    public function postEdit(Request $request)
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
        

        $rules = [
            'category_id' => 'not_in:0',
            'type' => 'not_in:0',
            'title' => 'required',
            'price' => 'required',
            'desc' => 'required'
        ];

        $messages = [
            'categroy_id.not_in' => 'Please select post category',
            'type.not_in' => 'Please select post type',
            'title.required' => 'Please enter post title',
            'price.required' => 'Please enter post price',
            'desc.required' => 'Please enter post content'
        ];

        $v = validator($request->all() , $rules , $messages);

        if ($v->fails())
        {
            return ['status' => 'error' ,'message' => implode('<br />' ,$v->errors()->all())];
        }

        $post = Post::find($request->post_id);

        $post->category_id = $request->category_id;
        $post->type = $request->type;
        $post->youtube = $request->youtube;
        $post->is_auction = $request->is_auction;
        $post->module_title = 'products';
        $post->city_id = '0';
        $post->created_user_id = '1';
        $post->updated_user_id = '0';
        $post->module_title = 'products';

        if(isset($request->image)){
            $photo = $request->image;  // your base64 encoded
            $photo = str_replace('data:image/png;base64,', '', $photo);
            $photo = str_replace('data:image/jpg;base64,', '', $photo);
            $photo = str_replace('data:image/jpeg;base64,', '', $photo);
            $photo = str_replace('data:image/bmp;base64,', '', $photo);
            $photo = str_replace(' ', '+', $photo);
            $imageName = str_random(10).'.'.'png';
            \File::put(storage_path( 'app/posts/').$imageName, base64_decode($photo));

            // $file = \File::get(storage_path('app/posts/'."{$imageName}"));

            $output[] = [
                'name'     => 'image',
                'contents' => fopen(storage_path('app/posts/'.$imageName), 'r' ),
                'filename' => $imageName
            ];

            $client = new \GuzzleHttp\Client();
            $url = "http://pazar.com.sa/home/elbiheiry";

            $response = $client->request( 'POST', $url, [
                'headers' => [
                    'Content-Type => multipart/form-data'
                ],
                'multipart' => $output
            ] );
            $mydata = response()->json($response->getBody()->getContents());
            $post->image = $mydata->getData();
            @unlink(storage_path( 'app/posts/').$imageName);
        }

        // $request->image->move('http://pazar.com.sa/home/pazarcom/public_html/assets/uploads/images/', $post->image);

        if ($post->save()) {

            echo "post saved";
        }else{
            echo "post not saved";
        }
            

        $post->arabic()->update([
            'title' => $request->title,
            'desc' => $request->desc,
            'slug' => str_slug($request->title)
        ]);

        
       $photos = $request->photos['image'];
                
            if(isset($photos)){
            foreach($photos as $key => $image)
            {
                $image = str_replace('data:image/png;base64,', '', $image);
                $image = str_replace('data:image/jpg;base64,', '', $image);
                $image = str_replace('data:image/jpeg;base64,', '', $image);
                $image = str_replace('data:image/bmp;base64,', '', $image);
                $image = str_replace(' ', '+', $image);
                $imageName1 = str_random(10).'.'.'png';
                \File::put(storage_path( 'app/posts/').$imageName1, base64_decode($image));

                $output1[] = [
                    'name'     => 'image',
                    'contents' => fopen( storage_path('app/posts/'.$imageName1), 'r' ),
                    'filename' => $imageName1
                ];

                $client1 = new \GuzzleHttp\Client();
                $url1 = "http://pazar.com.sa/home/elbiheiry";

                $response1 = $client1->request( 'POST', $url1, [
                    'headers' => [
                        'Content-Type => multipart/form-data'
                    ],
                    'multipart' => $output1
                ] );
                $mydata1 = response()->json($response1->getBody()->getContents());
                $post->gallery()->create([
                    'image' => $mydata1->getData(),
                    'title_ar' =>' ',
                    'title_en' =>' '
                ]);
                @unlink(storage_path( 'app/posts/').$imageName1);
                $output1 = [];

                echo 'images added successfully';
            }
        }
        if (isset($request->deleted_images)){
            foreach ($request->deleted_images as $deleted_image) {

                $client3 = new \GuzzleHttp\Client();
                $url3 = "http://pazar.com.sa/home/delete_elbiheiry/?imageName=".$deleted_image->image;

                $client3->request( 'GET', $url3);

                Gallery::find($deleted_image->id)->delete();
            }
            
            echo 'images deleted successfully';
        }
        return ['status' => 'success' , 'data' => Post::where('post_id' , $post->post_id)->with(['details:post_id,title,content,lang,slug','comments','seller:member_id,name,username','seller_city:city_id,name'])->first()];
    }
    
    
    public function getPost($token)
    {
        $member = Member::where('api_token' , $token)->first();
//        dd($member);

        if(!$member){
            return ['status' => 'error' , 'message' => 'token not valid','code' => '30'];
        }

        $posts = Post::where('member_id' , $member->id)->with(['details','seller','comments','seller_city','fields','gallery','fieldValue','category'])->get();

        return $posts;
    }
    
    public function postWishlist(Request $request ,$token)
    {
        $member = Member::where('api_token' , $token)->first();

        if(!$member){
            return ['status' => 'error' , 'message' => 'token not valid','code' => '30'];
        }

        $member_wishlist = Wishlist::where('member_id' , $member->member_id)->where('post_id' , $request->post_id)->first();
        
        // dd($member_wishlist);

        if ($member_wishlist){
            $member_wishlist->delete();
            return ['status' => 'success' , 'data' => 'Product has been removed from your wishlist'];
        }else{
            $wishlist = new Wishlist();

            $wishlist->post_id = $request->post_id;
            $wishlist->member_id = $member->member_id;
            
            $wishlist->save();
            
            return ['status' => 'success' , 'data' => 'Product has been added to your wishlist'];
        }

    }
    
    public function postMakeOrder(Request $request,$token)
    {
        $member = Member::where('api_token' , $token)->first();

        if(!$member){
            return ['status' => 'error' , 'message' => 'token not valid','code' => '30'];
        }

        $rules = [
            'city_id' => 'required',
            'address' => 'required',
            'quantity' => 'required',
            'total' => 'required'
        ];

        $messages = [
            'city_id.required' => 'Please select shipping city',
            'address.required' => 'Please select shipping address',
            'quantity.required' => 'Please enter product quantity',
            'total.required' => 'Please enter total price'
        ];

        $v = validator($request->all() , $rules , $messages);

        if ($v->fails())
        {
            return ['status' => 'error' ,'message' => implode('<br />' ,$v->errors()->all())];
        }

        $order = new Order();

        $order->post_id = $request->post_id;
        $order->member_id = $member->member_id;
        $order->to_member_id = $request->to_member_id;
        $order->city_id = $request->city_id;
        $order->address = $request->address;
        $order->notes = $request->notes;
        $order->total = $request->total;
        $order->bank_from = $request->bank_from;
        $order->bank_to = $request->bank_to;
        $order->buyer_name = $request->buyer_name;
        $order->buyer_phone = $request->buyer_phone;
        $order->buyer_city = $request->buyer_city;
        $order->seller_name = $request->seller_name;
        $order->seller_phone = $request->seller_phone;
        $order->seller_city = $request->seller_city;
        $order->send_type = $request->send_type;
        $order->delivery_id = $request->delivery_id;
        $order->created_date = Carbon::now();

        if ($order->save()){
            $order->orderpost()->create([
                'post_id' => $request->post_id,
                'quantity' => $request->quantity,
                'price' => $request->price
            ]);

            return ['status' => 'success' ,'message' => 'Order has been done successfully'];
        }

        return ['status' => 'error' ,'message' => 'error please try again later'];
    }
}
