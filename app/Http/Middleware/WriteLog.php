<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;


class WriteLog
{
     /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        try {
            // Log the incoming request and its data
           Log::info("IN COMING", [
               'url' => $request->fullUrl(),
               'resquest_params' => $request->all()
           ]);
           $params = $request->all();
           $endPoint = $request->url();
           $endPoint = explode("/", $endPoint);
           $endPoint = end($endPoint);
           $response = $next($request);
           $this->writeLog($endPoint, $params, $response->getData());

       } catch (\Exception $e) {
       }

        // return $next($request);
    }


    
    private function writeLog($endPointName, $paramsApi, $response)
    {
        $fileSizeMax = 1024 * 1024;
        $folder = storage_path() . "/logs/";

        $file = $folder . "log.txt";

        $fileFormatSufix = Date("YmdHis");
        $this->renameFile($file, $file, $fileSizeMax, $fileFormatSufix);
        $date = date("Y-m-d H:i:s");
        $separator = "*************************************************";
        # Opening the writing mode
        $fileopen = (fopen($file, 'a'));
        # Writing the "Begin of File" in the text file
        fwrite($fileopen, " \r\n");
        fwrite($fileopen, " \r\n");
        fwrite($fileopen, "Date : " . $date . " \r\n");
        fwrite($fileopen, $separator . "\r\n");
        fwrite($fileopen, "/" . $endPointName . " \r\n");
        fwrite($fileopen, "Params: " . json_encode($paramsApi) . " \r\n");
        fwrite($fileopen, "Response: " . json_encode($response) . " \r\n");
        fwrite($fileopen, $separator . "\r\n");
        fclose($fileopen);
        return 1;
    }


    private function renameFile($fileName, $newFileName, $fileSize, $fileFormatSufix)
    {
        if (file_exists($fileName) && (filesize($fileName) > $fileSize)) {
            $ext = pathinfo($fileName, PATHINFO_EXTENSION);
            if (!rename($fileName, $newFileName . $fileFormatSufix . "." . $ext)) {
                //Report errors in the logs file
                # Opening the writing mode
                $fileopen = (fopen(storage_path() . "\logs\\" . "ManageLogs.txt", 'a'));
                # Writing the "Begin of File" in the text file
                // fwrite($fileopen,"Begin of File");
                fwrite($fileopen, "error occurred when creating log file - " . $fileFormatSufix . "\r\n");
                # Close the file
                fclose($fileopen);
                //				chmod($fileName, 0777);
            } else {
                $fileopen = fopen($fileName, "w") or die("Unable to open file!");
                //fclose($fileopen);
            }
        }
    }
}
