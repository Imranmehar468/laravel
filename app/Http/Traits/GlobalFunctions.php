<?php
namespace App\Http\Traits;
use App\Models\AllowedFileSize;
use App\Models\DownloadFile;
use App\Models\UploadFile;
use Illuminate\Http\Request;
use ZipArchive;
use File;
use Carbon\Carbon;
trait GlobalFunctions {
    public static function UploadFileSize($size) {
        $size = File::size($size);
        return $size;
    }
    public static function AutoDel(){
        $uploadFolder = glob(public_path('upload_api/*'));
        $downloadFolder = glob(public_path('download_api/*'));
        $storageFolder = glob(storage_path('app/chunks/*'));

        foreach ($uploadFolder as $uploadFile){
            if(time()-filemtime($uploadFile) > 28800){
                unlink($uploadFile);
            }
        }

        foreach ($downloadFolder as $downloadFile){
            if(time()-filemtime($downloadFile) > 28800){
                unlink($downloadFile);
            }
        }

        foreach ($storageFolder as $storageFile){
            if(time()-filemtime($storageFile) > 28800){
                unlink($storageFile);
            }
        }

    }
    public static function ZipFile($hash){
        $zip_path = $hash.".zip";
        $files = DownloadFile::where('request_api',$hash)->get();
        $zip = new ZipArchive;
        if($zip->open(str_replace(
            'mergepdf_code/public',
            'public_html',public_path(
                "download_api/$zip_path")),ZipArchive::CREATE)===TRUE){
            foreach ($files as  $file){
                $file_id =  str_replace('mergepdf_code/public',
                    'public_html',public_path(
                        "download_api/").$file->uuid_name);

                $relativeNameZipFile = basename($file->download_name);
                $zip->addFile($file_id,$relativeNameZipFile);
            }
            $zip->close();
        }
    }
    public static function CustomZipFile(array $globs,$reletiveName,$pattern){

        $zip_path = $reletiveName.".zip";

        $zip = new ZipArchive;
        if($zip->open(str_replace(
                'mergepdf_code/public',
                'public_html',public_path(
                "download_api/$zip_path")),ZipArchive::CREATE)===TRUE){
            foreach ($globs as  $file){

                $relativeNameZipFile = basename(str_replace($pattern,$reletiveName,$file));
                $zip->addFile($file,$relativeNameZipFile);
            }
            $zip->close();
        }
    }
    public static function UrlZipFile($hash){
        $zip_path = $hash.".zip";
        $files = DownloadFile::where('request_api',$hash)->get();
        $zip = new ZipArchive;
        if($zip->open(str_replace(
                'mergepdf_code/public',
                'public_html',public_path(
                "download_api/$zip_path")),ZipArchive::CREATE)===TRUE){
            foreach ($files as  $file){
                $file_id =  str_replace('mergepdf_code/public',
                    'public_html',public_path(
                        "download_api/").$file->download_name);
                $relativeNameZipFile = basename($file->download_name);
                $zip->addFile($file_id,$relativeNameZipFile);
            }
            $zip->close();
        }
    }
    public static function UploadFilesName($hash,$name,$extension){

//        $name = preg_replace("/\s+/",'_',$name);
        $tname = substr($name,0,strrpos($name,'.',-1));
        if(strlen(substr($tname,0,strrpos($tname,'(',-1))) >0){
            $tname = trim(substr($tname,0,strrpos($tname,'(',-1)));
        }
        $tname = preg_replace('/[^A-Za-z0-9\-]/', '', $tname);
        $tname = preg_replace('/\s+/','_',$tname);
        $checkname = UploadFile::where('request_api_id',$hash)->where('file_name','LIKE',"%$tname%")->get();
        $count =count($checkname);

        if($checkname == null || $count == 0){
            $checkname=$tname.".$extension";
            if(strlen(substr($checkname,0,strrpos($checkname,'(',-1))) >0){
                $checkname =substr($checkname,0,strrpos($checkname,'(',-1)).".".$extension;
            }
        }else{
            $k =$count-1;
            $checkname = $name;
            $checkname = substr($checkname,0,strrpos($checkname,'.'));
            if(strlen(substr($checkname,0,strrpos($checkname,'(',-1))) >0){
                $checkname = substr($checkname,0,strrpos($checkname,'(',-1));
                $checkname = preg_replace("/\s+/", "", $checkname);
            }
            $checkname = $checkname."($k).$extension";

        }
            return $checkname;
    }
    public static function DownloadFilesName($name,$extension){
        $name = substr($name,0,strrpos($name,'.',-1));
        $name= $name.".$extension";
        return $name;
    }
    public function MaxLimit($file){
        $size = AllowedFileSize::whereFiletype($file)->first();
        $size = $size->maxuploadsize;
        return $size;
    }


}
