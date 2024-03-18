<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class FieldOption extends Model
{
    //
    protected $table = 'field_option';

    protected $primaryKey = 'field_option_id';

    public function options()
    {
        return $this->hasMany('App\PostFieldValue', 'field_option_id');
    }
}
