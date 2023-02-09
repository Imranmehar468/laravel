<?php

namespace App\Http\Controllers;

use App\Models\Blog;
use App\Models\BlogDetails;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
    public function Blog($website){
        $website = $website;
//        dd($website);
        $blogs = Blog::where('website', $website)
            ->where('is_draft', 0)
            ->orderBy('id', 'DESC')
            ->paginate(12);

        return $blogs;
    }
    public function BlogDetails($website,$id){

        $blog_url = $id;
        $website = $website;
        if($blog_url == 'blog')
        {
            return redirect(route('blog'));
        }else{

            $blogs = Blog::where([['blog_url', $blog_url], ['website', $website]])->first();
            if(!empty($blogs) && $blogs != null) {
                $recent_blog = Blog::where('website', $website)->where('is_draft', 0)->orderBy('id', 'DESC')->offset(0)->limit(10)->get();
//                return view('blogs.blogdetail', compact('blogs'))->with('recent_blog', $recent_blog);
                return [$blogs,$recent_blog];
            }else{
                return redirect(route('blog'));
            }
        }
    }
}
