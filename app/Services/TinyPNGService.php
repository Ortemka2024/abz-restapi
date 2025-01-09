<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;

class TinyPNGService
{
    public function __construct()
    {
        \Tinify\setKey(env('TINYPNG_API_KEY'));
    }

    public function processImage(UploadedFile $file, string $format)
    {
        try {
            $source = \Tinify\fromBuffer($file->getContent());

            $resized = $source->resize([
                'method' => 'cover',
                'width' => 70,
                'height' => 70,
            ]);

            $outputBuffer = $resized->toBuffer($format);

            return $outputBuffer;
        } catch (\Tinify\AccountException $e) {
            throw new \Exception('Account error: '.$e->getMessage());
        } catch (\Tinify\ClientException $e) {
            throw new \Exception('Client error: '.$e->getMessage());
        } catch (\Tinify\ServerException $e) {
            throw new \Exception('Server error: '.$e->getMessage());
        } catch (\Tinify\ConnectionException $e) {
            throw new \Exception('Connection error: '.$e->getMessage());
        } catch (\Exception $e) {
            throw new \Exception('Unknown error: '.$e->getMessage());
        }
    }
}
