<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Carbon\Carbon;

abstract class Controller
{
    public function slug($string){
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $string))).'-'.rand(1,999);
        return $slug;
    }

    public function checkExistPost($table,$fieldName,$fieldValue){
        $exist = Category::where($fieldName,$fieldValue)->count('id');
        return $exist;
    }


    public function cambodiaTime(): Carbon
    {
        return Carbon::now('Asia/Phnom_Penh');
    }

    public function uploadFile($File) {
        $fileName  = rand(1,999).'-'.$File->getClientOriginalName();
        $path      = 'uploads';
        $File->move(public_path($path), $fileName);
        return $fileName;
    }
}
