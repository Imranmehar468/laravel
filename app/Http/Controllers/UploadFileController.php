<?php

namespace App\Http\Controllers;

use App\Http\Traits\GlobalFunctions;
use App\Models\ConvertTool;
use App\Models\DownloadFile;
use App\Models\RequestApi;
use App\Models\UploadFile;
use App\Http\Requests\StoreConvertToolRequest;
use App\Http\Requests\UpdateConvertToolRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use mysql_xdevapi\Exception;
use Pion\Laravel\ChunkUpload\Exceptions\UploadMissingFileException;
use Pion\Laravel\ChunkUpload\Handler\HandlerFactory;
use Pion\Laravel\ChunkUpload\Receiver\FileReceiver;
use ZipArchive;
use File;
use Carbon\Carbon;


class UploadFileController extends Controller
{
    use GlobalFunctions;
    public function UploadFiles(Request $request){
        $apikey = $request->header('x-api-key')??'';
        if($apikey == env('API_KEY')){

            // $check = self::MaxLimit('pptx');
            $hash_api = $this->hash($request->hash);
            $request_api = $this->requestApi($hash_api);
            $api_response = array(
                'hash'=>$hash_api
            );

            $files =[];
            $allowedFiles = $this->allowedfiles();

            $wipefiles = UploadFile::where('updated_at','<',Carbon::now()->subDays(1))->get();
            foreach ($wipefiles as $wipefile){

                $wipefile->delete();
            }
            $downFiles = DownloadFile::where('updated_at','<',Carbon::now()->subDays(1))->get();
            foreach ($downFiles as $wipe){

                $wipe->delete();
            }
            self::AutoDel();
            if($request->file){
                foreach ($request->file as $item=>$file){
                    if(in_array(strtolower($file->getClientOriginalExtension()),$allowedFiles)){
                        $split_range = $request->split_range[$item]??'';
                        $split_range = str_replace(',',' ',$split_range);

                        $receiver = new FileReceiver($file,$request,HandlerFactory::classFromRequest($request));
                        if ($receiver->isUploaded() === false) {
                            throw new UploadMissingFileException();
                        }
                        $save = $receiver->receive();
                        if ($save->isFinished()) {
                            $hash = Str::uuid()->toString();
                            $extension = $save->getClientOriginalExtension();
                            $origional_names = $save->getClientOriginalName();
                            
                            $origional_name=GlobalFunctions::UploadFilesName($hash_api,$origional_names,$extension);

                            $hash = "{$hash}.{$extension}";
                            $path = $save->move('upload_api', $hash);
                            if(file_exists($path)){
                                $size = GlobalFunctions::UploadFileSize($path);
                                $upload_file = new UploadFile;
                                $upload_file->uuid_name = $hash;
                                $upload_file->request_api_id = $request_api->hash_name;
                                $upload_file->file_name = $origional_name;
                                $upload_file->split_range = $split_range;
                                $upload_file->size = $size;
                                $upload_file->upload_path = asset("upload_api/$hash");
                                $upload_file->save();
                            }

                        }
                        else{
                            $error_response = array(
                                'code'=>422,
                                'status'=>'Unprocessable Entity',
                                'message'=>'Invalid File type'

                            );
                            return response()->json($error_response);
                        }
                    }

                }
                $upload_file_updated = UploadFile::where('request_api_id',$request_api->hash_name)->get();
                foreach ($upload_file_updated as $updatedfile){
                    $sub_json = array(
                        'file_name' => $updatedfile->file_name,
                        'file_id' => $updatedfile->uuid_name,
                        'file_size' => $updatedfile->size,
                        'file_path' => asset("upload_api/$updatedfile->uuid_name")
                    );
                    array_push($files, $sub_json);
                }
                $api_response['files']=$files;
                return response()->json($api_response);
            }
            else{
                return response()->json(['code'=>404,
                    'status'=>'File not Found.',
                    'message'=>'Atleast one file is required for this action']);
            }
        }
        else{
            return response()->json(['code'=>401,
                'status'=>'Authorization required',
                'message'=>'Lacks valid authentication for the target resources']);
        }



    }
    public function UploadURLs(Request $request){
        $apikey = $request->header('x-api-key')??'';
        if($apikey == env('API_KEY')){
            $hash_api = $this->hash($request->hash);
            $request_api = $this->requestApi($hash_api);
            $api_response = array(
                'hash'=>$hash_api
            );
            $files =[];


            $wipefiles = UploadFile::where('updated_at','<',Carbon::now()->subDays(1))->get();
            foreach ($wipefiles as $wipefile){

                $wipefile->delete();
            }
            $downFiles = DownloadFile::where('updated_at','<',Carbon::now()->subDays(1))->get();
            foreach ($downFiles as $wipe){

                $wipe->delete();
            }
            self::AutoDel();
            foreach ($request->link as $file){

                $size = 0;
                $upload_file = new UploadFile;
                $upload_file->uuid_name = Str::uuid()->toString();
                $upload_file->request_api_id = $request_api->hash_name;
                $upload_file->file_name = $file;
                $upload_file->size = $size;
                $upload_file->upload_path =$file;
                $upload_file->save();
            }


            $upload_file_updated = UploadFile::where('request_api_id',$request_api->hash_name)->get();
            foreach ($upload_file_updated as $updatedfile){
                $sub_json = array(
                    'file_name' => $updatedfile->file_name,
                    'file_id' => $updatedfile->uuid_name,
                    'file_size' => $updatedfile->size,
                    'file_path' => $updatedfile->uuid_name
                );
                array_push($files, $sub_json);
            }
            $api_response['files']=$files;
            return response()->json($api_response);
        }
        else{
            return response()->json(['code'=>401,
                'status'=>'Authorization required',
                'message'=>'Lacks valid authentication for the target resources']);
        }


    }
    public function hash($hash){
        if($hash){
            $hash=$hash;
            return $hash;
        }else{
            $hash = Str::uuid()->toString();
            return $hash;
        }

    }
    public function requestApi($hash){
        $request_api = RequestApi::where('hash_name','=',$hash)->first();
        if($request_api){
            return $request_api;
        }else{
            $request_api =new RequestApi;
            $request_api->hash_name =$hash;
            $request_api->save();
            return $request_api;
        }
    }
    public function allowedfiles(){
        $files = array(
            'pdf','html','xps',
            'doc','docx','rtf','txt',
            'xls','xlsx','csv',
            'ppt','pptx',
            'png','jpg','jpeg',
            'tiff','gif','webp',
            'svg','tif'
        );
        return $files;
    }

}
