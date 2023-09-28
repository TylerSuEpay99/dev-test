<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Info extends Model
{
    use HasFactory;
    public $table = "info";

    //定義白名單，可讓在資料庫取得的資料
    protected $fillable = [
        'name',
        'info',
    ];

    //需要隱藏的資料 例如密碼等個人資料，暫時用不到
    protected $hidden = [
        
    ];
}
