<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PostLang extends Model
{
    //
    protected $table = 'post_lang';

    protected $primaryKey = 'post_lang_id';
    public $timestamps = false;

    protected $fillable = ['title','lang' ,'desc','content','meta_keys','meta_desc' ,'slug'];
}
