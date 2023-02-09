<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BlogController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
Route::post('max',[\App\Http\Controllers\AllowedFileSizeController::class,'MaxUploadFileSize']);
Route::post('del',[\App\Http\Controllers\DeleteFileController::class,'DeleteMaxfileSize']);
Route::post('delf',[\App\Http\Controllers\DeleteFileController::class,'DeleteFiles']);
Route::post('file/upload',[\App\Http\Controllers\UploadFileController::class,'UploadFiles']);
Route::post('url/upload',[\App\Http\Controllers\UploadFileController::class,'UploadURLs']);
Route::post('file/action/d',[\App\Http\Controllers\ActionController::class,'Converter']);

Route::post('upload/file',[\App\Http\Controllers\ConvertToolController::class,'uploadFile'])->name('api.upload.post');
Route::post('file/delete',[\App\Http\Controllers\ConvertToolController::class,'crud'])->name('api.crud');
Route::post('file/action',[\App\Http\Controllers\ConvertToolController::class,'WordToPdf'])->name('api.excel');
Route::post('process/check',[\App\Http\Controllers\ConvertToolController::class,'CheckProcess'])->name('process.check');
Route::post('merge',[\App\Http\Controllers\ConvertToolController::class,'merge']);
Route::get('file/limit',[\App\Http\Controllers\ConvertToolController::class,'filelimit']);
Route::get('/auto_del',[\App\Http\Controllers\ConvertToolController::class,'AutoDel'])->name('auto.del');
/*
 --------------------------------------------------------------
 */
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('blogs/{website}',[\App\Http\Controllers\BlogController::class,'Blog'])->name('blog');
Route::get('recent-blogs/{website}',[\App\Http\Controllers\BlogController::class,'RecentBlogs'])->name('blog.recent');
Route::get('blog/{website}/{slug}',[\App\Http\Controllers\BlogController::class,'BlogDetails'])->name('blog.details');
Route::get('blog/images/{filename}',function ($filename){
    return response(file_get_contents('https://www.dzinemedia.com/assets/blog_images/'.$filename))->header('Content-Type','image/*');
})->name('image.get');
Route::get('sitemap/{website}',[\App\Http\Controllers\BlogController::class,'sitemap'])->name('sitmap');
Route::post('/upload',[\App\Http\Controllers\UploadFileController::class,'UploadFiles']);
Route::post('/delete',[\App\Http\Controllers\DeleteFileController::class,'DeleteFiles']);
