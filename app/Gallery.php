<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Gallery extends Model
{
    //
    protected $table = 'gallery';
    protected $primaryKey = 'gallery_id';
    public $timestamps = false;
    protected $fillable = ['image' , 'title_ar' , 'title_en'];

}
