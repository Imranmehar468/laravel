<?php

namespace App\Http\Controllers;
use App\Models\Blog;
use App\Models\BlogDetails;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;

class BlogController extends Controller
{

    public function Blog($website){
        $website = $website;//'';//strtolower(env('WEBSITE'));
//        dd($website);
        $blogs = Blog::where('website', $website)
            ->where('is_draft', 0)
            ->orderBy('id', 'DESC')
            ->paginate(12);

        return response()->json($blogs);
    }
    public function BlogDetails($website,$id){

        $blog_url = $id;
        $website = $website;
        if($blog_url == 'blog')
        {
            return redirect(route('blog'));
        }else{

            $blogs = Blog::where([['blog_url', $blog_url], ['website', $website]])->first();
            $blog_detail=$blogs->blog_detail->blog_detail;
            if(!empty($blogs) && $blogs != null) {
                $recent_blog = Blog::where('website', $website)->where('is_draft', 0)->orderBy('id', 'DESC')->offset(0)->limit(10)->get();
//                return view('blogs.blogdetail', compact('blogs'))->with('recent_blog', $recent_blog);
                return response()->json([$blogs,$recent_blog,$blog_detail]);
            }else{
                return redirect(route('blog'));
            }
        }
    }
    public function RecentBlogs($website){
        $recent_blog= Blog::where('website', $website)->where('is_draft', 0)->orderBy('id', 'DESC')->offset(0)->limit(10)->get();
        return response()->json([$recent_blog]);
    }
    public function sitemap($website){
        $blog_query = blog::where('website', $website)->where('is_draft', 0)->orderBy('id', 'DESC')->get();
        return response($blog_query);
    }
}
