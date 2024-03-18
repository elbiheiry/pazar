<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class OrderPost extends Model
{
    //
    protected $table = 'order_post';

    protected $primaryKey = 'order_post_id';

    protected $fillable = ['post_id' ,'quantity' ,'price'];

    public $timestamps = false;
}
