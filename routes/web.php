<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/


Route::get('/', function () {
//    $str="
//  sternly strictly successfully suddenly supposedly surprisingly suspiciously sweetly swiftly sympathetically tenderly tensely terribly thankfully thoroughly thoughtfully tightly tomorrow too tremendously triumphantly truly truthfully
//    ";
//    $str = trim(strtolower($str));
////    $vala = trim(str_replace('H','h',$str));
//    $vala = explode(' ',$str);
//    $newarr=[];
//    foreach ($vala as $item){
//        array_push($newarr,"'".$item."'");
//    }
//    $vala=implode(',',$newarr);
//    dd($vala);
    return view('welcome');
})->name('home');
Route::get('/optimize-clear', function() {
    $exitCode = Artisan::call('optimize:clear');
    return redirect(route('home'));
});
Route::get('dump-a',function (){
    "<pre>".shell_exec('composer dump -a')."</pre>";
    return redirect(route('home'));
});


