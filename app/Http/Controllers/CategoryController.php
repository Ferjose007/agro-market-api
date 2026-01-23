<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    // Solo necesitamos esto para llenar el select del formulario
    public function index()
    {
        return Category::all();
    }
}