<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RequestApi extends Model
{
    use HasFactory;
    protected $fillable = ['hash_name'];
    public function uploadfile(){
        return $this->hasMany(UploadFile::class);
    }
    public function download(){
        return $this->hasMany(DownloadFile::class);
    }
}
