<?php

namespace App\Http\Controllers;

use App\Models\AllowedFileSize;
use Illuminate\Http\Request;

class AllowedFileSizeController extends Controller
{
    public function MaxUploadFileSize(Request $request){
        $allowedsizearr = AllowedFileSize::all();
        $tempArr=[];
        if($request->has('file') && $request->has('maxsize')){

            $files = $request->file;

            $sizes = $request->maxsize;
            foreach ($files as $key=>$file){
                if($allowedsizearr->contains('filetype',$file)){
                    $updateSize = AllowedFileSize::where('filetype',$file)->update(['maxuploadsize'=>(int)($sizes[$key])*1024*1024]);
                }else{
                    $fileSize =new AllowedFileSize;
                    $fileSize->filetype = $file;
                    $fileSize->maxuploadsize = (int)($sizes[$key])*1024*1024;
                    $fileSize->save();
                }

            }

            foreach (AllowedFileSize::all() as $filefs){
                $tempArr[$filefs->filetype] = $filefs->maxuploadsize;
            }
            return $tempArr;
        }
        elseif($request->has('file')){
            $files = $request->file;
            foreach ($files as $file){
                if($allowedsizearr->contains('filetype',$file)){

                }else{

                    $fileSize = new AllowedFileSize;
                    $fileSize->filetype = $file;
                    $fileSize->save();
                }
                foreach (AllowedFileSize::all() as $filefs){
                    $tempArr[$filefs->filetype] = $filefs->maxuploadsize;
                }

            }
            return $tempArr;
        }
        else{
                $sizeArr = AllowedFileSize::all();
                foreach ($sizeArr as $item){
                    $tmparr[$item->filetype]=$item->maxuploadsize;
//
                }
                return $tmparr;
        }

    }

}
