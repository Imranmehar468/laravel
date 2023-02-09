<?php
namespace App\Http\Traits;
use App\Models\DownloadFile;
use App\Models\UploadFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use ZipArchive;
use File;
use Illuminate\Support\Str;
use Carbon\Carbon;
trait LibOfficeTools {
    use GlobalFunctions;
//    word to PDF
    public static function WordToPdf($hash, $action){
        $api_response['hash']= $hash;
        $api_response['zip_path']=asset("download_api/$hash.zip");
        $zip_path = $hash.".zip";
        $files = UploadFile::where('request_api_id',$hash)->get();
        $allowedFiles = array(
            'doc',
            'docx',
            'rtf',
            'txt'
        );

        if(!count($files)==0 && $action == 'dtp'){
            $pdf_array = [];
            foreach ($files as $file){
                $file_name = $file->uuid_name;
                $user_defined_name = GlobalFunctions::DownloadFilesName($file->file_name,'pdf');
                $ext = explode('.',$file_name);
                $pdf_id =$ext[0];
                $ext = strtolower($ext[1]);
                if(in_array($ext,$allowedFiles)){
                    $pdf_id = $pdf_id.".pdf";
                    $str =" soffice --headless --convert-to pdf:writer_pdf_Export ".str_replace(
                        'mergepdf_code/public',
                        'public_html',public_path(
                            "upload_api/$file_name"))." --outdir ".str_replace(
                                'mergepdf_code/public',
                                'public_html',public_path(
                                    'download_api/'));
                    shell_exec($str);

                    if(file_exists(
                        str_replace(
                            'mergepdf_code/public',
                            'public_html', public_path(
                                "download_api/$pdf_id")))) {
                        rename(public_path("/download_api/$pdf_id"),public_path("/download_api/$user_defined_name"));
                        $downloadFiles = DownloadFile::where('request_api',$hash)->get();
                        if(count($downloadFiles)==count($files)){
                            foreach ($downloadFiles as  $downloadFile){
                                $downloadFile->update(['zip_path'=>asset("download_api/$zip_path")]);
                            }
                        }
                        else{
                            $downloadFiles = DownloadFile::updateOrCreate(
                                ['uuid_name'=>$user_defined_name],
                                ['uuid_name'=>$user_defined_name,'request_api'=>$hash,
                                    'download_path'=>asset("download_api/$user_defined_name"),
                                    'zip_path'=>asset("download_api/$zip_path"),
                                    'download_name'=>$user_defined_name]
                            );
                        }
                        $sub_arr = array(
                            'file_id'=>$user_defined_name,
                            'download_path'=>asset("download_api/$user_defined_name")
                        );
                        array_push($pdf_array,$sub_arr);

                    }
                    else{
                        return response()->json(['code'=>404,
                            'status'=>'Files not exist.',
                            'message'=>'Files conversion failed']);

                    }

                }else{
                    return response()->json(['code'=>415,
                        'status'=>'File type is not allowed.',
                        'message'=>'File type is not allowed for this action.']);

                }
            }
            GlobalFunctions::ZipFile($hash);
            $api_response['files']=$pdf_array;
            return response()->json($api_response);
        }else{
            return response()->json(['code'=>404,
                'status'=>'File not Found.',
                'message'=>'Atleast one file is required for this action']);

        }

    }
//    Excel to PDF
    public static function ExcelToPdf($hash, $action){
        $api_response['hash']= $hash;
        $api_response['zip_path']=asset("download_api/$hash.zip");
        $zip_path = $hash.".zip";
//        ->selectRaw('SUM(size) as TotalSize,request_api_id')->groupBy('request_api_id')
        $files = UploadFile::where('request_api_id',$hash)->get();
        $totalSum = $files->sum('size');

        $allowedFiles = array(
            'xls',
            'xlsx',
            'csv'
        );

        if(!count($files)==0 && $action == 'etp'){
            $pdf_array = [];
            foreach ($files as $file){
                $file_name = $file->uuid_name;
                $user_defined_name = GlobalFunctions::DownloadFilesName($file->file_name,'pdf');
                $ext = explode('.',$file_name);
                $pdf_id =$ext[0];
                $ext = strtolower($ext[1]);
                if(in_array($ext,$allowedFiles)){
                    $pdf_id = $pdf_id.".pdf";
                    $str =" soffice --headless --convert-to pdf:writer_pdf_Export ".str_replace(
                            'mergepdf_code/public',
                            'public_html',public_path(
                            "upload_api/$file_name"))." --outdir ".str_replace(
                            'mergepdf_code/public',
                            'public_html',public_path(
                            'download_api/'));
                    shell_exec($str);

                    if(file_exists(
                        str_replace(
                            'mergepdf_code/public',
                            'public_html', public_path(
                            "download_api/$pdf_id")))) {
                        rename(public_path("/download_api/$pdf_id"),public_path("/download_api/$user_defined_name"));
                        $downloadFiles = DownloadFile::where('request_api',$hash)->get();
                        if(count($downloadFiles)==count($files)){
                            foreach ($downloadFiles as  $downloadFile){
                                $downloadFile->update(['zip_path'=>asset("download_api/$zip_path")]);
                            }
                        }
                        else{
                            $downloadFiles = DownloadFile::updateOrCreate(
                                ['uuid_name'=>$user_defined_name],
                                ['uuid_name'=>$user_defined_name,'request_api'=>$hash,
                                    'download_path'=>asset("download_api/$user_defined_name"),
                                    'zip_path'=>asset("download_api/$zip_path"),
                                    'download_name'=>$user_defined_name]
                            );
                        }
                        $sub_arr = array(
                            'file_id'=>$user_defined_name,
                            'download_path'=>asset("download_api/$user_defined_name")
                        );
                        array_push($pdf_array,$sub_arr);

                    }
                    else{
                        return response()->json(['code'=>404,
                            'status'=>'Files not exist.',
                            'message'=>'Files conversion failed']);

                    }

                }else{
                    return response()->json(['code'=>415,
                        'status'=>'File type is not allowed.',
                        'message'=>'File type is not allowed for this action.']);

                }
            }
            GlobalFunctions::ZipFile($hash);
            $api_response['files']=$pdf_array;
            return response()->json($api_response);
        }else{
            return response()->json(['code'=>404,
                'status'=>'File not Found.',
                'message'=>'Atleast one file is required for this action']);

        }

    }
//    Power Point to PDF
    public static function PowerPointToPdf($hash, $action){
        $api_response['hash']= $hash;
        $api_response['zip_path']=asset("download_api/$hash.zip");
        $zip_path = $hash.".zip";
        $files = UploadFile::where('request_api_id',$hash)->get();
        $allowedFiles = array(
            'ppt',
            'pptx'
        );

        if(!count($files)==0 && $action == 'pptp'){
            $pdf_array = [];
            foreach ($files as $file){
                $file_name = $file->uuid_name;
                $user_defined_name = GlobalFunctions::DownloadFilesName($file->file_name,'pdf');
                $ext = explode('.',$file_name);
                $pdf_id =$ext[0];
                $ext = strtolower($ext[1]);
                if(in_array($ext,$allowedFiles)){
                    $pdf_id = $pdf_id.".pdf";
                    $str =" soffice --headless --convert-to pdf:writer_pdf_Export ".str_replace(
                            'mergepdf_code/public',
                            'public_html',public_path(
                            "upload_api/$file_name"))." --outdir ".str_replace(
                            'mergepdf_code/public',
                            'public_html',public_path(
                            'download_api/'));
                    shell_exec($str);

                    if(file_exists(
                        str_replace(
                            'mergepdf_code/public',
                            'public_html', public_path(
                            "download_api/$pdf_id")))) {
                        rename(public_path("/download_api/$pdf_id"),public_path("/download_api/$user_defined_name"));
                        $downloadFiles = DownloadFile::where('request_api',$hash)->get();
                        if(count($downloadFiles)==count($files)){
                            foreach ($downloadFiles as  $downloadFile){
                                $downloadFile->update(['zip_path'=>asset("download_api/$zip_path")]);
                            }
                        }
                        else{
                            $downloadFiles = DownloadFile::updateOrCreate(
                                ['uuid_name'=>$user_defined_name],
                                ['uuid_name'=>$user_defined_name,'request_api'=>$hash,
                                    'download_path'=>asset("download_api/$user_defined_name"),
                                    'zip_path'=>asset("download_api/$zip_path"),
                                    'download_name'=>$user_defined_name]
                            );
                        }
                        $sub_arr = array(
                            'file_id'=>$user_defined_name,
                            'download_path'=>asset("download_api/$user_defined_name")
                        );
                        array_push($pdf_array,$sub_arr);

                    }
                    else{
                        return response()->json(['code'=>404,
                            'status'=>'Files not exist.',
                            'message'=>'Files conversion failed']);

                    }

                }else{
                    return response()->json(['code'=>415,
                        'status'=>'File type is not allowed.',
                        'message'=>'File type is not allowed for this action.']);

                }
            }
            GlobalFunctions::ZipFile($hash);
            $api_response['files']=$pdf_array;
            return response()->json($api_response);
        }else{
            return response()->json(['code'=>404,
                'status'=>'File not Found.',
                'message'=>'Atleast one file is required for this action']);

        }

    }
//    HTML to PDF
    public static function HtmlToPdf($hash, $action){
        $api_response['hash']= $hash;
        $api_response['zip_path']=asset("download_api/$hash.zip");
        $zip_path = $hash.".zip";
        $files = UploadFile::where('request_api_id',$hash)->get();
        $allowedFiles = array(
            'html'
        );

        if(!count($files)==0 && $action == 'htmltopdf'){
            $pdf_array = [];
            foreach ($files as $file){
                $file_name = $file->uuid_name;
                $user_defined_name = GlobalFunctions::DownloadFilesName($file->file_name,'pdf');
                $ext = explode('.',$file_name);
                $pdf_id =$ext[0];
                $ext = strtolower($ext[1]);
                if(in_array($ext,$allowedFiles)){
                    $pdf_id = $pdf_id.".pdf";
                    $str =" wkhtmltopdf --enable-local-file-access file://".str_replace(
                            'mergepdf_code/public',
                            'public_html',public_path(
                            "upload_api/$file_name"))."  ".str_replace(
                            'mergepdf_code/public',
                            'public_html',public_path(
                            'download_api/'.$pdf_id));
                    dd($str);
                    shell_exec($str);
//dd($pdf_id);
                    if(file_exists(
                        str_replace(
                            'mergepdf_code/public',
                            'public_html', public_path(
                            "download_api/$pdf_id")))) {
                        rename(public_path("/download_api/$pdf_id"),public_path("/download_api/$user_defined_name"));
                        $downloadFiles = DownloadFile::where('request_api',$hash)->get();
                        if(count($downloadFiles)==count($files)){
                            foreach ($downloadFiles as  $downloadFile){
                                $downloadFile->update(['zip_path'=>asset("download_api/$zip_path")]);
                            }
                        }
                        else{
                            $downloadFiles = DownloadFile::updateOrCreate(
                                ['uuid_name'=>$user_defined_name],
                                ['uuid_name'=>$user_defined_name,'request_api'=>$hash,
                                    'download_path'=>asset("download_api/$user_defined_name"),
                                    'zip_path'=>asset("download_api/$zip_path"),
                                    'download_name'=>$user_defined_name]
                            );
                        }
                        $sub_arr = array(
                            'file_id'=>$user_defined_name,
                            'download_path'=>asset("download_api/$user_defined_name")
                        );
                        array_push($pdf_array,$sub_arr);

                    }
                    else{
                        return response()->json(['code'=>404,
                            'status'=>'Files not exist.',
                            'message'=>'Files conversion failed']);

                    }

                }else{
                    return response()->json(['code'=>415,
                        'status'=>'File type is not allowed.',
                        'message'=>'File type is not allowed for this action.']);

                }
            }
            GlobalFunctions::ZipFile($hash);
            $api_response['files']=$pdf_array;
            return response()->json($api_response);
        }else{
            return response()->json(['code'=>404,
                'status'=>'File not Found.',
                'message'=>'Atleast one file is required for this action']);

        }

    }
//    URL to PDF
    public static function URLToPdf($hash, $action){
        $api_response['hash']= $hash;
        $api_response['zip_path']=asset("download_api/$hash.zip");
        $zip_path = $hash.".zip";
        $files = UploadFile::where('request_api_id',$hash)->get();


        if(!count($files)==0 && $action == 'urltopdf'){
            $pdf_array = [];
            foreach ($files as $file){
                $file_name = $file->file_name;
                $user_defined_name = Str::uuid()->toString().'.pdf';
                $pdf_id =$file_name;

//                ********************************


                    $str =" wkhtmltopdf --enable-local-file-access ".$pdf_id."  ".str_replace(
                            'mergepdf_code/public',
                            'public_html',public_path(
                            'download_api/'.$user_defined_name));
//                    dd($str);
                    shell_exec($str);
//dd($pdf_id);
                    if(file_exists(
                        str_replace(
                            'mergepdf_code/public',
                            'public_html', public_path(
                            "download_api/$user_defined_name")))) {

                        $downloadFiles = DownloadFile::where('request_api',$hash)->get();
                        if(count($downloadFiles)==count($files)){
                            foreach ($downloadFiles as  $downloadFile){
                                $downloadFile->update(['zip_path'=>asset("download_api/$zip_path")]);
                            }
                        }
                        else{
                            $tempvar = Str::uuid()->toString();
                            $downloadFiles = DownloadFile::updateOrCreate(
                                ['uuid_name'=>$tempvar],
                                ['uuid_name'=>$tempvar,'request_api'=>$hash,
                                    'download_path'=>asset("download_api/$user_defined_name"),
                                    'zip_path'=>asset("download_api/$zip_path"),
                                    'download_name'=>$user_defined_name]
                            );
                        }
                        $sub_arr = array(
                            'file_id'=>$user_defined_name,
                            'download_path'=>asset("download_api/$user_defined_name")
                        );
                        array_push($pdf_array,$sub_arr);

                    }
                    else{
                        return response()->json(['code'=>404,
                            'status'=>'Files not exist.',
                            'message'=>'Files conversion failed']);

                    }



// ********************************************
            }
            GlobalFunctions::UrlZipFile($hash);
            $api_response['files']=$pdf_array;
            return response()->json($api_response);
        }else{
            return response()->json(['code'=>404,
                'status'=>'File not Found.',
                'message'=>'Atleast one file is required for this action']);

        }

    }
//    PDF to Docx
    public static function PdfToDocx($hash, $action){
        $api_response['hash']= $hash;
        $api_response['zip_path']=asset("download_api/$hash.zip");
        $zip_path = $hash.".zip";
        $files = UploadFile::where('request_api_id',$hash)->get();
        $allowedFiles = array(
            'pdf'
        );

        if(!count($files)==0 && $action == 'ptd'){
            $pdf_array = [];
            foreach ($files as $file){
                $file_name = $file->uuid_name;
                $user_defined_name = GlobalFunctions::DownloadFilesName($file->file_name,'docx');
                $ext = explode('.',$file_name);
                $pdf_id =$ext[0];
                $ext = strtolower($ext[1]);
                if(in_array($ext,$allowedFiles)){
                    $pdf_id = $pdf_id.".docx";
                    $str =" soffice --headless --infilter='writer_pdf_import' --convert-to docx ".str_replace(
                            'mergepdf_code/public',
                            'public_html',public_path(
                            "upload_api/$file_name"))." --outdir ".str_replace(
                            'mergepdf_code/public',
                            'public_html',public_path(
                            'download_api/'));
                    shell_exec($str);

                    if(file_exists(
                        str_replace(
                            'mergepdf_code/public',
                            'public_html', public_path(
                            "download_api/$pdf_id")))) {
                        rename(public_path("/download_api/$pdf_id"),public_path("/download_api/$user_defined_name"));
                        $downloadFiles = DownloadFile::where('request_api',$hash)->get();
                        if(count($downloadFiles)==count($files)){
                            foreach ($downloadFiles as  $downloadFile){
                                $downloadFile->update(['zip_path'=>asset("download_api/$zip_path")]);
                            }
                        }
                        else{
                            $downloadFiles = DownloadFile::updateOrCreate(
                                ['uuid_name'=>$user_defined_name],
                                ['uuid_name'=>$user_defined_name,'request_api'=>$hash,
                                    'download_path'=>asset("download_api/$user_defined_name"),
                                    'zip_path'=>asset("download_api/$zip_path"),
                                    'download_name'=>$user_defined_name]
                            );
                        }
                        $sub_arr = array(
                            'file_id'=>$user_defined_name,
                            'download_path'=>asset("download_api/$user_defined_name")
                        );
                        array_push($pdf_array,$sub_arr);

                    }
                    else{
                        return response()->json(['code'=>404,
                            'status'=>'Files not exist.',
                            'message'=>'Files conversion failed']);

                    }

                }else{
                    return response()->json(['code'=>415,
                        'status'=>'File type is not allowed.',
                        'message'=>'File type is not allowed for this action.']);

                }
            }
            GlobalFunctions::ZipFile($hash);
            $api_response['files']=$pdf_array;
            return response()->json($api_response);
        }else{
            return response()->json(['code'=>404,
                'status'=>'File not Found.',
                'message'=>'Atleast one file is required for this action']);

        }

    }
//    Image to PDF
    public static function ImageToPdf($hash, $action){
        $api_response['hash']= $hash;
        $api_response['zip_path']=asset("download_api/$hash.zip");
        $zip_path = $hash.".zip";
        $files = UploadFile::where('request_api_id',$hash)->get();
        $allowedFiles = array(
            'png',
            'jpg',
            'jpeg',
            'svg',
            'tiff',
            'tif',
            'gif',
            'webp'
        );

        if(!count($files)==0 && $action == 'itp'){
            $pdf_array = [];
            foreach ($files as $file){
                $file_name = $file->uuid_name;
                $user_defined_name = GlobalFunctions::DownloadFilesName($file->file_name,'pdf');
                $ext = explode('.',$file_name);
                $pdf_id =$ext[0];
                $ext = strtolower($ext[1]);
                if(in_array($ext,$allowedFiles)){
                    $pdf_id = $pdf_id.".pdf";
                    $str =" convert ".str_replace(
                            'mergepdf_code/public',
                            'public_html',public_path(
                            "upload_api/$file_name"))." ".str_replace(
                            'mergepdf_code/public',
                            'public_html',public_path(
                            'download_api/'.$pdf_id));
                    shell_exec($str);

                    if(file_exists(str_replace(
                            'mergepdf_code/public',
                            'public_html', public_path(
                            "download_api/$pdf_id"))))
                    {
                        rename(public_path("/download_api/$pdf_id"),public_path("/download_api/$user_defined_name"));
                        $downloadFiles = DownloadFile::where('request_api',$hash)->get();
                        if(count($downloadFiles)==count($files)){
                            foreach ($downloadFiles as  $downloadFile){
                                $downloadFile->update(['zip_path'=>asset("download_api/$zip_path")]);
                            }
                        }
                        else{
                            $downloadFiles = DownloadFile::updateOrCreate(
                                ['uuid_name'=>$user_defined_name],
                                ['uuid_name'=>$user_defined_name,'request_api'=>$hash,
                                    'download_path'=>asset("download_api/$user_defined_name"),
                                    'zip_path'=>asset("download_api/$zip_path"),
                                    'download_name'=>$user_defined_name]
                            );
                        }
                        $sub_arr = array(
                            'file_id'=>$user_defined_name,
                            'download_path'=>asset("download_api/$user_defined_name")
                        );
                        array_push($pdf_array,$sub_arr);

                    }
                    else{
                        return response()->json(['code'=>404,
                            'status'=>'Files not exist.',
                            'message'=>'Files conversion failed']);

                    }

                }else{
                    return response()->json(['code'=>415,
                        'status'=>'File type is not allowed.',
                        'message'=>'File type is not allowed for this action.']);

                }
            }
            GlobalFunctions::ZipFile($hash);
            $api_response['files']=$pdf_array;
            return response()->json($api_response);
        }
        else{
            return response()->json(['code'=>404,
                'status'=>'File not Found.',
                'message'=>'Atleast one file is required for this action']);

        }

    }
//    Image to PDF (Single File)
    public static function ImageToSinglePdf($hash, $action){
        $api_response['hash']= $hash;
        $api_response['zip_path']="";
        $files = UploadFile::where('request_api_id',$hash)->get();
        $allowedFiles = array(
            'png',
            'jpg',
            'jpeg',
            'svg',
            'tiff',
            'tif',
            'gif',
            'webp'
        );

        if(!count($files)==0 && $action == 'mitp'){
            $pdf_array = [];
            $imageArray=[];
            foreach ($files as $file){
                $file_name = $file->uuid_name;
                $ext = explode('.',$file_name);
                $ext = strtolower($ext[1]);
                if(in_array($ext,$allowedFiles)){
                array_push($imageArray,public_path("upload_api/$file_name"));
                }
                else{
                    return response()->json(['code'=>415,
                        'status'=>'File type is not allowed.',
                        'message'=>'File type is not allowed for this action.']);

                }
            }
            $imageString = implode(' ',$imageArray);
            $user_defined_name = $hash.".pdf";
            $user_defined_name_path = public_path("download_api/$user_defined_name");
            $str = "convert $imageString $user_defined_name_path";
            shell_exec($str);
            if(file_exists($user_defined_name_path)){
                $downloadFiles = DownloadFile::updateOrCreate(
                    ['uuid_name'=>$user_defined_name],
                    ['uuid_name'=>$user_defined_name,'request_api'=>$hash,
                        'download_path'=>asset("download_api/$user_defined_name"),
                        'zip_path'=>"",
                        'download_name'=>$user_defined_name]
                );
                $sub_arr = array(
                    'file_id'=>$user_defined_name,
                    'download_path'=>asset("download_api/$user_defined_name")
                );
                array_push($pdf_array,$sub_arr);
            }
            else{
                return response()->json(['code'=>404,
                    'status'=>'Files not exist.',
                    'message'=>'Files conversion failed']);

            }
            $api_response['files']=$pdf_array;
            return response()->json($api_response);
            }
        else{
            return response()->json(['code'=>404,
                'status'=>'File not Found.',
                'message'=>'Atleast one file is required for this action']);

        }

        }
//    XPS to PDF
    public static function XpsToPdf($hash, $action){
        $api_response['hash']= $hash;
        $api_response['zip_path']=asset("download_api/$hash.zip");
        $zip_path = $hash.".zip";
        $files = UploadFile::where('request_api_id',$hash)->get();
        $allowedFiles = array(
            'xps'
        );

        if(!count($files)==0 && $action == 'xpstopdf'){
            $pdf_array = [];
            foreach ($files as $file){
                $file_name = $file->uuid_name;
                $user_defined_name = GlobalFunctions::DownloadFilesName($file->file_name,'pdf');
                $ext = explode('.',$file_name);
                $pdf_id =$ext[0];
                $ext = strtolower($ext[1]);
                if(in_array($ext,$allowedFiles)){
                    $pdf_id = $pdf_id.".pdf";
                    $str = "mutool convert -o ".public_path("download_api/$pdf_id")." ".public_path("upload_api/$file_name");
                    shell_exec($str);

                    if(file_exists(
                        str_replace(
                            'mergepdf_code/public',
                            'public_html', public_path(
                            "download_api/$pdf_id")))) {
                        rename(public_path("/download_api/$pdf_id"),public_path("/download_api/$user_defined_name"));
                        $downloadFiles = DownloadFile::where('request_api',$hash)->get();
                        if(count($downloadFiles)==count($files)){
                            foreach ($downloadFiles as  $downloadFile){
                                $downloadFile->update(['zip_path'=>asset("download_api/$zip_path")]);
                            }
                        }
                        else{
                            $downloadFiles = DownloadFile::updateOrCreate(
                                ['uuid_name'=>$user_defined_name],
                                ['uuid_name'=>$user_defined_name,'request_api'=>$hash,
                                    'download_path'=>asset("download_api/$user_defined_name"),
                                    'zip_path'=>asset("download_api/$zip_path"),
                                    'download_name'=>$user_defined_name]
                            );
                        }
                        $sub_arr = array(
                            'file_id'=>$user_defined_name,
                            'download_path'=>asset("download_api/$user_defined_name")
                        );
                        array_push($pdf_array,$sub_arr);

                    }
                    else{
                        return response()->json(['code'=>404,
                            'status'=>'Files not exist.',
                            'message'=>'Files conversion failed']);

                    }

                }else{
                    return response()->json(['code'=>415,
                        'status'=>'File type is not allowed.',
                        'message'=>'File type is not allowed for this action.']);

                }
            }
            GlobalFunctions::ZipFile($hash);
            $api_response['files']=$pdf_array;
            return response()->json($api_response);
        }else{
            return response()->json(['code'=>404,
                'status'=>'File not Found.',
                'message'=>'Atleast one file is required for this action']);

        }

    }
//    WEBP to PNG
    public static function WebpToPng($hash, $action){
        $api_response['hash']= $hash;
        $api_response['zip_path']=asset("download_api/$hash.zip");
        $zip_path = $hash.".zip";
        $files = UploadFile::where('request_api_id',$hash)->get();
        $allowedFiles = array(
            'webp'
        );

        if(!count($files)==0 && $action == 'webptopng'){
            $pdf_array = [];
            foreach ($files as $file){
                $file_name = $file->uuid_name;
                $user_defined_name = GlobalFunctions::DownloadFilesName($file->file_name,'png');
                $ext = explode('.',$file_name);
                $pdf_id =$ext[0];
                $ext = strtolower($ext[1]);
                if(in_array($ext,$allowedFiles)){
                    $pdf_id = $pdf_id.".png";
                    $str =" convert ".str_replace(
                            'mergepdf_code/public',
                            'public_html',public_path(
                            "upload_api/$file_name"))." -flatten ".str_replace(
                            'mergepdf_code/public',
                            'public_html',public_path(
                            'download_api/'.$pdf_id));
                    shell_exec($str);

                    if(file_exists(str_replace(
                        'mergepdf_code/public',
                        'public_html', public_path(
                        "download_api/$pdf_id"))))
                    {
                        rename(public_path("/download_api/$pdf_id"),public_path("/download_api/$user_defined_name"));
                        $downloadFiles = DownloadFile::where('request_api',$hash)->get();
                        if(count($downloadFiles)==count($files)){
                            foreach ($downloadFiles as  $downloadFile){
                                $downloadFile->update(['zip_path'=>asset("download_api/$zip_path")]);
                            }
                        }
                        else{
                            $downloadFiles = DownloadFile::updateOrCreate(
                                ['uuid_name'=>$user_defined_name],
                                ['uuid_name'=>$user_defined_name,'request_api'=>$hash,
                                    'download_path'=>asset("download_api/$user_defined_name"),
                                    'zip_path'=>asset("download_api/$zip_path"),
                                    'download_name'=>$user_defined_name]
                            );
                        }
                        $sub_arr = array(
                            'file_id'=>$user_defined_name,
                            'download_path'=>asset("download_api/$user_defined_name")
                        );
                        array_push($pdf_array,$sub_arr);

                    }
                    else{
                        return response()->json(['code'=>404,
                            'status'=>'Files not exist.',
                            'message'=>'Files conversion failed']);

                    }

                }else{
                    return response()->json(['code'=>415,
                        'status'=>'File type is not allowed.',
                        'message'=>'File type is not allowed for this action.']);

                }
            }
            GlobalFunctions::ZipFile($hash);
            $api_response['files']=$pdf_array;
            return response()->json($api_response);
        }
        else{
            return response()->json(['code'=>404,
                'status'=>'File not Found.',
                'message'=>'Atleast one file is required for this action']);

        }

    }
//    WEBP to JPEG
    public static function WebpToJpeg($hash, $action){
        $api_response['hash']= $hash;
        $api_response['zip_path']=asset("download_api/$hash.zip");
        $zip_path = $hash.".zip";
        $files = UploadFile::where('request_api_id',$hash)->get();
        $allowedFiles = array(
            'webp'
        );

        if(!count($files)==0 && $action == 'webptojpg'){
            $pdf_array = [];
            foreach ($files as $file){
                $file_name = $file->uuid_name;
                $user_defined_name = GlobalFunctions::DownloadFilesName($file->file_name,'jpeg');
                $ext = explode('.',$file_name);
                $pdf_id =$ext[0];
                $ext = strtolower($ext[1]);
                if(in_array($ext,$allowedFiles)){
                    $pdf_id = $pdf_id.".jpeg";
                    $str =" convert ".str_replace(
                            'mergepdf_code/public',
                            'public_html',public_path(
                            "upload_api/$file_name"))." -flatten ".str_replace(
                            'mergepdf_code/public',
                            'public_html',public_path(
                            'download_api/'.$pdf_id));
                    shell_exec($str);

                    if(file_exists(str_replace(
                        'mergepdf_code/public',
                        'public_html', public_path(
                        "download_api/$pdf_id"))))
                    {
                        rename(public_path("/download_api/$pdf_id"),public_path("/download_api/$user_defined_name"));
                        $downloadFiles = DownloadFile::where('request_api',$hash)->get();
                        if(count($downloadFiles)==count($files)){
                            foreach ($downloadFiles as  $downloadFile){
                                $downloadFile->update(['zip_path'=>asset("download_api/$zip_path")]);
                            }
                        }
                        else{
                            $downloadFiles = DownloadFile::updateOrCreate(
                                ['uuid_name'=>$user_defined_name],
                                ['uuid_name'=>$user_defined_name,'request_api'=>$hash,
                                    'download_path'=>asset("download_api/$user_defined_name"),
                                    'zip_path'=>asset("download_api/$zip_path"),
                                    'download_name'=>$user_defined_name]
                            );
                        }
                        $sub_arr = array(
                            'file_id'=>$user_defined_name,
                            'download_path'=>asset("download_api/$user_defined_name")
                        );
                        array_push($pdf_array,$sub_arr);

                    }
                    else{
                        return response()->json(['code'=>404,
                            'status'=>'Files not exist.',
                            'message'=>'Files conversion failed']);

                    }

                }else{
                    return response()->json(['code'=>415,
                        'status'=>'File type is not allowed.',
                        'message'=>'File type is not allowed for this action.']);

                }
            }
            GlobalFunctions::ZipFile($hash);
            $api_response['files']=$pdf_array;
            return response()->json($api_response);
        }
        else{
            return response()->json(['code'=>404,
                'status'=>'File not Found.',
                'message'=>'Atleast one file is required for this action']);

        }

    }
//    WEBP to GIF
    public static function WebpToGif($hash, $action){
        $api_response['hash']= $hash;
        $api_response['zip_path']=asset("download_api/$hash.zip");
        $zip_path = $hash.".zip";
        $files = UploadFile::where('request_api_id',$hash)->get();
        $allowedFiles = array(
            'webp'
        );

        if(!count($files)==0 && $action == 'webptogif'){
            $pdf_array = [];
            foreach ($files as $file){
                $file_name = $file->uuid_name;
                $user_defined_name = GlobalFunctions::DownloadFilesName($file->file_name,'gif');
                $ext = explode('.',$file_name);
                $pdf_id =$ext[0];
                $ext = strtolower($ext[1]);
                if(in_array($ext,$allowedFiles)){
                    $pdf_id = $pdf_id.".gif";
                    $str =" convert ".str_replace(
                            'mergepdf_code/public',
                            'public_html',public_path(
                            "upload_api/$file_name"))." ".str_replace(
                            'mergepdf_code/public',
                            'public_html',public_path(
                            'download_api/'.$pdf_id));
                    shell_exec($str);

                    if(file_exists(str_replace(
                        'mergepdf_code/public',
                        'public_html', public_path(
                        "download_api/$pdf_id"))))
                    {
                        rename(public_path("/download_api/$pdf_id"),public_path("/download_api/$user_defined_name"));
                        $downloadFiles = DownloadFile::where('request_api',$hash)->get();
                        if(count($downloadFiles)==count($files)){
                            foreach ($downloadFiles as  $downloadFile){
                                $downloadFile->update(['zip_path'=>asset("download_api/$zip_path")]);
                            }
                        }
                        else{
                            $downloadFiles = DownloadFile::updateOrCreate(
                                ['uuid_name'=>$user_defined_name],
                                ['uuid_name'=>$user_defined_name,'request_api'=>$hash,
                                    'download_path'=>asset("download_api/$user_defined_name"),
                                    'zip_path'=>asset("download_api/$zip_path"),
                                    'download_name'=>$user_defined_name]
                            );
                        }
                        $sub_arr = array(
                            'file_id'=>$user_defined_name,
                            'download_path'=>asset("download_api/$user_defined_name")
                        );
                        array_push($pdf_array,$sub_arr);

                    }
                    else{
                        return response()->json(['code'=>404,
                            'status'=>'Files not exist.',
                            'message'=>'Files conversion failed']);

                    }

                }else{
                    return response()->json(['code'=>415,
                        'status'=>'File type is not allowed.',
                        'message'=>'File type is not allowed for this action.']);

                }
            }
            GlobalFunctions::ZipFile($hash);
            $api_response['files']=$pdf_array;
            return response()->json($api_response);
        }
        else{
            return response()->json(['code'=>404,
                'status'=>'File not Found.',
                'message'=>'Atleast one file is required for this action']);

        }

    }
//    WEBP to TIFF
    public static function WebpToTiff($hash, $action){
        $api_response['hash']= $hash;
        $api_response['zip_path']=asset("download_api/$hash.zip");
        $zip_path = $hash.".zip";
        $files = UploadFile::where('request_api_id',$hash)->get();
        $allowedFiles = array(
            'webp'
        );

        if(!count($files)==0 && $action == 'webptotiff'){
            $pdf_array = [];
            foreach ($files as $file){
                $file_name = $file->uuid_name;
                $user_defined_name = GlobalFunctions::DownloadFilesName($file->file_name,'tiff');
                $ext = explode('.',$file_name);
                $pdf_id =$ext[0];
                $ext = strtolower($ext[1]);
                if(in_array($ext,$allowedFiles)){
                    $pdf_id = $pdf_id.".tiff";
                    $str =" convert ".str_replace(
                            'mergepdf_code/public',
                            'public_html',public_path(
                            "upload_api/$file_name"))." -flatten ".str_replace(
                            'mergepdf_code/public',
                            'public_html',public_path(
                            'download_api/'.$pdf_id));
                    shell_exec($str);

                    if(file_exists(str_replace(
                        'mergepdf_code/public',
                        'public_html', public_path(
                        "download_api/$pdf_id"))))
                    {
                        rename(public_path("/download_api/$pdf_id"),public_path("/download_api/$user_defined_name"));
                        $downloadFiles = DownloadFile::where('request_api',$hash)->get();
                        if(count($downloadFiles)==count($files)){
                            foreach ($downloadFiles as  $downloadFile){
                                $downloadFile->update(['zip_path'=>asset("download_api/$zip_path")]);
                            }
                        }
                        else{
                            $downloadFiles = DownloadFile::updateOrCreate(
                                ['uuid_name'=>$user_defined_name],
                                ['uuid_name'=>$user_defined_name,'request_api'=>$hash,
                                    'download_path'=>asset("download_api/$user_defined_name"),
                                    'zip_path'=>asset("download_api/$zip_path"),
                                    'download_name'=>$user_defined_name]
                            );
                        }
                        $sub_arr = array(
                            'file_id'=>$user_defined_name,
                            'download_path'=>asset("download_api/$user_defined_name")
                        );
                        array_push($pdf_array,$sub_arr);

                    }
                    else{
                        return response()->json(['code'=>404,
                            'status'=>'Files not exist.',
                            'message'=>'Files conversion failed']);

                    }

                }else{
                    return response()->json(['code'=>415,
                        'status'=>'File type is not allowed.',
                        'message'=>'File type is not allowed for this action.']);

                }
            }
            GlobalFunctions::ZipFile($hash);
            $api_response['files']=$pdf_array;
            return response()->json($api_response);
        }
        else{
            return response()->json(['code'=>404,
                'status'=>'File not Found.',
                'message'=>'Atleast one file is required for this action']);

        }

    }
//    PNG to JPEG
    public static function PngToJpg($hash, $action){
        $api_response['hash']= $hash;
        $api_response['zip_path']=asset("download_api/$hash.zip");
        $zip_path = $hash.".zip";
        $files = UploadFile::where('request_api_id',$hash)->get();
        $allowedFiles = array(
            'png'
        );

        if(!count($files)==0 && $action == 'pngtojpg'){
            $pdf_array = [];
            foreach ($files as $file){
                $file_name = $file->uuid_name;
                $user_defined_name = GlobalFunctions::DownloadFilesName($file->file_name,'jpeg');
                $ext = explode('.',$file_name);
                $pdf_id =$ext[0];
                $ext = strtolower($ext[1]);
                if(in_array($ext,$allowedFiles)){
                    $pdf_id = $pdf_id.".jpeg";
                    $str =" convert ".str_replace(
                            'mergepdf_code/public',
                            'public_html',public_path(
                            "upload_api/$file_name"))." -flatten ".str_replace(
                            'mergepdf_code/public',
                            'public_html',public_path(
                            'download_api/'.$pdf_id));
                    shell_exec($str);

                    if(file_exists(str_replace(
                        'mergepdf_code/public',
                        'public_html', public_path(
                        "download_api/$pdf_id"))))
                    {
                        rename(public_path("/download_api/$pdf_id"),public_path("/download_api/$user_defined_name"));
                        $downloadFiles = DownloadFile::where('request_api',$hash)->get();
                        if(count($downloadFiles)==count($files)){
                            foreach ($downloadFiles as  $downloadFile){
                                $downloadFile->update(['zip_path'=>asset("download_api/$zip_path")]);
                            }
                        }
                        else{
                            $downloadFiles = DownloadFile::updateOrCreate(
                                ['uuid_name'=>$user_defined_name],
                                ['uuid_name'=>$user_defined_name,'request_api'=>$hash,
                                    'download_path'=>asset("download_api/$user_defined_name"),
                                    'zip_path'=>asset("download_api/$zip_path"),
                                    'download_name'=>$user_defined_name]
                            );
                        }
                        $sub_arr = array(
                            'file_id'=>$user_defined_name,
                            'download_path'=>asset("download_api/$user_defined_name")
                        );
                        array_push($pdf_array,$sub_arr);

                    }
                    else{
                        return response()->json(['code'=>404,
                            'status'=>'Files not exist.',
                            'message'=>'Files conversion failed']);

                    }

                }else{
                    return response()->json(['code'=>415,
                        'status'=>'File type is not allowed.',
                        'message'=>'File type is not allowed for this action.']);

                }
            }
            GlobalFunctions::ZipFile($hash);
            $api_response['files']=$pdf_array;
            return response()->json($api_response);
        }
        else{
            return response()->json(['code'=>404,
                'status'=>'File not Found.',
                'message'=>'Atleast one file is required for this action']);

        }

    }
//    PNG to WEBP
    public static function PngToWebp($hash, $action){
        $api_response['hash']= $hash;
        $api_response['zip_path']=asset("download_api/$hash.zip");
        $zip_path = $hash.".zip";
        $files = UploadFile::where('request_api_id',$hash)->get();
        $allowedFiles = array(
            'png'
        );

        if(!count($files)==0 && $action == 'pngtowebp'){
            $pdf_array = [];
            foreach ($files as $file){
                $file_name = $file->uuid_name;
                $user_defined_name = GlobalFunctions::DownloadFilesName($file->file_name,'webp');
                $ext = explode('.',$file_name);
                $pdf_id =$ext[0];
                $ext = strtolower($ext[1]);
                if(in_array($ext,$allowedFiles)){
                    $pdf_id = $pdf_id.".webp";
                    $str =" convert ".str_replace(
                            'mergepdf_code/public',
                            'public_html',public_path(
                            "upload_api/$file_name"))." -flatten ".str_replace(
                            'mergepdf_code/public',
                            'public_html',public_path(
                            'download_api/'.$pdf_id));
                    shell_exec($str);

                    if(file_exists(str_replace(
                        'mergepdf_code/public',
                        'public_html', public_path(
                        "download_api/$pdf_id"))))
                    {
                        rename(public_path("/download_api/$pdf_id"),public_path("/download_api/$user_defined_name"));
                        $downloadFiles = DownloadFile::where('request_api',$hash)->get();
                        if(count($downloadFiles)==count($files)){
                            foreach ($downloadFiles as  $downloadFile){
                                $downloadFile->update(['zip_path'=>asset("download_api/$zip_path")]);
                            }
                        }
                        else{
                            $downloadFiles = DownloadFile::updateOrCreate(
                                ['uuid_name'=>$user_defined_name],
                                ['uuid_name'=>$user_defined_name,'request_api'=>$hash,
                                    'download_path'=>asset("download_api/$user_defined_name"),
                                    'zip_path'=>asset("download_api/$zip_path"),
                                    'download_name'=>$user_defined_name]
                            );
                        }
                        $sub_arr = array(
                            'file_id'=>$user_defined_name,
                            'download_path'=>asset("download_api/$user_defined_name")
                        );
                        array_push($pdf_array,$sub_arr);

                    }
                    else{
                        return response()->json(['code'=>404,
                            'status'=>'Files not exist.',
                            'message'=>'Files conversion failed']);

                    }

                }else{
                    return response()->json(['code'=>415,
                        'status'=>'File type is not allowed.',
                        'message'=>'File type is not allowed for this action.']);

                }
            }
            GlobalFunctions::ZipFile($hash);
            $api_response['files']=$pdf_array;
            return response()->json($api_response);
        }
        else{
            return response()->json(['code'=>404,
                'status'=>'File not Found.',
                'message'=>'Atleast one file is required for this action']);

        }

    }
//    JPEG to PNG
    public static function JpgToPng($hash, $action){
        $api_response['hash']= $hash;
        $api_response['zip_path']=asset("download_api/$hash.zip");
        $zip_path = $hash.".zip";
        $files = UploadFile::where('request_api_id',$hash)->get();
        $allowedFiles = array(
            'jpg','jpeg'
        );

        if(!count($files)==0 && $action == 'jpgtopng'){
            $pdf_array = [];
            foreach ($files as $file){
                $file_name = $file->uuid_name;
                $user_defined_name = GlobalFunctions::DownloadFilesName($file->file_name,'png');
                $ext = explode('.',$file_name);
                $pdf_id =$ext[0];
                $ext = strtolower($ext[1]);
                if(in_array($ext,$allowedFiles)){
                    $pdf_id = $pdf_id.".png";
                    $str =" convert ".str_replace(
                            'mergepdf_code/public',
                            'public_html',public_path(
                            "upload_api/$file_name"))." -flatten ".str_replace(
                            'mergepdf_code/public',
                            'public_html',public_path(
                            'download_api/'.$pdf_id));
                    shell_exec($str);

                    if(file_exists(str_replace(
                        'mergepdf_code/public',
                        'public_html', public_path(
                        "download_api/$pdf_id"))))
                    {
                        rename(public_path("/download_api/$pdf_id"),public_path("/download_api/$user_defined_name"));
                        $downloadFiles = DownloadFile::where('request_api',$hash)->get();
                        if(count($downloadFiles)==count($files)){
                            foreach ($downloadFiles as  $downloadFile){
                                $downloadFile->update(['zip_path'=>asset("download_api/$zip_path")]);
                            }
                        }
                        else{
                            $downloadFiles = DownloadFile::updateOrCreate(
                                ['uuid_name'=>$user_defined_name],
                                ['uuid_name'=>$user_defined_name,'request_api'=>$hash,
                                    'download_path'=>asset("download_api/$user_defined_name"),
                                    'zip_path'=>asset("download_api/$zip_path"),
                                    'download_name'=>$user_defined_name]
                            );
                        }
                        $sub_arr = array(
                            'file_id'=>$user_defined_name,
                            'download_path'=>asset("download_api/$user_defined_name")
                        );
                        array_push($pdf_array,$sub_arr);

                    }
                    else{
                        return response()->json(['code'=>404,
                            'status'=>'Files not exist.',
                            'message'=>'Files conversion failed']);

                    }

                }else{
                    return response()->json(['code'=>415,
                        'status'=>'File type is not allowed.',
                        'message'=>'File type is not allowed for this action.']);

                }
            }
            GlobalFunctions::ZipFile($hash);
            $api_response['files']=$pdf_array;
            return response()->json($api_response);
        }
        else{
            return response()->json(['code'=>404,
                'status'=>'File not Found.',
                'message'=>'Atleast one file is required for this action']);

        }

    }
//    JPEG to WEBP
    public static function JpegToWebp($hash, $action){
        $api_response['hash']= $hash;
        $api_response['zip_path']=asset("download_api/$hash.zip");
        $zip_path = $hash.".zip";
        $files = UploadFile::where('request_api_id',$hash)->get();
        $allowedFiles = array(
            'jpeg','jpg'
        );

        if(!count($files)==0 && $action == 'jpgtowebp'){
            $pdf_array = [];
            foreach ($files as $file){
                $file_name = $file->uuid_name;
                $user_defined_name = GlobalFunctions::DownloadFilesName($file->file_name,'webp');
                $ext = explode('.',$file_name);
                $pdf_id =$ext[0];
                $ext = strtolower($ext[1]);
                if(in_array($ext,$allowedFiles)){
                    $pdf_id = $pdf_id.".webp";
                    $str =" convert ".str_replace(
                            'mergepdf_code/public',
                            'public_html',public_path(
                            "upload_api/$file_name"))." -flatten ".str_replace(
                            'mergepdf_code/public',
                            'public_html',public_path(
                            'download_api/'.$pdf_id));
                    shell_exec($str);

                    if(file_exists(str_replace(
                        'mergepdf_code/public',
                        'public_html', public_path(
                        "download_api/$pdf_id"))))
                    {
                        rename(public_path("/download_api/$pdf_id"),public_path("/download_api/$user_defined_name"));
                        $downloadFiles = DownloadFile::where('request_api',$hash)->get();
                        if(count($downloadFiles)==count($files)){
                            foreach ($downloadFiles as  $downloadFile){
                                $downloadFile->update(['zip_path'=>asset("download_api/$zip_path")]);
                            }
                        }
                        else{
                            $downloadFiles = DownloadFile::updateOrCreate(
                                ['uuid_name'=>$user_defined_name],
                                ['uuid_name'=>$user_defined_name,'request_api'=>$hash,
                                    'download_path'=>asset("download_api/$user_defined_name"),
                                    'zip_path'=>asset("download_api/$zip_path"),
                                    'download_name'=>$user_defined_name]
                            );
                        }
                        $sub_arr = array(
                            'file_id'=>$user_defined_name,
                            'download_path'=>asset("download_api/$user_defined_name")
                        );
                        array_push($pdf_array,$sub_arr);

                    }
                    else{
                        return response()->json(['code'=>404,
                            'status'=>'Files not exist.',
                            'message'=>'Files conversion failed']);

                    }

                }else{
                    return response()->json(['code'=>415,
                        'status'=>'File type is not allowed.',
                        'message'=>'File type is not allowed for this action.']);

                }
            }
            GlobalFunctions::ZipFile($hash);
            $api_response['files']=$pdf_array;
            return response()->json($api_response);
        }
        else{
            return response()->json(['code'=>404,
                'status'=>'File not Found.',
                'message'=>'Atleast one file is required for this action']);

        }

    }
//    GIF to PNG
    public static function GifToPng($hash, $action){
        $api_response['hash']= $hash;
        $api_response['zip_path']=asset("download_api/$hash.zip");
        $zip_path = $hash.".zip";
        $files = UploadFile::where('request_api_id',$hash)->get();
        $allowedFiles = array(
            'gif'
        );

        if(!count($files)==0 && $action == 'giftopng'){
            $pdf_array = [];
            foreach ($files as $file){
                $file_name = $file->uuid_name;
                $user_defined_name = GlobalFunctions::DownloadFilesName($file->file_name,'png');
                $ext = explode('.',$file_name);
                $pdf_id =$ext[0];
                $ext = strtolower($ext[1]);
                if(in_array($ext,$allowedFiles)){
                    $pdf_id = $pdf_id.".png";
                    $str =" convert ".str_replace(
                            'mergepdf_code/public',
                            'public_html',public_path(
                            "upload_api/$file_name"))." -flatten ".str_replace(
                            'mergepdf_code/public',
                            'public_html',public_path(
                            'download_api/'.$pdf_id));
                    shell_exec($str);

                    if(file_exists(str_replace(
                        'mergepdf_code/public',
                        'public_html', public_path(
                        "download_api/$pdf_id"))))
                    {
                        rename(public_path("/download_api/$pdf_id"),public_path("/download_api/$user_defined_name"));
                        $downloadFiles = DownloadFile::where('request_api',$hash)->get();
                        if(count($downloadFiles)==count($files)){
                            foreach ($downloadFiles as  $downloadFile){
                                $downloadFile->update(['zip_path'=>asset("download_api/$zip_path")]);
                            }
                        }
                        else{
                            $downloadFiles = DownloadFile::updateOrCreate(
                                ['uuid_name'=>$user_defined_name],
                                ['uuid_name'=>$user_defined_name,'request_api'=>$hash,
                                    'download_path'=>asset("download_api/$user_defined_name"),
                                    'zip_path'=>asset("download_api/$zip_path"),
                                    'download_name'=>$user_defined_name]
                            );
                        }
                        $sub_arr = array(
                            'file_id'=>$user_defined_name,
                            'download_path'=>asset("download_api/$user_defined_name")
                        );
                        array_push($pdf_array,$sub_arr);

                    }
                    else{
                        return response()->json(['code'=>404,
                            'status'=>'Files not exist.',
                            'message'=>'Files conversion failed']);

                    }

                }else{
                    return response()->json(['code'=>415,
                        'status'=>'File type is not allowed.',
                        'message'=>'File type is not allowed for this action.']);

                }
            }
            GlobalFunctions::ZipFile($hash);
            $api_response['files']=$pdf_array;
            return response()->json($api_response);
        }
        else{
            return response()->json(['code'=>404,
                'status'=>'File not Found.',
                'message'=>'Atleast one file is required for this action']);

        }

    }
//    GIF to JPEG
    public static function GifToJpg($hash, $action){
        $api_response['hash']= $hash;
        $api_response['zip_path']=asset("download_api/$hash.zip");
        $zip_path = $hash.".zip";
        $files = UploadFile::where('request_api_id',$hash)->get();
        $allowedFiles = array(
            'gif'
        );

        if(!count($files)==0 && $action == 'giftojpg'){
            $pdf_array = [];
            foreach ($files as $file){
                $file_name = $file->uuid_name;
                $user_defined_name = GlobalFunctions::DownloadFilesName($file->file_name,'jpeg');
                $ext = explode('.',$file_name);
                $pdf_id =$ext[0];
                $ext = strtolower($ext[1]);
                if(in_array($ext,$allowedFiles)){
                    $pdf_id = $pdf_id.".jpeg";
                    $str =" convert ".str_replace(
                            'mergepdf_code/public',
                            'public_html',public_path(
                            "upload_api/$file_name"))." -flatten ".str_replace(
                            'mergepdf_code/public',
                            'public_html',public_path(
                            'download_api/'.$pdf_id));
                    shell_exec($str);

                    if(file_exists(str_replace(
                        'mergepdf_code/public',
                        'public_html', public_path(
                        "download_api/$pdf_id"))))
                    {
                        rename(public_path("/download_api/$pdf_id"),public_path("/download_api/$user_defined_name"));
                        $downloadFiles = DownloadFile::where('request_api',$hash)->get();
                        if(count($downloadFiles)==count($files)){
                            foreach ($downloadFiles as  $downloadFile){
                                $downloadFile->update(['zip_path'=>asset("download_api/$zip_path")]);
                            }
                        }
                        else{
                            $downloadFiles = DownloadFile::updateOrCreate(
                                ['uuid_name'=>$user_defined_name],
                                ['uuid_name'=>$user_defined_name,'request_api'=>$hash,
                                    'download_path'=>asset("download_api/$user_defined_name"),
                                    'zip_path'=>asset("download_api/$zip_path"),
                                    'download_name'=>$user_defined_name]
                            );
                        }
                        $sub_arr = array(
                            'file_id'=>$user_defined_name,
                            'download_path'=>asset("download_api/$user_defined_name")
                        );
                        array_push($pdf_array,$sub_arr);

                    }
                    else{
                        return response()->json(['code'=>404,
                            'status'=>'Files not exist.',
                            'message'=>'Files conversion failed']);

                    }

                }else{
                    return response()->json(['code'=>415,
                        'status'=>'File type is not allowed.',
                        'message'=>'File type is not allowed for this action.']);

                }
            }
            GlobalFunctions::ZipFile($hash);
            $api_response['files']=$pdf_array;
            return response()->json($api_response);
        }
        else{
            return response()->json(['code'=>404,
                'status'=>'File not Found.',
                'message'=>'Atleast one file is required for this action']);

        }

    }
//    GIF to WEBP
    public static function GifToWebp($hash, $action){
        $api_response['hash']= $hash;
        $api_response['zip_path']=asset("download_api/$hash.zip");
        $zip_path = $hash.".zip";
        $files = UploadFile::where('request_api_id',$hash)->get();
        $allowedFiles = array(
            'gif'
        );

        if(!count($files)==0 && $action == 'giftowebp'){
            $pdf_array = [];
            foreach ($files as $file){
                $file_name = $file->uuid_name;
                $user_defined_name = GlobalFunctions::DownloadFilesName($file->file_name,'webp');
                $ext = explode('.',$file_name);
                $pdf_id =$ext[0];
                $ext = strtolower($ext[1]);
                if(in_array($ext,$allowedFiles)){
                    $pdf_id = $pdf_id.".webp";
                    $str =" convert ".str_replace(
                            'mergepdf_code/public',
                            'public_html',public_path(
                            "upload_api/$file_name"))." ".str_replace(
                            'mergepdf_code/public',
                            'public_html',public_path(
                            'download_api/'.$pdf_id));
                    shell_exec($str);

                    if(file_exists(str_replace(
                        'mergepdf_code/public',
                        'public_html', public_path(
                        "download_api/$pdf_id"))))
                    {
                        rename(public_path("/download_api/$pdf_id"),public_path("/download_api/$user_defined_name"));
                        $downloadFiles = DownloadFile::where('request_api',$hash)->get();
                        if(count($downloadFiles)==count($files)){
                            foreach ($downloadFiles as  $downloadFile){
                                $downloadFile->update(['zip_path'=>asset("download_api/$zip_path")]);
                            }
                        }
                        else{
                            $downloadFiles = DownloadFile::updateOrCreate(
                                ['uuid_name'=>$user_defined_name],
                                ['uuid_name'=>$user_defined_name,'request_api'=>$hash,
                                    'download_path'=>asset("download_api/$user_defined_name"),
                                    'zip_path'=>asset("download_api/$zip_path"),
                                    'download_name'=>$user_defined_name]
                            );
                        }
                        $sub_arr = array(
                            'file_id'=>$user_defined_name,
                            'download_path'=>asset("download_api/$user_defined_name")
                        );
                        array_push($pdf_array,$sub_arr);

                    }
                    else{
                        return response()->json(['code'=>404,
                            'status'=>'Files not exist.',
                            'message'=>'Files conversion failed']);

                    }

                }else{
                    return response()->json(['code'=>415,
                        'status'=>'File type is not allowed.',
                        'message'=>'File type is not allowed for this action.']);

                }
            }
            GlobalFunctions::ZipFile($hash);
            $api_response['files']=$pdf_array;
            return response()->json($api_response);
        }
        else{
            return response()->json(['code'=>404,
                'status'=>'File not Found.',
                'message'=>'Atleast one file is required for this action']);

        }

    }
//    TIFF to PNG
    public static function TiffToPng($hash, $action){
        $api_response['hash']= $hash;
        $api_response['zip_path']=asset("download_api/$hash.zip");
        $zip_path = $hash.".zip";
        $files = UploadFile::where('request_api_id',$hash)->get();
        $allowedFiles = array(
            'tif',
            'tiff'
        );

        if(!count($files)==0 && $action == 'tifftopng'){
            $pdf_array = [];
            foreach ($files as $file){
                $file_name = $file->uuid_name;
                $user_defined_name = GlobalFunctions::DownloadFilesName($file->file_name,'png');
                $ext = explode('.',$file_name);
                $pdf_id =$ext[0];
                $ext = strtolower($ext[1]);
                if(in_array($ext,$allowedFiles)){
                    $pdf_id = $pdf_id.".png";
                    $str =" convert ".str_replace(
                            'mergepdf_code/public',
                            'public_html',public_path(
                            "upload_api/$file_name"))." -flatten ".str_replace(
                            'mergepdf_code/public',
                            'public_html',public_path(
                            'download_api/'.$pdf_id));
                    shell_exec($str);

                    if(file_exists(str_replace(
                        'mergepdf_code/public',
                        'public_html', public_path(
                        "download_api/$pdf_id"))))
                    {
                        rename(public_path("/download_api/$pdf_id"),public_path("/download_api/$user_defined_name"));
                        $downloadFiles = DownloadFile::where('request_api',$hash)->get();
                        if(count($downloadFiles)==count($files)){
                            foreach ($downloadFiles as  $downloadFile){
                                $downloadFile->update(['zip_path'=>asset("download_api/$zip_path")]);
                            }
                        }
                        else{
                            $downloadFiles = DownloadFile::updateOrCreate(
                                ['uuid_name'=>$user_defined_name],
                                ['uuid_name'=>$user_defined_name,'request_api'=>$hash,
                                    'download_path'=>asset("download_api/$user_defined_name"),
                                    'zip_path'=>asset("download_api/$zip_path"),
                                    'download_name'=>$user_defined_name]
                            );
                        }
                        $sub_arr = array(
                            'file_id'=>$user_defined_name,
                            'download_path'=>asset("download_api/$user_defined_name")
                        );
                        array_push($pdf_array,$sub_arr);

                    }
                    else{
                        return response()->json(['code'=>404,
                            'status'=>'Files not exist.',
                            'message'=>'Files conversion failed']);

                    }

                }else{
                    return response()->json(['code'=>415,
                        'status'=>'File type is not allowed.',
                        'message'=>'File type is not allowed for this action.']);

                }
            }
            GlobalFunctions::ZipFile($hash);
            $api_response['files']=$pdf_array;
            return response()->json($api_response);
        }
        else{
            return response()->json(['code'=>404,
                'status'=>'File not Found.',
                'message'=>'Atleast one file is required for this action']);

        }

    }
//    TIFF to JPEG
    public static function TiffToJpg($hash, $action){
        $api_response['hash']= $hash;
        $api_response['zip_path']=asset("download_api/$hash.zip");
        $zip_path = $hash.".zip";
        $files = UploadFile::where('request_api_id',$hash)->get();
        $allowedFiles = array(
            'tif',
            'tiff'
        );

        if(!count($files)==0 && $action == 'tifftojpg'){
            $pdf_array = [];
            foreach ($files as $file){
                $file_name = $file->uuid_name;
                $user_defined_name = GlobalFunctions::DownloadFilesName($file->file_name,'jpeg');
                $ext = explode('.',$file_name);
                $pdf_id =$ext[0];
                $ext = strtolower($ext[1]);
                if(in_array($ext,$allowedFiles)){
                    $pdf_id = $pdf_id.".jpeg";
                    $str =" convert ".str_replace(
                            'mergepdf_code/public',
                            'public_html',public_path(
                            "upload_api/$file_name"))." -flatten ".str_replace(
                            'mergepdf_code/public',
                            'public_html',public_path(
                            'download_api/'.$pdf_id));
                    shell_exec($str);

                    if(file_exists(str_replace(
                        'mergepdf_code/public',
                        'public_html', public_path(
                        "download_api/$pdf_id"))))
                    {
                        rename(public_path("/download_api/$pdf_id"),public_path("/download_api/$user_defined_name"));
                        $downloadFiles = DownloadFile::where('request_api',$hash)->get();
                        if(count($downloadFiles)==count($files)){
                            foreach ($downloadFiles as  $downloadFile){
                                $downloadFile->update(['zip_path'=>asset("download_api/$zip_path")]);
                            }
                        }
                        else{
                            $downloadFiles = DownloadFile::updateOrCreate(
                                ['uuid_name'=>$user_defined_name],
                                ['uuid_name'=>$user_defined_name,'request_api'=>$hash,
                                    'download_path'=>asset("download_api/$user_defined_name"),
                                    'zip_path'=>asset("download_api/$zip_path"),
                                    'download_name'=>$user_defined_name]
                            );
                        }
                        $sub_arr = array(
                            'file_id'=>$user_defined_name,
                            'download_path'=>asset("download_api/$user_defined_name")
                        );
                        array_push($pdf_array,$sub_arr);

                    }
                    else{
                        return response()->json(['code'=>404,
                            'status'=>'Files not exist.',
                            'message'=>'Files conversion failed']);

                    }

                }else{
                    return response()->json(['code'=>415,
                        'status'=>'File type is not allowed.',
                        'message'=>'File type is not allowed for this action.']);

                }
            }
            GlobalFunctions::ZipFile($hash);
            $api_response['files']=$pdf_array;
            return response()->json($api_response);
        }
        else{
            return response()->json(['code'=>404,
                'status'=>'File not Found.',
                'message'=>'Atleast one file is required for this action']);

        }

    }
//    TIFF to WEBP
    public static function TiffToWebp($hash, $action){
        $api_response['hash']= $hash;
        $api_response['zip_path']=asset("download_api/$hash.zip");
        $zip_path = $hash.".zip";
        $files = UploadFile::where('request_api_id',$hash)->get();
        $allowedFiles = array(
            'tif',
            'tiff'
        );

        if(!count($files)==0 && $action == 'tifftowebp'){
            $pdf_array = [];
            foreach ($files as $file){
                $file_name = $file->uuid_name;
                $user_defined_name = GlobalFunctions::DownloadFilesName($file->file_name,'webp');
                $ext = explode('.',$file_name);
                $pdf_id =$ext[0];
                $ext = strtolower($ext[1]);
                if(in_array($ext,$allowedFiles)){
                    $pdf_id = $pdf_id.".webp";
                    $str =" convert ".str_replace(
                            'mergepdf_code/public',
                            'public_html',public_path(
                            "upload_api/$file_name"))." -flatten ".str_replace(
                            'mergepdf_code/public',
                            'public_html',public_path(
                            'download_api/'.$pdf_id));
                    shell_exec($str);

                    if(file_exists(str_replace(
                        'mergepdf_code/public',
                        'public_html', public_path(
                        "download_api/$pdf_id"))))
                    {
                        rename(public_path("/download_api/$pdf_id"),public_path("/download_api/$user_defined_name"));
                        $downloadFiles = DownloadFile::where('request_api',$hash)->get();
                        if(count($downloadFiles)==count($files)){
                            foreach ($downloadFiles as  $downloadFile){
                                $downloadFile->update(['zip_path'=>asset("download_api/$zip_path")]);
                            }
                        }
                        else{
                            $downloadFiles = DownloadFile::updateOrCreate(
                                ['uuid_name'=>$user_defined_name],
                                ['uuid_name'=>$user_defined_name,'request_api'=>$hash,
                                    'download_path'=>asset("download_api/$user_defined_name"),
                                    'zip_path'=>asset("download_api/$zip_path"),
                                    'download_name'=>$user_defined_name]
                            );
                        }
                        $sub_arr = array(
                            'file_id'=>$user_defined_name,
                            'download_path'=>asset("download_api/$user_defined_name")
                        );
                        array_push($pdf_array,$sub_arr);

                    }
                    else{
                        return response()->json(['code'=>404,
                            'status'=>'Files not exist.',
                            'message'=>'Files conversion failed']);

                    }

                }else{
                    return response()->json(['code'=>415,
                        'status'=>'File type is not allowed.',
                        'message'=>'File type is not allowed for this action.']);

                }
            }
            GlobalFunctions::ZipFile($hash);
            $api_response['files']=$pdf_array;
            return response()->json($api_response);
        }
        else{
            return response()->json(['code'=>404,
                'status'=>'File not Found.',
                'message'=>'Atleast one file is required for this action']);

        }

    }
//    SVG to PNG
    public static function SvgToPng($hash, $action){
        $api_response['hash']= $hash;
        $api_response['zip_path']=asset("download_api/$hash.zip");
        $zip_path = $hash.".zip";
        $files = UploadFile::where('request_api_id',$hash)->get();
        $allowedFiles = array(
            'svg'
        );

        if(!count($files)==0 && $action == 'svgtopng'){
            $pdf_array = [];
            foreach ($files as $file){
                $file_name = $file->uuid_name;
                $user_defined_name = GlobalFunctions::DownloadFilesName($file->file_name,'png');
                $ext = explode('.',$file_name);
                $pdf_id =$ext[0];
                $ext = strtolower($ext[1]);
                if(in_array($ext,$allowedFiles)){
                    $pdf_id = $pdf_id.".png";
                    $str =" convert ".str_replace(
                            'mergepdf_code/public',
                            'public_html',public_path(
                            "upload_api/$file_name"))." -flatten ".str_replace(
                            'mergepdf_code/public',
                            'public_html',public_path(
                            'download_api/'.$pdf_id));
                    shell_exec($str);

                    if(file_exists(str_replace(
                        'mergepdf_code/public',
                        'public_html', public_path(
                        "download_api/$pdf_id"))))
                    {
                        rename(public_path("/download_api/$pdf_id"),public_path("/download_api/$user_defined_name"));
                        $downloadFiles = DownloadFile::where('request_api',$hash)->get();
                        if(count($downloadFiles)==count($files)){
                            foreach ($downloadFiles as  $downloadFile){
                                $downloadFile->update(['zip_path'=>asset("download_api/$zip_path")]);
                            }
                        }
                        else{
                            $downloadFiles = DownloadFile::updateOrCreate(
                                ['uuid_name'=>$user_defined_name],
                                ['uuid_name'=>$user_defined_name,'request_api'=>$hash,
                                    'download_path'=>asset("download_api/$user_defined_name"),
                                    'zip_path'=>asset("download_api/$zip_path"),
                                    'download_name'=>$user_defined_name]
                            );
                        }
                        $sub_arr = array(
                            'file_id'=>$user_defined_name,
                            'download_path'=>asset("download_api/$user_defined_name")
                        );
                        array_push($pdf_array,$sub_arr);

                    }
                    else{
                        return response()->json(['code'=>404,
                            'status'=>'Files not exist.',
                            'message'=>'Files conversion failed']);

                    }

                }else{
                    return response()->json(['code'=>415,
                        'status'=>'File type is not allowed.',
                        'message'=>'File type is not allowed for this action.']);

                }
            }
            GlobalFunctions::ZipFile($hash);
            $api_response['files']=$pdf_array;
            return response()->json($api_response);
        }
        else{
            return response()->json(['code'=>404,
                'status'=>'File not Found.',
                'message'=>'Atleast one file is required for this action']);

        }

    }
//    SVG to JPEG
    public static function SvgToJpg($hash, $action){
        $api_response['hash']= $hash;
        $api_response['zip_path']=asset("download_api/$hash.zip");
        $zip_path = $hash.".zip";
        $files = UploadFile::where('request_api_id',$hash)->get();
        $allowedFiles = array(
            'svg'
        );

        if(!count($files)==0 && $action == 'svgtojpg'){
            $pdf_array = [];
            foreach ($files as $file){
                $file_name = $file->uuid_name;
                $user_defined_name = GlobalFunctions::DownloadFilesName($file->file_name,'jpeg');
                $ext = explode('.',$file_name);
                $pdf_id =$ext[0];
                $ext = strtolower($ext[1]);
                if(in_array($ext,$allowedFiles)){
                    $pdf_id = $pdf_id.".jpeg";
                    $str =" convert ".str_replace(
                            'mergepdf_code/public',
                            'public_html',public_path(
                            "upload_api/$file_name"))." -flatten ".str_replace(
                            'mergepdf_code/public',
                            'public_html',public_path(
                            'download_api/'.$pdf_id));
                    shell_exec($str);

                    if(file_exists(str_replace(
                        'mergepdf_code/public',
                        'public_html', public_path(
                        "download_api/$pdf_id"))))
                    {
                        rename(public_path("/download_api/$pdf_id"),public_path("/download_api/$user_defined_name"));
                        $downloadFiles = DownloadFile::where('request_api',$hash)->get();
                        if(count($downloadFiles)==count($files)){
                            foreach ($downloadFiles as  $downloadFile){
                                $downloadFile->update(['zip_path'=>asset("download_api/$zip_path")]);
                            }
                        }
                        else{
                            $downloadFiles = DownloadFile::updateOrCreate(
                                ['uuid_name'=>$user_defined_name],
                                ['uuid_name'=>$user_defined_name,'request_api'=>$hash,
                                    'download_path'=>asset("download_api/$user_defined_name"),
                                    'zip_path'=>asset("download_api/$zip_path"),
                                    'download_name'=>$user_defined_name]
                            );
                        }
                        $sub_arr = array(
                            'file_id'=>$user_defined_name,
                            'download_path'=>asset("download_api/$user_defined_name")
                        );
                        array_push($pdf_array,$sub_arr);

                    }
                    else{
                        return response()->json(['code'=>404,
                            'status'=>'Files not exist.',
                            'message'=>'Files conversion failed']);

                    }

                }else{
                    return response()->json(['code'=>415,
                        'status'=>'File type is not allowed.',
                        'message'=>'File type is not allowed for this action.']);

                }
            }
            GlobalFunctions::ZipFile($hash);
            $api_response['files']=$pdf_array;
            return response()->json($api_response);
        }
        else{
            return response()->json(['code'=>404,
                'status'=>'File not Found.',
                'message'=>'Atleast one file is required for this action']);

        }

    }
//    SVG to WEBP
    public static function SvgToWebp($hash, $action){
        $api_response['hash']= $hash;
        $api_response['zip_path']=asset("download_api/$hash.zip");
        $zip_path = $hash.".zip";
        $files = UploadFile::where('request_api_id',$hash)->get();
        $allowedFiles = array(
            'svg'
        );

        if(!count($files)==0 && $action == 'svgtowebp'){
            $pdf_array = [];
            foreach ($files as $file){
                $file_name = $file->uuid_name;
                $user_defined_name = GlobalFunctions::DownloadFilesName($file->file_name,'webp');
                $ext = explode('.',$file_name);
                $pdf_id =$ext[0];
                $ext = strtolower($ext[1]);
                if(in_array($ext,$allowedFiles)){
                    $pdf_id = $pdf_id.".webp";
                    $str =" convert ".str_replace(
                            'mergepdf_code/public',
                            'public_html',public_path(
                            "upload_api/$file_name"))." -flatten ".str_replace(
                            'mergepdf_code/public',
                            'public_html',public_path(
                            'download_api/'.$pdf_id));
                    shell_exec($str);

                    if(file_exists(str_replace(
                        'mergepdf_code/public',
                        'public_html', public_path(
                        "download_api/$pdf_id"))))
                    {
                        rename(public_path("/download_api/$pdf_id"),public_path("/download_api/$user_defined_name"));
                        $downloadFiles = DownloadFile::where('request_api',$hash)->get();
                        if(count($downloadFiles)==count($files)){
                            foreach ($downloadFiles as  $downloadFile){
                                $downloadFile->update(['zip_path'=>asset("download_api/$zip_path")]);
                            }
                        }
                        else{
                            $downloadFiles = DownloadFile::updateOrCreate(
                                ['uuid_name'=>$user_defined_name],
                                ['uuid_name'=>$user_defined_name,'request_api'=>$hash,
                                    'download_path'=>asset("download_api/$user_defined_name"),
                                    'zip_path'=>asset("download_api/$zip_path"),
                                    'download_name'=>$user_defined_name]
                            );
                        }
                        $sub_arr = array(
                            'file_id'=>$user_defined_name,
                            'download_path'=>asset("download_api/$user_defined_name")
                        );
                        array_push($pdf_array,$sub_arr);

                    }
                    else{
                        return response()->json(['code'=>404,
                            'status'=>'Files not exist.',
                            'message'=>'Files conversion failed']);

                    }

                }else{
                    return response()->json(['code'=>415,
                        'status'=>'File type is not allowed.',
                        'message'=>'File type is not allowed for this action.']);

                }
            }
            GlobalFunctions::ZipFile($hash);
            $api_response['files']=$pdf_array;
            return response()->json($api_response);
        }
        else{
            return response()->json(['code'=>404,
                'status'=>'File not Found.',
                'message'=>'Atleast one file is required for this action']);

        }

    }
//    PDF to PNG
    public static function PdfToPng($hash, $action){

        $api_response['hash']= $hash;
        $api_response['zip_path']=asset("download_api/$hash.zip");
        $zip_path = $hash.".zip";
        $files = UploadFile::where('request_api_id',$hash)->get();
        $allowedFiles = array(
            'pdf'
        );

        if(!count($files)==0 && $action == 'pdftopng'){
            $pdf_array = [];
            $user_name = '';
            foreach ($files as $file){
                $file_name = $file->uuid_name;
                $user_defined_name = substr($file->file_name,0,strrpos($file->file_name,'.',-1));
                $ext = explode('.',$file_name);
                $pdf_id =$ext[0];
                $ext = strtolower($ext[1]);
                if(in_array($ext,$allowedFiles)){

                    $str =" pdftoppm ".str_replace(
                            'mergepdf_code/public',
                            'public_html',public_path(
                            "upload_api/$file_name"))." ".str_replace(
                            'mergepdf_code/public',
                            'public_html',public_path(
                            'download_api/'.$pdf_id))." -png";
                    shell_exec($str);
                    $globs=glob(public_path('download_api/'.$pdf_id)."*",GLOB_BRACE);
                    if(count($globs) > 0)
                    {



                        $sub_zip_path = $user_defined_name.".zip";
                        $downloadFiles = DownloadFile::updateOrCreate(
                            ['uuid_name'=>$user_defined_name.".zip"],
                            ['uuid_name'=>$user_defined_name.".zip",'request_api'=>$hash,
                                'download_path'=>asset("download_api/$sub_zip_path"),
                                'zip_path'=>asset("download_api/$zip_path"),
                                'download_name'=>$sub_zip_path]
                        );

                        GlobalFunctions::CustomZipFile($globs,$user_defined_name,$pdf_id);
                        foreach ($globs as $glob)
                        {
                            if(file_exists($glob))
                            {
                                unlink($glob);
                            }
                        }

                        $sub_arr = array(
                            'file_id'=>$sub_zip_path,
                            'download_path'=>asset("download_api/$sub_zip_path")
                        );

                        array_push($pdf_array,$sub_arr);


                    }
                    else{
                        return response()->json(['code'=>404,
                            'status'=>'Files not exist.',
                            'message'=>'Files conversion failed']);

                    }

                }else{
                    return response()->json(['code'=>415,
                        'status'=>'File type is not allowed.',
                        'message'=>'File type is not allowed for this action.']);

                }

            }


            GlobalFunctions::ZipFile($hash);
            $api_response['files']=$pdf_array;
            return response()->json($api_response);
        }
        else{
            return response()->json(['code'=>404,
                'status'=>'File not Found.',
                'message'=>'Atleast one file is required for this action']);

        }

    }
//    PDF to JPEG
    public static function PdfToJpeg($hash, $action){

        $api_response['hash']= $hash;
        $api_response['zip_path']=asset("download_api/$hash.zip");
        $zip_path = $hash.".zip";
        $files = UploadFile::where('request_api_id',$hash)->get();
        $allowedFiles = array(
            'pdf'
        );

        if(!count($files)==0 && $action == 'pdftojpg'){
            $pdf_array = [];
            $user_name = '';
            foreach ($files as $file){
                $file_name = $file->uuid_name;
                $user_defined_name = substr($file->file_name,0,strrpos($file->file_name,'.',-1));
                $ext = explode('.',$file_name);
                $pdf_id =$ext[0];
                $ext = strtolower($ext[1]);
                if(in_array($ext,$allowedFiles)){

                    $str =" pdftoppm ".str_replace(
                            'mergepdf_code/public',
                            'public_html',public_path(
                            "upload_api/$file_name"))." ".str_replace(
                            'mergepdf_code/public',
                            'public_html',public_path(
                            'download_api/'.$pdf_id))." -jpeg";
                    shell_exec($str);
                    $globs=glob(public_path('download_api/'.$pdf_id)."*",GLOB_BRACE);
                    if(count($globs) > 0)
                    {



                        $sub_zip_path = $user_defined_name.".zip";
                        $downloadFiles = DownloadFile::updateOrCreate(
                            ['uuid_name'=>$user_defined_name.".zip"],
                            ['uuid_name'=>$user_defined_name.".zip",'request_api'=>$hash,
                                'download_path'=>asset("download_api/$sub_zip_path"),
                                'zip_path'=>asset("download_api/$zip_path"),
                                'download_name'=>$sub_zip_path]
                        );

                        GlobalFunctions::CustomZipFile($globs,$user_defined_name,$pdf_id);
                        foreach ($globs as $glob)
                        {
                            if(file_exists($glob))
                            {
                                unlink($glob);
                            }
                        }

                        $sub_arr = array(
                            'file_id'=>$sub_zip_path,
                            'download_path'=>asset("download_api/$sub_zip_path")
                        );

                        array_push($pdf_array,$sub_arr);


                    }
                    else{
                        return response()->json(['code'=>404,
                            'status'=>'Files not exist.',
                            'message'=>'Files conversion failed']);

                    }

                }else{
                    return response()->json(['code'=>415,
                        'status'=>'File type is not allowed.',
                        'message'=>'File type is not allowed for this action.']);

                }

            }


            GlobalFunctions::ZipFile($hash);
            $api_response['files']=$pdf_array;
            return response()->json($api_response);
        }
        else{
            return response()->json(['code'=>404,
                'status'=>'File not Found.',
                'message'=>'Atleast one file is required for this action']);

        }

    }
//    PDF to WEBP
    public static function PdfToWebp($hash, $action){
        $api_response['hash']= $hash;
        $api_response['zip_path']=asset("download_api/$hash.zip");
        $zip_path = $hash.".zip";
        $files = UploadFile::where('request_api_id',$hash)->get();
        $allowedFiles = array(
            'pdf'
        );

        if(!count($files)==0 && $action == 'pdftowebp'){
            $pdf_array = [];
            foreach ($files as $file){
                $file_name = $file->uuid_name;
                $user_defined_name = GlobalFunctions::DownloadFilesName($file->file_name,'webp');
                $ext = explode('.',$file_name);
                $pdf_id =$ext[0];
                $tempName = $pdf_id;
                $ext = strtolower($ext[1]);
                if(in_array($ext,$allowedFiles)){
                    $pdf_id = $pdf_id.".webp";
                    $str = "pdftoppm ".public_path("upload_api/$file_name")." ".public_path("download_api/$tempName")." -png && convert -delay 60 ".public_path("download_api/").$tempName."*.png ".public_path("download_api/$pdf_id");
                    shell_exec($str);

                    if(file_exists(str_replace(
                        'mergepdf_code/public',
                        'public_html', public_path(
                        "download_api/$pdf_id"))))
                    {
                            foreach (glob(public_path("download_api/$tempName")."*.png",GLOB_BRACE) as $del_file){
                                    unlink($del_file);
                            }
                        rename(public_path("/download_api/$pdf_id"),public_path("/download_api/$user_defined_name"));
                        $downloadFiles = DownloadFile::where('request_api',$hash)->get();
                        if(count($downloadFiles)==count($files)){
                            foreach ($downloadFiles as  $downloadFile){
                                $downloadFile->update(['zip_path'=>asset("download_api/$zip_path")]);
                            }
                        }
                        else{
                            $downloadFiles = DownloadFile::updateOrCreate(
                                ['uuid_name'=>$user_defined_name],
                                ['uuid_name'=>$user_defined_name,'request_api'=>$hash,
                                    'download_path'=>asset("download_api/$user_defined_name"),
                                    'zip_path'=>asset("download_api/$zip_path"),
                                    'download_name'=>$user_defined_name]
                            );
                        }

                        $sub_arr = array(
                            'file_id'=>$user_defined_name,
                            'download_path'=>asset("download_api/$user_defined_name")
                        );
                        array_push($pdf_array,$sub_arr);

                    }
                    else{
                        return response()->json(['code'=>404,
                            'status'=>'Files not exist.',
                            'message'=>'Files conversion failed']);

                    }

                }else{
                    return response()->json(['code'=>415,
                        'status'=>'File type is not allowed.',
                        'message'=>'File type is not allowed for this action.']);

                }
            }
            GlobalFunctions::ZipFile($hash);
            $api_response['files']=$pdf_array;
            return response()->json($api_response);
        }
        else{
            return response()->json(['code'=>404,
                'status'=>'File not Found.',
                'message'=>'Atleast one file is required for this action']);

        }

    }
//    Merge PDF
    public static function MergePdf($hash, $action){
        $api_response['hash']= $hash;
        $api_response['zip_path']="";
        $files = UploadFile::where('request_api_id',$hash)->get();
        $allowedFiles = array(
            'pdf'
        );

        if(!count($files)==0 && $action == 'mergepdf'){
            $pdf_array = [];
            $imageArray=[];
            foreach ($files as $file){
                $file_name = $file->uuid_name;
                $ext = explode('.',$file_name);
                $ext = strtolower($ext[1]);
                if(in_array($ext,$allowedFiles)){
                    array_push($imageArray,public_path("upload_api/$file_name"));
                }
                else{
                    return response()->json(['code'=>415,
                        'status'=>'File type is not allowed.',
                        'message'=>'File type is not allowed for this action.']);

                }
            }
            $imageString = implode(' ',$imageArray);
            $user_defined_name = $hash.".pdf";
            $user_defined_name_path = public_path("download_api/$user_defined_name");
            $str = "mutool merge -o  $user_defined_name_path $imageString";
//            dd($str);
            shell_exec($str);
            if(file_exists($user_defined_name_path)){
                $downloadFiles = DownloadFile::updateOrCreate(
                    ['uuid_name'=>$user_defined_name],
                    ['uuid_name'=>$user_defined_name,'request_api'=>$hash,
                        'download_path'=>asset("download_api/$user_defined_name"),
                        'zip_path'=>"",
                        'download_name'=>$user_defined_name]
                );
                $sub_arr = array(
                    'file_id'=>$user_defined_name,
                    'download_path'=>asset("download_api/$user_defined_name")
                );
                array_push($pdf_array,$sub_arr);
            }
            else{
                return response()->json(['code'=>404,
                    'status'=>'Files not exist.',
                    'message'=>'Files conversion failed']);

            }
            $api_response['files']=$pdf_array;
            return response()->json($api_response);
        }
        else{
            return response()->json(['code'=>404,
                'status'=>'File not Found.',
                'message'=>'Atleast one file is required for this action']);

        }

    }
//    SPLIT PDF (ALL PAGES)
    public static function SplitAll($hash, $action){

        $api_response['hash']= $hash;
        $api_response['zip_path']=asset("download_api/$hash.zip");
        $zip_path = $hash.".zip";
        $files = UploadFile::where('request_api_id',$hash)->get();
        $allowedFiles = array(
            'pdf'
        );

        if(!count($files)==0 && $action == 'splitall'){
            $pdf_array = [];
            foreach ($files as $file){
                $file_name = $file->uuid_name;
                $user_defined_name = substr($file->file_name,0,strrpos($file->file_name,'.',-1));
                $ext = explode('.',$file_name);
                $pdf_id =$ext[0];
                $ext = strtolower($ext[1]);
                if(in_array($ext,$allowedFiles)){

                    $str =" pdftk ".str_replace(
                            'mergepdf_code/public',
                            'public_html',public_path(
                            "upload_api/$file_name"))." burst output ".str_replace(
                            'mergepdf_code/public',
                            'public_html',public_path(
                            'download_api/'.$pdf_id));

                    shell_exec($str);
                    $globs=glob(public_path('download_api/'.$pdf_id)."*",GLOB_BRACE);
                    if(count($globs) > 0)
                    {



                        $sub_zip_path = $user_defined_name.".zip";
                        $downloadFiles = DownloadFile::updateOrCreate(
                            ['uuid_name'=>$user_defined_name.".zip"],
                            ['uuid_name'=>$user_defined_name.".zip",'request_api'=>$hash,
                                'download_path'=>asset("download_api/$sub_zip_path"),
                                'zip_path'=>asset("download_api/$zip_path"),
                                'download_name'=>$sub_zip_path]
                        );

                        GlobalFunctions::CustomZipFile($globs,$user_defined_name,$pdf_id);
                        foreach ($globs as $glob)
                        {
                            if(file_exists($glob))
                            {
                                unlink($glob);
                            }
                        }

                        $sub_arr = array(
                            'file_id'=>$sub_zip_path,
                            'download_path'=>asset("download_api/$sub_zip_path")
                        );

                        array_push($pdf_array,$sub_arr);


                    }
                    else{
                        return response()->json(['code'=>404,
                            'status'=>'Files not exist.',
                            'message'=>'Files conversion failed']);

                    }

                }else{
                    return response()->json(['code'=>415,
                        'status'=>'File type is not allowed.',
                        'message'=>'File type is not allowed for this action.']);

                }

            }


            GlobalFunctions::ZipFile($hash);
            $api_response['files']=$pdf_array;
            return response()->json($api_response);
        }
        else{
            return response()->json(['code'=>404,
                'status'=>'File not Found.',
                'message'=>'Atleast one file is required for this action']);

        }

    }
//    SPLIT PDF (RANGES MERGED)
    public static function SplitRangesMerged($hash, $action){
        $api_response['hash']= $hash;
        $api_response['zip_path']=asset("download_api/$hash.zip");
        $zip_path = $hash.".zip";
        $files = UploadFile::where('request_api_id',$hash)->get();
        $allowedFiles = array(
            'pdf'
        );

        if(!count($files)==0 && $action == 'splitrangemerge'){
            $pdf_array = [];
            foreach ($files as $file){
                $file_name = $file->uuid_name;
                $user_defined_name = $file->file_name;
                $ext = explode('.',$file_name);
                $pdf_id =$file_name;
                $split_range = $file->split_range;
                $ext = strtolower($ext[1]);
                if(in_array($ext,$allowedFiles)){
                    $str = "pdftk ".public_path("upload_api/$file_name")." cat $split_range output ".public_path("download_api/$pdf_id");
                    shell_exec($str);

                    if(file_exists(
                        str_replace(
                            'mergepdf_code/public',
                            'public_html', public_path(
                            "download_api/$pdf_id")))) {
                        rename(public_path("/download_api/$pdf_id"),public_path("/download_api/$user_defined_name"));
                        $downloadFiles = DownloadFile::where('request_api',$hash)->get();
                        if(count($downloadFiles)==count($files)){
                            foreach ($downloadFiles as  $downloadFile){
                                $downloadFile->update(['zip_path'=>asset("download_api/$zip_path")]);
                            }
                        }
                        else{
                            $downloadFiles = DownloadFile::updateOrCreate(
                                ['uuid_name'=>$user_defined_name],
                                ['uuid_name'=>$user_defined_name,'request_api'=>$hash,
                                    'download_path'=>asset("download_api/$user_defined_name"),
                                    'zip_path'=>asset("download_api/$zip_path"),
                                    'download_name'=>$user_defined_name]
                            );
                        }
                        $sub_arr = array(
                            'file_id'=>$user_defined_name,
                            'download_path'=>asset("download_api/$user_defined_name")
                        );
                        array_push($pdf_array,$sub_arr);

                    }
                    else{
                        return response()->json(['code'=>404,
                            'status'=>'Files not exist.',
                            'message'=>'Files conversion failed']);

                    }

                }else{
                    return response()->json(['code'=>415,
                        'status'=>'File type is not allowed.',
                        'message'=>'File type is not allowed for this action.']);

                }
            }
            GlobalFunctions::ZipFile($hash);
            $api_response['files']=$pdf_array;
            return response()->json($api_response);
        }else{
            return response()->json(['code'=>404,
                'status'=>'File not Found.',
                'message'=>'Atleast one file is required for this action']);

        }

    }
//    SPLIT PDF (MULTIPLE FILES)
    public static function SplitPdfMultiFiles($hash, $action){
        $api_response['hash']= $hash;
        $api_response['zip_path']=asset("download_api/$hash.zip");
        $zip_path = $hash.".zip";
        $files = UploadFile::where('request_api_id',$hash)->get();
        $allowedFiles = array(
            'pdf'
        );

        if(!count($files)==0 && $action == 'splitrangemultifile'){
            $pdf_array = [];
            foreach ($files as $file){
                $file_name = $file->uuid_name;
                $user_defined_name = $file->file_name;
                $relativeName =substr($user_defined_name,0,strrpos($user_defined_name,'.',-1));
                $ext = explode('.',$file_name);
                $tempvar = $ext[0];
                $pdf_id =$file_name;
                $split_range = $file->split_range;
                $ext = strtolower($ext[1]);
                if(in_array($ext,$allowedFiles)){
                    $split_range = explode(' ',$split_range);
                    foreach ($split_range as $range){
                        $str = "pdftk ".public_path("upload_api/$file_name")." cat $range output ".public_path("download_api/$tempvar"."_$range".'.pdf');
                        shell_exec($str);
                    }
                    $globs = glob(public_path('download_api/'.$tempvar)."*",GLOB_BRACE);
                    if(count($globs) > 0){

                        $sub_zip_path = substr($user_defined_name,0,strrpos($user_defined_name,'.',-1)).".zip";
                        $downloadFiles = DownloadFile::updateOrCreate(
                            ['uuid_name'=>$sub_zip_path],
                            ['uuid_name'=>$sub_zip_path,'request_api'=>$hash,
                                'download_path'=>asset("download_api/$sub_zip_path"),
                                'zip_path'=>asset("download_api/$zip_path"),
                                'download_name'=>$sub_zip_path]
                        );
                        GlobalFunctions::CustomZipFile($globs,$relativeName,$tempvar);
                        foreach ($globs as $glob)
                        {
                            if(file_exists($glob))
                            {
                                unlink($glob);
                            }
                        }

                        $sub_arr = array(
                            'file_id'=>$sub_zip_path,
                            'download_path'=>asset("download_api/$sub_zip_path")
                        );

                        array_push($pdf_array,$sub_arr);
                    }
                    else{
                        return response()->json(['code'=>404,
                            'status'=>'Files not exist.',
                            'message'=>'Files conversion failed']);

                    }

                }else{
                    return response()->json(['code'=>415,
                        'status'=>'File type is not allowed.',
                        'message'=>'File type is not allowed for this action.']);

                }
            }
            GlobalFunctions::ZipFile($hash);
            $api_response['files']=$pdf_array;
            return response()->json($api_response);
        }else{
            return response()->json(['code'=>404,
                'status'=>'File not Found.',
                'message'=>'Atleast one file is required for this action']);

        }

    }
//    COMPRESS PDF
    public static function CompressPdf($hash, $action){
        $api_response['hash']= $hash;
        $api_response['zip_path']=asset("download_api/$hash.zip");
        $zip_path = $hash.".zip";
        $files = UploadFile::where('request_api_id',$hash)->get();
        $allowedFiles = array(
            'pdf'
        );

        if(!count($files)==0 && $action == 'compresspdf'){
            $pdf_array = [];
            foreach ($files as $file){
                $file_name = $file->uuid_name;
                $user_defined_name = GlobalFunctions::DownloadFilesName($file->file_name,'pdf');
                $ext = explode('.',$file_name);
                $pdf_id =$ext[0];
                $ext = strtolower($ext[1]);
                if(in_array($ext,$allowedFiles)){
                    $pdf_id = $pdf_id.".pdf";
//    "time gs -o /home2/compresserpdf/public_html/ud/downloads/3f126e69-afad-40e4-a1a5-a34ec3741885.pdf -dNOPAUSE -dBATCH -sDEVICE=pdfwrite -dCompatibilityLevel=1.4 -dMaxPatternBitmap=1000000000 -dPDFSETTINGS=/screen -dNumRenderingThreads=4 -dEmbedAllFonts=true -dSubsetFonts=true  -dQUIET -dColorImageResolution=75 -c '6000000000 setvmthreshold' -f /home2/compresserpdf/public_html/ud/uploads/3f126e69-afad-40e4-a1a5-a34ec3741885.pdf"
                   $str = "gs -o ".public_path('download_api/'.$pdf_id)." -dNOPAUSE -dBATCH -sDEVICE=pdfwrite -dCompatibilityLevel=1.4 -dMaxPatternBitmap=1000000000 -dPDFSETTINGS=/screen -dNumRenderingThreads=4 -dEmbedAllFonts=true -dSubsetFonts=true -dQUIET -dColorImageResolution=75 -c '6000000000 setvmthreshold' -f ".public_path('upload_api/'.$file_name);
//                   dd($str);
                    shell_exec($str);

                    if(file_exists(str_replace(
                        'mergepdf_code/public',
                        'public_html', public_path(
                        "download_api/$pdf_id"))))
                    {
                        if(self::UploadFileSize(public_path("download_api/$pdf_id")) > self::UploadFileSize(public_path("upload_api/$file_name")))
                        {
                        unlink(public_path("download_api/$pdf_id"));
                        copy(public_path("upload_api/$file_name"),public_path("/download_api/$user_defined_name"));

                        }
                        else
                            {
                            rename(public_path("/download_api/$pdf_id"), public_path("/download_api/$user_defined_name"));
                            }
                            $downloadFiles = DownloadFile::where('request_api',$hash)->get();
                            if(count($downloadFiles)==count($files)){
                                foreach ($downloadFiles as  $downloadFile){
                                    $downloadFile->update(['zip_path'=>asset("download_api/$zip_path")]);
                                }
                            }
                            else{
                                $downloadFiles = DownloadFile::updateOrCreate(
                                    ['uuid_name'=>$user_defined_name],
                                    ['uuid_name'=>$user_defined_name,'request_api'=>$hash,
                                        'download_path'=>asset("download_api/$user_defined_name"),
                                        'zip_path'=>asset("download_api/$zip_path"),
                                        'download_name'=>$user_defined_name]
                                );
                            }
                            $sub_arr = array(
                                'file_id'=>$user_defined_name,
                                'download_path'=>asset("download_api/$user_defined_name")
                            );
                            array_push($pdf_array,$sub_arr);



                    }
                    else{
                        return response()->json(['code'=>404,
                            'status'=>'Files not exist.',
                            'message'=>'Files conversion failed']);

                    }

                }else{
                    return response()->json(['code'=>415,
                        'status'=>'File type is not allowed.',
                        'message'=>'File type is not allowed for this action.']);

                }
            }
            GlobalFunctions::ZipFile($hash);
            $api_response['files']=$pdf_array;
            return response()->json($api_response);
        }
        else{
            return response()->json(['code'=>404,
                'status'=>'File not Found.',
                'message'=>'Atleast one file is required for this action']);

        }

    }
//    COMPRESS PNG
    public static function CompressImg($hash, $action){
        $api_response['hash']= $hash;
        $api_response['zip_path']=asset("download_api/$hash.zip");
        $zip_path = $hash.".zip";
        $files = UploadFile::where('request_api_id',$hash)->get();
        $allowedFiles = array(
            'png','jpg','jpeg'
        );

        if(!count($files)==0 && $action == 'compressimage'){
            $pdf_array = [];
            foreach ($files as $file){
                $file_name = $file->uuid_name;
                $ext = explode('.',$file_name);
                $pdf_id =$ext[0];
                $ext = strtolower($ext[1]);
                $user_defined_name = GlobalFunctions::DownloadFilesName($file->file_name,$ext);
                if(in_array($ext,$allowedFiles)){
                    $pdf_id = $pdf_id.".$ext";
                    if(strtolower($ext) == 'png'){
                        $str = "pngquant --force --quality=10-20  -o ".public_path('download_api/'.$pdf_id)." ".public_path("upload_api/$file_name");

                    }
                    elseif(strtolower($ext) == 'jpg' || strtolower($ext) == 'jpeg'){
                        $str = "jpegoptim -f -m 10 ".public_path("upload_api/$file_name")." -d ".public_path("download_api/");
                    }

                    shell_exec($str);

                    if(file_exists(str_replace(
                        'mergepdf_code/public',
                        'public_html', public_path(
                        "download_api/$pdf_id"))))
                    {
                        rename(public_path("/download_api/$pdf_id"),public_path("/download_api/$user_defined_name"));
                        $downloadFiles = DownloadFile::where('request_api',$hash)->get();
                        if(count($downloadFiles)==count($files)){
                            foreach ($downloadFiles as  $downloadFile){
                                $downloadFile->update(['zip_path'=>asset("download_api/$zip_path")]);
                            }
                        }
                        else{
                            $downloadFiles = DownloadFile::updateOrCreate(
                                ['uuid_name'=>$user_defined_name],
                                ['uuid_name'=>$user_defined_name,'request_api'=>$hash,
                                    'download_path'=>asset("download_api/$user_defined_name"),
                                    'zip_path'=>asset("download_api/$zip_path"),
                                    'download_name'=>$user_defined_name]
                            );
                        }
                        $sub_arr = array(
                            'file_id'=>$user_defined_name,
                            'download_path'=>asset("download_api/$user_defined_name")
                        );
                        array_push($pdf_array,$sub_arr);

                    }
                    else{
                        return response()->json(['code'=>404,
                            'status'=>'Files not exist.',
                            'message'=>'Files conversion failed']);

                    }

                }else{
                    return response()->json(['code'=>415,
                        'status'=>'File type is not allowed.',
                        'message'=>'File type is not allowed for this action.']);

                }
            }
            GlobalFunctions::ZipFile($hash);
            $api_response['files']=$pdf_array;
            return response()->json($api_response);
        }
        else{
            return response()->json(['code'=>404,
                'status'=>'File not Found.',
                'message'=>'Atleast one file is required for this action']);

        }

    }
}


