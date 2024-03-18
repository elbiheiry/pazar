<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    //
    protected $table = 'post';
    protected $primaryKey = 'post_id';
    public $timestamps = false;

    public function details()
    {
        return $this->hasMany('App\PostLang' , 'post_id');
    }

    public function category()
    {
        return $this->belongsTo('App\Category' , 'category_id');
    }

    public function fields()
    {
        return $this->hasMany('App\PostFieldValue' , 'post_field_value_id');
    }

    public function gallery()
    {
        return $this->hasMany('App\Gallery' ,'post_id');
    }

    public function fieldValue()
    {
        return $this->hasMany('App\PostFieldValue' , 'post_id');
    }
    
    
    public function comments()
    {
        return $this->hasMany('App\Comment' , 'post_id');
    }
    
    public function seller(){
        return $this->belongsTo('App\Member' ,'member_id');
    }
    
    public function seller_city(){
        return $this->belongsTo('App\City' ,'city_id');
    }
}
