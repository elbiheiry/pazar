<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Blacklist extends Model
{
    //
    protected $table = 'black_menu';

    protected $primaryKey = 'black_number_id';
}
