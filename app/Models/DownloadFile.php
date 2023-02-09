<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DownloadFile extends Model
{
    use HasFactory;
    protected $fillable = ['request_api','uuid_name','download_path','zip_path','download_name'];
//    protected $guarded = ['download_path','zip_path'];
    public function myrequestapi(){
        return $this->belongsTo(RequestApi::class);
    }
}
