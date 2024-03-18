<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Wishlist extends Model
{
    //
    protected $table = 'wishlist';

    protected $primaryKey = 'wishlist_id';

    public $timestamps = false;
}
