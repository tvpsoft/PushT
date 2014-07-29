<?php
/**
 * User: mberberoglu
 * Date: 29/07/14
 * Time: 12:18
 */

namespace PushT\Bundle\MainBundle\Model;


class Helper
{
    private static $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

    public static function randomString($length = 10)
    {
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= self::$characters[rand(0, strlen(self::$characters) - 1)];
        }
        return $randomString;
    }

    public static function makePostRequest($url, $secret, $token, $data)
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => $url,
            CURLOPT_USERAGENT => 'PushT Service',
            CURLOPT_POST => 1,
            CURLOPT_POSTFIELDS => $data,
            CURLOPT_HTTPHEADER  => array(
                'Trivia-Secret: '.$secret,
                'Trivia-Token: '.$token
            ),
        ));
        $resp = json_decode(curl_exec($curl), 1);
        curl_close($curl);
        return $resp;
    }

    public static function makeGetRequest($url, $secret, $token)
    {
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => $url,
            CURLOPT_USERAGENT => 'PushT Service',
            CURLOPT_HTTPHEADER  => array(
                'Trivia-Secret: '.$secret,
                'Trivia-Token: '.$token
            ),
        ));
        $resp = json_decode(curl_exec($curl), 1);
        curl_close($curl);
        return $resp;
    }

    public static function slugify($str, $replace = array(), $delimiter='-')
    {
        if (!empty($replace)) {
            $str = str_replace((array)$replace, ' ', $str);
        }

        $clean = iconv('UTF-8', 'ASCII//TRANSLIT', $str);
        $clean = preg_replace("/[^a-zA-Z0-9\/_|+ -]/", '', $clean);
        $clean = strtolower(trim($clean, '-'));
        $clean = preg_replace("/[\/_|+ -]+/", $delimiter, $clean);

        return $clean;
    }

    public static function saveImage($base64, $path)
    {
        if (file_exists($path.'orig.jpg')) {
            unlink($path.'orig.jpg');
        }
        $base64 = str_replace('data:image/png;base64,', '', $base64);
        $base64 = str_replace('data:image/jpg;base64,', '', $base64);
        $base64 = str_replace('data:image/jpeg;base64,', '', $base64);

        $img = str_replace(' ', '+', $base64);
        $data = base64_decode($img);
        $success = file_put_contents($path.'orig.jpg', $data);

        $image = new \Imagick($path.'orig.jpg');
        $image->cropThumbnailImage(400, 400);
        $image->writeImage($path.'400.jpg');
        $image->cropThumbnailImage(200, 200);
        $image->writeImage($path.'200.jpg');
        $image->cropThumbnailImage(100, 100);
        $image->writeImage($path.'100.jpg');
    }
}