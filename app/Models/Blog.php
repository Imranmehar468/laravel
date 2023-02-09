<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Blog extends Model
{
    use HasFactory;
    protected $connection = 'mysql2';
    protected $table = 'seo_blogs';
    public function blog_detail()
    {
        return $this->hasOne(BlogDetails::class, 'blog_id');
    }
}
