<?php

namespace App\Http\Controllers;

use App\Models\Category; // <- indispensable !
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        return Category::all();
    }
}