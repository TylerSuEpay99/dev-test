<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Info; //使用Info Model

class InfoController extends Controller
{
    //
    public function index (){

        // 定義$info 為 Info::all()
        // $info = Info::all(); //這是ORM最基本的使用方法 從Info中取得所有資料
        /* [Model]::[要取甚麼] */
        $info = Info::where('name','ban')->get(); //取得id 為 1 的資料
        // dd($info); //先使用dd 來看看取到甚麼資料吧
        return view('info', compact('info'));
        // return view('info', ['info' => $info]);
    }
}
