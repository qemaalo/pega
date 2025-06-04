<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TestController extends Controller
{
    public function index(request $request){
        return view('test.index');
    }

    public function tabla1(request $request){
        return view('test.tabla');
    }
    
    public function tabla2(request $request){
        return view('test.tabla2');
    }

    public function tabla3(request $request){
        return view ('test.tabla3');
    }

}
