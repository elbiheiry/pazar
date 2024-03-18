<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    //
    protected $table = 'comment';
    protected $primaryKey = 'comment_id';
    public $timestamps = false;
    
    public function member(){
        return $this->belongsTo('App\Member' ,'member_id');
    }
}
