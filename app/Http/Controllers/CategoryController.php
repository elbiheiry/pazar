<?php

namespace App\Http\Controllers;

use App\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    //
    public function getIndex()
    {
        $categories = Category::with('details')->get();

        return $categories;
    }
}
