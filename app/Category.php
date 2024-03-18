<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    //
    protected $table = 'category';

    protected $primaryKey = 'category_id';

    public function details()
    {
        return $this->hasMany('App\CategoryLang' , 'category_id');
    }

    public function translated($lang)
    {
        return $this->details()->where('lang' , $lang)->first();
    }
}
