<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BlogDetails extends Model
{
    use HasFactory;
    protected $connection = 'mysql2';
    protected $table = 'seo_blogs_detail';

    public function blog()
    {
        return $this->belongsTo(Blog::class);
    }
}
