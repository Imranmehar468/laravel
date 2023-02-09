<?php

namespace App\Http\Controllers;

use App\Models\DownloadFile;
use App\Models\UploadFile;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Traits\LibOfficeTools;


class ActionController extends Controller
{
    use LibOfficeTools;
    public  function Converter(Request $request){
        $hash = $request->hash;
        $action = $request->action;
        $apikey = $request->header('x-api-key')??'';
        if($apikey == env('API_KEY')){
            if($hash && $action=='dtp')
            {
                $response = LibOfficeTools::WordToPdf($hash, $action);
                return $response;
            }
            elseif ($hash && $action == 'etp')
            {
                $response = LibOfficeTools::ExcelToPdf($hash, $action);
                return $response;
            }
            elseif ($hash && $action == 'pptp')
            {
                $response = LibOfficeTools::PowerPointToPdf($hash,$action);
                return $response;
            }
            elseif ($hash && $action == 'htmltopdf')
            {
                $response = LibOfficeTools::HtmlToPdf($hash, $action);
                return $response;
            }
            elseif ($hash && $action == 'urltopdf')
            {
                $response = LibOfficeTools::URLToPdf($hash, $action);
                return $response;
            }
            elseif ($hash && $action == 'ptd')
            {
                $response = LibOfficeTools::PdfToDocx($hash, $action);
                return $response;
            }
            elseif ($hash && $action == 'itp')
            {
                $response = LibOfficeTools::ImageToPdf($hash,$action);
                return $response;
            }
            elseif ($hash && $action == 'mitp')
            {
                $response = LibOfficeTools::ImageToSinglePdf($hash,$action);
                return $response;
            }
            elseif ($hash && $action == 'xpstopdf')
            {
                $response = LibOfficeTools::XpsToPdf($hash,$action);
                return $response;
            }
            elseif ($hash && $action == 'pngtojpg')
            {
                $response = LibOfficeTools::PngToJpg($hash,$action);
                return $response;
            }
            elseif ($hash && $action == 'jpgtopng')
            {
                $response = LibOfficeTools::JpgToPng($hash,$action);
                return $response;
            }
            elseif ($hash && $action == 'giftopng')
            {
                $response = LibOfficeTools::GifToPng($hash,$action);
                return $response;
            }
            elseif ($hash && $action == 'giftojpg')
            {
                $response = LibOfficeTools::GifToJpg($hash,$action);
                return $response;
            }
            elseif ($hash && $action == 'tifftopng')
            {
                $response = LibOfficeTools::TiffToPng($hash,$action);
                return $response;
            }
            elseif ($hash && $action == 'tifftojpg')
            {
                $response = LibOfficeTools::TiffToJpg($hash,$action);
                return $response;
            }
            elseif ($hash && $action == 'svgtopng')
            {
                $response = LibOfficeTools::SvgToPng($hash,$action);
                return $response;
            }
            elseif ($hash && $action == 'svgtojpg')
            {
                $response = LibOfficeTools::SvgToJpg($hash,$action);
                return $response;
            }
            elseif ($hash && $action == 'webptopng')
            {
                $response = LibOfficeTools::WebpToPng($hash,$action);
                return $response;
            }
            elseif ($hash && $action == 'webptojpg')
            {
                $response = LibOfficeTools::WebpToJpeg($hash,$action);
                return $response;
            }
            elseif ($hash && $action == 'webptogif')
            {
                $response = LibOfficeTools::WebpToGif($hash,$action);
                return $response;
            }
            elseif ($hash && $action == 'webptotiff')
            {
                $response = LibOfficeTools::WebpToTiff($hash,$action);
                return $response;
            }
            elseif ($hash && $action == 'pngtowebp')
            {
                $response = LibOfficeTools::PngToWebp($hash,$action);
                return $response;
            }
            elseif ($hash && $action == 'jpgtowebp')
            {
                $response = LibOfficeTools::JpegToWebp($hash,$action);
                return $response;
            }
            elseif ($hash && $action == 'giftowebp')
            {
                $response = LibOfficeTools::GifToWebp($hash,$action);
                return $response;
            }
            elseif ($hash && $action == 'tifftowebp')
            {
                $response = LibOfficeTools::TiffToWebp($hash,$action);
                return $response;
            }
            elseif ($hash && $action == 'svgtowebp')
            {
                $response = LibOfficeTools::SvgToWebp($hash,$action);
                return $response;
            }
            elseif ($hash && $action == 'pdftopng')
            {
                $response = LibOfficeTools::PdfToPng($hash, $action);
                return $response;
            }
            elseif ($hash && $action == 'pdftojpg')
            {
                $response = LibOfficeTools::PdfToJpeg($hash, $action);
                return $response;
            }
            elseif ($hash && $action == 'pdftowebp')
            {
                $response = LibOfficeTools::PdfToWebp($hash, $action);
                return $response;
            }
            elseif ($hash && $action == 'mergepdf')
            {
                $response = LibOfficeTools::MergePdf($hash, $action);
                return $response;
            }
            elseif ($hash && $action == 'splitall')
            {
                $response = LibOfficeTools::SplitAll($hash, $action);
                return $response;
            }
            elseif ($hash && $action == 'splitrangemerge')
            {
                $response = LibOfficeTools::SplitRangesMerged($hash, $action);
                return $response;
            }
            elseif ($hash && $action == 'splitrangemultifile')
            {
                $response = LibOfficeTools::SplitPdfMultiFiles($hash, $action);
                return $response;
            }
            elseif ($hash && $action == 'compresspdf')
            {
                $response = LibOfficeTools::CompressPdf($hash, $action);
                return $response;
            }
            elseif ($hash && $action == 'compressimage')
            {
                $response = LibOfficeTools::CompressImg($hash, $action);
                return $response;
            }
        }
        else{
            return response()->json(['code'=>401,
                'status'=>'Authorization required',
                'message'=>'Lacks valid authentication for the target resources']);
        }

    }
}
