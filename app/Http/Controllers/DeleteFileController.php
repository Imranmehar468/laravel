<?php

namespace App\Http\Controllers;
use App\Models\AllowedFileSize;
use App\Models\UploadFile;
use Illuminate\Http\Request;
use File;
use App\Http\Traits\GlobalFunctions;


class DeleteFileController extends Controller
{
    use GlobalFunctions;


    public function DeleteFiles(Request $request){

        $hash = $request->hash;
        $api_response=array(
            'hash'=>$hash
        );
        $files=[];
        if($hash && $request->file_id && !empty($request->file_id)){
            foreach ($request->file_id as $file_id){
                if(file_exists(public_path("upload_api/$file_id"))){
                    UploadFile::where('uuid_name',$file_id)->where('request_api_id',$hash)->delete();
                    unlink("upload_api/$file_id");
                }
                else{
                    $error_response = array(
                        'code'=>404,
                        'status'=>'file not found',
                        'message'=>'your file id is incorrect or file does not exist'
                    );

                    return response()->json($error_response);
                }

            }
            $upload_file_updated = UploadFile::where('request_api_id',$hash)->get();
            foreach ($upload_file_updated as $updatedfile){
                $sub_json = array(
                    'file_name' => $updatedfile->file_name,
                    'file_id' => $updatedfile->uuid_name,
                    'file_path' => asset("upload_api/$updatedfile->uuid_name"),
                    'file_size'=>GlobalFunctions::UploadFileSize(public_path("upload_api/$updatedfile->uuid_name")),

                );
                array_push($files, $sub_json);
            }
            $api_response['files'] = $files;
            GlobalFunctions::AutoDel();
            return response()->json($api_response);
        }
        else{
            $error_response = array(
                'code'=>404,
                'status'=>'file not found',
                'message'=>'your file id is incorrect or file does not exist'
            );
            return response()->json($error_response);
        }

    }
    public function DeleteMaxfileSize(Request $request){
        $Delfiles = AllowedFileSize::all();
        $tempArr = [];
        if($request->has('file')){
            $files = $request->file;
            foreach ($files as $file){
                AllowedFileSize::where('filetype',$file)->delete();
            }
            foreach (AllowedFileSize::all() as $item){
                $tmparr[$item->filetype]=$item->maxuploadsize;
//
            }
            return $tmparr;
        }else{

            foreach (AllowedFileSize::all() as $item){
                $tmparr[$item->filetype]=$item->maxuploadsize;
//
            }
            return $tmparr;
        }
    }
}
