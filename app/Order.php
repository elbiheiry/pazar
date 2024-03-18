<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    //
    protected $table = 'order';

    protected $primaryKey = 'order_id';

    public $timestamps = false;

    public function orderpost()
    {
        return $this->hasOne('App\OrderPost' , 'order_id');
    }
}
