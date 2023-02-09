<?php

namespace App\Http\Controllers;

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


class ConvertToolController extends Controller
{


    public function uploadFile(Request $request){

        if($request->hash){

            $hash_api = $request->hash;
            $request_api = RequestApi::where('hash_name','=',$hash_api)->first();
            if(!$request_api){
                $error_response = array(
                    'code'=> 404,
                    'status'=>'record not found',
                    'message'=>'hash value not exist'
                );
                return response()->json($error_response);

            }

        }
        else {
            $hash_api = Str::uuid()->toString();
            $request_api =new RequestApi;
            $request_api->hash_name =$hash_api;
            $request_api->save();
        }
        $api_response=array(
            'hash'=>$hash_api
        );

        $files=[];

        foreach ($request->file as $file){
            if(strtolower($file->getClientOriginalExtension())== 'tiff' || strtolower($file->getClientOriginalExtension())== 'svg' || strtolower($file->getClientOriginalExtension())== 'jpg' || strtolower($file->getClientOriginalExtension())== 'jpeg' || strtolower($file->getClientOriginalExtension())== 'png' || strtolower($file->getClientOriginalExtension())== 'xls' || strtolower($file->getClientOriginalExtension())== 'xlsx' || strtolower($file->getClientOriginalExtension())== 'csv' || strtolower($file->getClientOriginalExtension())== 'doc' || strtolower($file->getClientOriginalExtension())== 'docx' || strtolower($file->getClientOriginalExtension())== 'ppt' || strtolower($file->getClientOriginalExtension())== 'pptx' || strtolower($file->getClientOriginalExtension())== 'pdf')
            {
                $receiver = new FileReceiver($file,$request,HandlerFactory::classFromRequest($request));
                if ($receiver->isUploaded() === false) {
                    throw new UploadMissingFileException();
                }
                $save = $receiver->receive();
                if ($save->isFinished()) {
                    $hash = Str::uuid()->toString();
                    $extension = $save->getClientOriginalExtension();
                    $origional_name = $save->getClientOriginalName();
                    $downloadfilename = "{$hash}.pdf";
                    $downloadfile = public_path('download_api/') . "{$hash}.pdf";
                    $downloadpath = public_path('download_api/');

                    $hash = "{$hash}.{$extension}";
                    $path = $save->move('upload_api', $hash);

                    if(file_exists($path)) {
                        $size = $this->UploadFileSize($path);
                        $upload_file = UploadFile::where('request_api_id','97d3c989-54fc-46cd-a07f-f8446a025614')->count('request_api_id');
//                        dd($upload_file);
                        $upload_file = new UploadFile;
                        $upload_file->uuid_name = $hash;
                        $upload_file->request_api_id = $request_api->hash_name;
                        $upload_file->file_name = $origional_name;
                        $upload_file->size = $size;
                        $upload_file->upload_path = asset("upload_api/$hash");
                        $upload_file->save();

                    }

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
        $upload_file_updated = UploadFile::where('request_api_id',$request_api->hash_name)->get();
        foreach ($upload_file_updated as $updatedfile){
            $sub_json = array(
                'file_name' => $updatedfile->file_name,
                'file_id' => $updatedfile->uuid_name,
                'file_size' => $updatedfile->size,
//                'file_size' => $size,
                'file_path' => asset("upload_api/$updatedfile->uuid_name")
            );
            array_push($files, $sub_json);
        }
        $api_response['files']=$files;

        return response()->json($api_response);

    }
    //*********************CRUD Upload Files************//
    public function crud(Request $request){
        $hash = $request->hash;
        $api_response=array(
            'hash'=>$hash
        );
        $files=[];

//        dd($request->file_id);
        if($hash && $request->file_id && !empty($request->file_id)) {
            foreach ($request->file_id as $file_id) {

                if (file_exists(public_path("upload_api/$file_id"))) {

                    $uploadfile = UploadFile::where('uuid_name', $file_id)->where('request_api_id', $hash)->delete();
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
            $upload_file_updated = UploadFile::where('request_api_id', $hash)->get();
            $can_upload = [];
            foreach ($upload_file_updated as $updatedfile) {
                array_push($can_upload,$this->UploadFileSize("upload_api/$updatedfile->uuid_name"));
                $tot_siz = 50*1024*1024 - (int)array_sum($can_upload);
//                dd($tot_siz);
                $sub_json = array(
                    'file_name' => $updatedfile->file_name,
                    'file_id' => $updatedfile->uuid_name,
                    'file_path' => asset("upload_api/$updatedfile->uuid_name"),
                    'file_size'=>$this->UploadFileSize("upload_api/$updatedfile->uuid_name")
                );
                array_push($files, $sub_json);
            }
            $api_response['files'] = $files;
            $this->AutoDel();
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


//*********************excel to pdf************//
//export HOME=".public_path('upload_api')." &&

//*********************word to pdf************//
    public function WordToPdf(Request $request){

        $hash=$request->hash;
        $action =$request->action;
        $api_response['hash']=$hash;
        $zip_path =$hash.'.zip';
        $files= UploadFile::where('request_api_id',$hash)->get();
        $total_admin_allowed_size = $this->filelimit();

        $total_file_size_arar=[];
        foreach ($files as $kfile){
            array_push($total_file_size_arar,$this->UploadFileSize(str_replace('mergepdf_code/public','public_html',public_path("upload_api/$kfile->uuid_name"))));
        }
        $arr_sum = array_sum($total_file_size_arar);
//        dd($total_admin_allowed_size);
//        dd(!count($files)==0);
        //Excel to pdf
//        if($hash && $action=='etp' && !count($files)==0 && $arr_sum > 0 && $arr_sum <= $total_admin_allowed_size['xlx'])
        if($hash && $action=='etp' && !count($files)==0)
        {
            $pdf_array = [];
            foreach ($files as $file){
//                dd($file->uuid_name);

                $file_name = $file->uuid_name;
                $pdf_id = $file->uuid_name;
                $parts = explode('.',$pdf_id);
                $parts[count($parts)-1]='pdf';
                $pdf_id=implode('.',$parts);
                $str =" soffice --headless --convert-to pdf:writer_pdf_Export ".str_replace('mergepdf_code/public','public_html',public_path("upload_api/$file_name"))." --outdir ".str_replace('mergepdf_code/public','public_html',public_path('download_api/'));
//                dd($str);
                shell_exec($str);

                if(file_exists(str_replace('mergepdf_code/public','public_html',public_path("download_api/$pdf_id")))){
//                    dd(asset("download_api/$pdf_id"));

                    $downloadfiles =DownloadFile::where('request_api',$hash)->get();
//                    dd(count($downloadfiles));

                    if(count($downloadfiles) == count($files)){

                        foreach ($downloadfiles as $downloadfile){
                            $downloadfile->update(['zip_path'=>asset("download_api/$zip_path")]);
                        }

                    }else{

                        $downloadfiles = DownloadFile::updateOrCreate(
                            ['uuid_name'=>$pdf_id],
                            ['uuid_name'=>$pdf_id,'request_api'=>$hash,'download_path'=>asset("download_api/$pdf_id"),'zip_path'=>asset("download_api/$zip_path")]);
//                    dd('wow');
                    }

                    $sub_arr = array(
                        'file_id'=>$pdf_id,
                        'download_path'=>asset("download_api/$pdf_id"),
                        'zip_path'=>asset("download_api/$zip_path"
                        )
                    );
                    array_push($pdf_array,$sub_arr);

                }else{
                    $error_response = array(
                        'code'=>500,
                        'status'=>'Files not found',
                        'message'=>'file not existpop'
                    );
                    return response()->json($error_response);
                }

            }

            $zip = new ZipArchive;
            if($zip->open(str_replace('mergepdf_code/public','public_html',public_path("download_api/$zip_path")),ZipArchive::CREATE)===TRUE){
                foreach ($pdf_array as $zip_pdf){

                    $file_id = str_replace('mergepdf_code/public','public_html',public_path("download_api/").$zip_pdf['file_id']);
                    $relativeNameZipFile = basename($file_id);
//                    dd($relativeNameZipFile,$file_id);
                    $zip->addFile($file_id,$relativeNameZipFile);
                }
                $zip->close();
            }
            $api_response['files']=$pdf_array;
            return response()->json($api_response);

        }
        //Doc to pdf
        elseif ($hash && $action=='dtp' && !count($files)==0){

            $pdf_array = [];
            foreach ($files as $file){
                $file_name = $file->uuid_name;
                $pdf_id = $file->uuid_name;
                $parts = explode('.',$pdf_id);
                $parts[count($parts)-1]='pdf';
                $pdf_id=implode('.',$parts);
//                $temp = exec(" soffice --cat ".str_replace('mergepdf_code/public','public_html',public_path("upload_api/$file_name")));
//                dd($temp);
                $str =" soffice --headless --convert-to pdf:writer_pdf_Export ".str_replace('mergepdf_code/public','public_html',public_path("upload_api/$file_name"))." --outdir ".str_replace('mergepdf_code/public','public_html',public_path('download_api/'));
//dd($str);
                shell_exec($str);
//dd('done');
                if(file_exists(str_replace('mergepdf_code/public','public_html',public_path("download_api/$pdf_id")))){
//                    dd(asset("download_api/$pdf_id"));

                    $downloadfiles =DownloadFile::where('request_api',$hash)->get();
//                    dd(count($downloadfiles));

                    if(count($downloadfiles) == count($files)){

                        foreach ($downloadfiles as $downloadfile){
                            $downloadfile->update(['zip_path'=>asset("download_api/$zip_path")]);
                        }

                    }else{

                        $downloadfiles = DownloadFile::updateOrCreate(
                            ['uuid_name'=>$pdf_id],
                            ['uuid_name'=>$pdf_id,'request_api'=>$hash,'download_path'=>asset("download_api/$pdf_id"),'zip_path'=>asset("download_api/$zip_path")]);
//                    dd('wow');
                    }

                    $sub_arr = array(
                        'file_id'=>$pdf_id,
                        'download_path'=>asset("download_api/$pdf_id"),
                        'zip_path'=>asset("download_api/$zip_path"
                        )
                    );
                    array_push($pdf_array,$sub_arr);

                }else{
                    $error_response = array(
                        'code'=>500,
                        'status'=>'Files not found',
                        'message'=>'file not exist'
                    );
                    return response()->json($error_response);
                }

            }
            $zip = new ZipArchive;
            if($zip->open(str_replace('mergepdf_code/public','public_html',public_path("download_api/$zip_path")),ZipArchive::CREATE)===TRUE){
                foreach ($pdf_array as $zip_pdf){

                    $file_id = str_replace('mergepdf_code/public','public_html',public_path("download_api/").$zip_pdf['file_id']);
                    $relativeNameZipFile = basename($file_id);
//                    dd($relativeNameZipFile,$file_id);
                    $zip->addFile($file_id,$relativeNameZipFile);
                }
                $zip->close();
            }
            $api_response['files']=$pdf_array;
            return response()->json($api_response);
        }
        //ppt to pdf
        elseif ($hash && $action=='pptp' && !count($files)==0){
            $pdf_array = [];
            foreach ($files as $file){
                $file_name = $file->uuid_name;
                $pdf_id = $file->uuid_name;
                $parts = explode('.',$pdf_id);
                $parts[count($parts)-1]='pdf';
                $pdf_id=implode('.',$parts);
                $str =" soffice --headless --convert-to pdf:writer_pdf_Export ".str_replace('mergepdf_code/public','public_html',public_path("upload_api/$file_name"))." --outdir ".str_replace('mergepdf_code/public','public_html',public_path('download_api/'));
                shell_exec($str);

                if(file_exists(str_replace('mergepdf_code/public','public_html',public_path("download_api/$pdf_id")))){
//                    dd(asset("download_api/$pdf_id"));

                    $downloadfiles =DownloadFile::where('request_api',$hash)->get();
//                    dd(count($downloadfiles));

                    if(count($downloadfiles) == count($files)){

                        foreach ($downloadfiles as $downloadfile){
                            $downloadfile->update(['zip_path'=>asset("download_api/$zip_path")]);
                        }

                    }else{

                        $downloadfiles = DownloadFile::updateOrCreate(
                            ['uuid_name'=>$pdf_id],
                            ['uuid_name'=>$pdf_id,'request_api'=>$hash,'download_path'=>asset("download_api/$pdf_id"),'zip_path'=>asset("download_api/$zip_path")]);
//                    dd('wow');
                    }

                    $sub_arr = array(
                        'file_id'=>$pdf_id,
                        'download_path'=>asset("download_api/$pdf_id"),
                        'zip_path'=>asset("download_api/$zip_path"
                        )
                    );
                    array_push($pdf_array,$sub_arr);

                }else{
                    $error_response = array(
                        'code'=>500,
                        'status'=>'Files not found',
                        'message'=>'file not existpop'
                    );
                    return response()->json($error_response);
                }

            }
            $zip = new ZipArchive;
            if($zip->open(str_replace('mergepdf_code/public','public_html',public_path("download_api/$zip_path")),ZipArchive::CREATE)===TRUE){
                foreach ($pdf_array as $zip_pdf){

                    $file_id = str_replace('mergepdf_code/public','public_html',public_path("download_api/").$zip_pdf['file_id']);
                    $relativeNameZipFile = basename($file_id);
//                    dd($relativeNameZipFile,$file_id);
                    $zip->addFile($file_id,$relativeNameZipFile);
                }
                $zip->close();
            }
            $api_response['files']=$pdf_array;
            return response()->json($api_response);
        }
        //pdf to doc
        elseif ($hash && $action=='ptd' && !count($files)==0){
            $pdf_array = [];
            foreach ($files as $file){
                $file_name = $file->uuid_name;
                $pdf_id = $file->uuid_name;
                $parts = explode('.',$pdf_id);
                $parts[count($parts)-1]='docx';
                $pdf_id=implode('.',$parts);
                $str =" soffice --headless --infilter='writer_pdf_import' --convert-to docx ".str_replace('mergepdf_code/public','public_html',public_path("upload_api/$file_name"))." --outdir ".str_replace('mergepdf_code/public','public_html',public_path('download_api/'));
//                dd($str);
                shell_exec($str);

                if(file_exists(str_replace('mergepdf_code/public','public_html',public_path("download_api/$pdf_id")))){
//                    dd(asset("download_api/$pdf_id"));

                    $downloadfiles =DownloadFile::where('request_api',$hash)->get();
//                    dd(count($downloadfiles));

                    if(count($downloadfiles) == count($files)){

                        foreach ($downloadfiles as $downloadfile){
                            $downloadfile->update(['zip_path'=>asset("download_api/$zip_path")]);
                        }

                    }else{

                        $downloadfiles = DownloadFile::updateOrCreate(
                            ['uuid_name'=>$pdf_id],
                            ['uuid_name'=>$pdf_id,'request_api'=>$hash,'download_path'=>asset("download_api/$pdf_id"),'zip_path'=>asset("download_api/$zip_path")]);
//                    dd('wow');
                    }

                    $sub_arr = array(
                        'file_id'=>$pdf_id,
                        'download_path'=>asset("download_api/$pdf_id"),
                        'zip_path'=>asset("download_api/$zip_path"
                        )
                    );
                    array_push($pdf_array,$sub_arr);

                }else{
                    $error_response = array(
                        'code'=>500,
                        'status'=>'Files not found',
                        'message'=>'file not existpop'
                    );
                    return response()->json($error_response);
                }

            }
            $zip = new ZipArchive;
            if($zip->open(str_replace('mergepdf_code/public','public_html',public_path("download_api/$zip_path")),ZipArchive::CREATE)===TRUE){
                foreach ($pdf_array as $zip_pdf){

                    $file_id = str_replace('mergepdf_code/public','public_html',public_path("download_api/").$zip_pdf['file_id']);
                    $relativeNameZipFile = basename($file_id);
//                    dd($relativeNameZipFile,$file_id);
                    $zip->addFile($file_id,$relativeNameZipFile);
                }
                $zip->close();
            }
            $api_response['files']=$pdf_array;
            return response()->json($api_response);
        }
        elseif($hash && $action=='itp' && !count($files)==0){
            $pdf_array = [];
            $imgstr=[];
            $pdf_id = $hash.'.pdf';
            foreach ($files as $file){
                array_push($imgstr,str_replace('mergepdf_code/public','public_html',public_path("upload_api/$file->uuid_name")));
            }
            $imgarray = implode(' ',$imgstr);
            $str = " convert $imgarray ".str_replace('mergepdf_code/public','public_html',public_path("download_api/$pdf_id"));
            shell_exec($str);

            if(file_exists(str_replace('mergepdf_code/public','public_html',public_path("download_api/$pdf_id")))){
//                    dd(asset("download_api/$pdf_id"));

//                $downloadfiles =DownloadFile::where('request_api',$hash)->get();




                    $downloadfiles = DownloadFile::updateOrCreate(
                        ['uuid_name'=>$pdf_id],
                        ['uuid_name'=>$pdf_id,'request_api'=>$hash,
                            'download_path'=>asset("download_api/$pdf_id"),
                            'zip_path'=>asset("download_api/$zip_path")]);
//                    dd('wow');


                $sub_arr = array(
                    'file_id'=>$pdf_id,
                    'download_path'=>asset("download_api/$pdf_id"),
                    'zip_path'=>asset("download_api/$zip_path"
                    )
                );
                array_push($pdf_array,$sub_arr);

            }
            else{
                $error_response = array(
                    'code'=>500,
                    'status'=>'Files not found',
                    'message'=>'file not existpop'
                );
                return response()->json($error_response);
            }
            $zip = new ZipArchive;
            if($zip->open(str_replace('mergepdf_code/public','public_html',public_path("download_api/$zip_path")),ZipArchive::CREATE)===TRUE){
                foreach ($pdf_array as $zip_pdf){

                    $file_id = str_replace('mergepdf_code/public','public_html',public_path("download_api/").$zip_pdf['file_id']);
                    $relativeNameZipFile = basename($file_id);
//                    dd($relativeNameZipFile,$file_id);
                    $zip->addFile($file_id,$relativeNameZipFile);
                }
                $zip->close();
            }
            $api_response['files']=$pdf_array;
            return response()->json($api_response);
        }
        elseif ($hash && $action=='merge' && !count($files)==0){
            $mergestr=[];
            foreach ($files as $file){
                array_push($mergestr,str_replace('mergepdf_code/public','public_html',public_path("upload_api/{$file->uuid_name}")));
            }
            $mergestr = implode(' ',$mergestr);
            $pdf_id = $files[0]->uuid_name;
            $str = "pdfunite {$mergestr} ".str_replace('mergepdf_code/public','public_html',public_path("download_api/{$pdf_id}"));
            shell_exec($str);
            $sub_arr = array(
                'file_id'=>$pdf_id,
                'download_path'=>asset("download_api/$pdf_id"),
                'zip_path'=>''
            );

            $downloadfiles = DownloadFile::updateOrCreate(
                ['uuid_name'=>$pdf_id],
                ['uuid_name'=>$pdf_id,'request_api'=>$hash,'download_path'=>asset("download_api/$pdf_id"),'zip_path'=>'']);
            $api_response['files']=$sub_arr;
            return response()->json($api_response);
        }
        else{
            $error_response = array(
                'code'=>500,
                'status'=>'Files not found',
                'message'=>'file not exist'
            );
            return response()->json($error_response);
        }


    }

    //*********************process api************//
    public function CheckProcess(Request $request){
        $hash= $request->hash;
        $counter = UploadFile::where('request_api_id',$hash)->get();
        $totalfiles = count($counter);
        $files = DownloadFile::where('request_api',$hash)->get();
        $completedfiles = count($files);
        $download_files = [];
        foreach ($files as $file){

            $sub_array = array(
                'file_id'=>$file->uuid_name,
                'download_path'=>$file->download_path,
                'file_size'=>$size=$this->fsize(public_path("download_api/{$file->uuid_name}"))
            );
//            dd(public_path("download_api/{$file->uuid_name}"));
//            $size=$this->fsize(public_path("download_api/{$file->uuid_name}"));
            array_push($download_files,$sub_array);
        }

        $api_response = array(
            'hash'=>$hash,
            'total_files'=>$totalfiles,
            'completed'=>$completedfiles,
            'remaining'=>$totalfiles-$completedfiles,
            'zip_path'=>$completedfiles==$totalfiles ? asset("download_api/$hash.zip") : '',
            'files'=>$download_files
        );
return response()->json($api_response);
    }
    public function UploadFileSize($size){
        $size = File::size($size);
        return $size;
    }
public function fsize($size){
        $size = File::size($size);
        if($size < 1024){
            $size = $size.'B';
        }elseif ($size < 1048576){
            $size = round((float)($size/1024));
//            $size = number_format((float)($size/1024),2,'.','');
            $size = $size.'KB';
        }elseif ($size < 1073741824){
            $size = round((float)($size/1048576));
//            $size = number_format((float)($size/1048576),2,'.','');
            $size = $size.'MB';
        }else{
            $size = round((float)($size/1073741824));
//            $size =number_format((float)$size/1073741824,2,'.','');
            $size = $size.'GB';
        }
        return $size;
}
public function AutoDel(){
    $uploadfiles = glob(public_path('upload_api/')."*.{xls,xls#,xlsx,xlsx#,csv,csv#,doc,doc#,docx,docx#,pdf,jpeg,jpeg#,jpg,jpg#,png,png#,zip,zip#,pdf#,ppt,ppt#,pptx,pptx#,svg,svg#,PNG,PNG#,PDF,PDF#,JPG,JPG#,JPEG,JPEG#}",GLOB_BRACE);
    $tmpfiles = glob(public_path('upload_api/').".~lock.*.{xls,xls#,xlsx,xlsx#,csv,csv#,doc,doc#,docx,docx#,pdf,jpeg,jpeg#,jpg,jpg#,png,png#,zip,zip#,pdf#,ppt,ppt#,pptx,pptx#,svg,svg#,PNG,PNG#,PDF,PDF#,JPG,JPG#,JPEG,JPEG#}",GLOB_BRACE);
//    $thumbnailfiles = glob(public_path('upload_api/')."*.{pdf,jpg,jpeg,png,xls,xlsx,csv,doc,docx,zip}",GLOB_BRACE);
    $downloadfiles = glob(public_path('download_api/')."*.{xls,xls#,xlsx,xlsx#,csv,csv#,doc,doc#,docx,docx#,pdf,jpeg,jpeg#,jpg,jpg#,png,png#,zip,zip#,pdf#,ppt,ppt#,pptx,pptx#,svg,svg#,PNG,PNG#,PDF,PDF#,JPG,JPG#,JPEG,JPEG#}",GLOB_BRACE);
    $tmpfiles_d = glob(public_path('download_api/').".~lock.*.{xls,xls#,xlsx,xlsx#,csv,csv#,doc,doc#,docx,docx#,pdf,jpeg,jpeg#,jpg,jpg#,png,png#,zip,zip#,pdf#,ppt,ppt#,pptx,pptx#,svg,svg#,PNG,PNG#,PDF,PDF#,JPG,JPG#,JPEG,JPEG#}",GLOB_BRACE);
    $storagefiles = glob(str_replace('compress_code/public','public_html',storage_path('app/chunks/'))."*.{xls,xls#,xlsx,xlsx#,csv,csv#,doc,doc#,docx,docx#,pdf,jpeg,jpeg#,jpg,jpg#,png,png#,zip,zip#,pdf#,ppt,ppt#,pptx,pptx#,svg,svg#,PNG,PNG#,PDF,PDF#,JPG,JPG#,JPEG,JPEG#}",GLOB_BRACE);
    $tmpfiles_s = glob(str_replace('compress_code/public','public_html',storage_path('app/chunks/')).".~lock.*.{xls,xls#,xlsx,xlsx#,csv,csv#,doc,doc#,docx,docx#,pdf,jpeg,jpeg#,jpg,jpg#,png,png#,zip,zip#,pdf#,ppt,ppt#,pptx,pptx#,svg,svg#,PNG,PNG#,PDF,PDF#,JPG,JPG#,JPEG,JPEG#}",GLOB_BRACE);

//        dd($uploadfiles,$thumbnailfiles,$downloadfiles);
    foreach ($uploadfiles as $uploadfile){
        if(time()-filemtime($uploadfile) > 28800){
            unlink($uploadfile);

        }
    }
    foreach ($tmpfiles as $tmp){
        if(time()-filemtime($tmp) > 28800){
            unlink($tmp);

        }
    }
    foreach ($tmpfiles_d as $tmp_d){
        if(time()-filemtime($tmp_d) > 28800){
            unlink($tmp_d);

        }
    }
    foreach ($tmpfiles_s as $tmp_s){
        if(time()-filemtime($tmp_s) > 28800){
            unlink($tmp_s);

        }
    }
    foreach ($downloadfiles as $downloadfile){
        if(time()-filemtime($downloadfile) > 28800){
            unlink($downloadfile);

        }
    }

    foreach ($storagefiles as $storagefile){
        if(time()-filemtime($storagefile) >28800){
//                dd($storagefile);
            unlink($storagefile);
        }
    }

    return 'done';
}

public function merge(Request $request){
        $hash = $request->hash;
        $action =$request->action;
        $api_response['hash']=$hash;
        $mergestr=[];
        $files= UploadFile::where('request_api_id',$hash)->get();
        foreach ($files as $file){
            array_push($mergestr,str_replace('mergepdf_code/public','public_html',public_path("upload_api/{$file->uuid_name}")));
        }
        $mergestr = implode(' ',$mergestr);
        $pdf_id = $files[0]->uuid_name;
        $str = "pdfunite {$mergestr} ".str_replace('mergepdf_code/public','public_html',public_path("download_api/{$pdf_id}"));
        shell_exec($str);
    $sub_arr = array(
        'file_id'=>$pdf_id,
        'download_path'=>asset("download_api/$pdf_id"),
        'zip_path'=>''
    );
    $api_response['files']=$sub_arr;
    return response()->json($api_response);
//        dd($sub_arr);
}
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function filelimit(){
        $data = ['pdf'=>10*1024*1024,'doc'=>10*1024*1024,'docx'=>10*1024*1024,'ppt'=>10*1024*1024,'pptx'=>10*1024*1024,'xlx'=>10*1024*1024,'xlxs'=>10*1024*1024,'img'=>10*1024*1024];
        return response()->json($data);
    }
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreConvertToolRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreConvertToolRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\ConvertTool  $convertTool
     * @return \Illuminate\Http\Response
     */
    public function show(ConvertTool $convertTool)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\ConvertTool  $convertTool
     * @return \Illuminate\Http\Response
     */
    public function edit(ConvertTool $convertTool)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateConvertToolRequest  $request
     * @param  \App\Models\ConvertTool  $convertTool
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateConvertToolRequest $request, ConvertTool $convertTool)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\ConvertTool  $convertTool
     * @return \Illuminate\Http\Response
     */
    public function destroy(ConvertTool $convertTool)
    {
        //
    }
}
