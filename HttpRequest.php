<?php
class HttpRequest {
    
    public static $curl;
    
    public static function get($url, $headers = array(), $return_header = false) {
        return self::request($url, [], 'GET', $headers, $return_header);
    }
    
    public static function post($url, $data, $headers = array(), $return_header = false) {
        return self::request($url, $data, 'POST', $headers, $return_header);
    }
    
    public static function request($url, $data=array(), $method="GET", $headers = array(), $return_header = false) {
        $curl = self::getCurl();
        curl_setopt($curl, CURLOPT_URL, $url);
        if ($data) {
            if (is_array($data)) {
                $data = http_build_query($data);
            }
        }
        if ($method == 'POST'){
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        } else {
            //curl为多次请求公用实例，清除之前POST请求的设置项
            curl_setopt($curl, CURLOPT_POST, 0);
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "GET");
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        }
        
        if (!empty($headers)) {
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        }
        
        curl_setopt($curl, CURLOPT_TIMEOUT, 60);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false); // 信任任何证书
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false); // 检查证书中是否设置域名
        curl_setopt($curl, CURLOPT_HEADER, $return_header);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $result = curl_exec($curl);
        $errno = curl_errno($curl);
//         $error = curl_error($curl);
        // 			$hd = fopen("/var/www/logs/1.txt","a");
        // 		if( strpos($url, 'grant_type=authorization_code') !== false && strpos($url, "wx3c4ece6e355a0414") !== false){
        // 			fwrite($hd, "url:".$url."\n");
        // 			fwrite($hd, 'res:' . json_encode($result));
        // 			fwrite($hd, "_curlErrno:".$curlErrno."\n");
        // 			fwrite($hd, "_curlErrnor:".$curlErrnor."\n----------------------------------------------\n");
        // 		}
        if ($errno > 0) {
            return '';
        }
        
        if ($return_header) {
            $header_size = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
            $header = substr($result, 0, $header_size);
            $body = substr($result, $header_size);
            return array('header' => $header, 'body' => $body);
        }
        return $result;
    }
    
    public static function getCurl() {
        if (!isset(self::$curl) || !self::$curl) {
            self::$curl = curl_init();
        }
        return self::$curl;
    }
    
    public static function getCookies($header = '') {
        if (empty($header)) {
            return '';
        }
        $cookies = array();
        $lines = explode(PHP_EOL, $header);
        foreach ($lines as $line) {
            if (strpos($line, 'Set-Cookie') !== 0) {
                continue;
            }
            list($_set_cookie, $cookie_value) = explode(':', $line);
            list($cookie_value, $cookie_rest) = explode(';', $cookie_value, 2);
            $cookie_value = trim($cookie_value, ' ;');
            $cookies[] = $cookie_value;
        }
        return implode(';', $cookies);
    }
    
    public static function getRawCookieValue($header, $key, $default = '') {
        if (empty($header)) {
            return '';
        }
        $lines = explode(PHP_EOL, $header);
        foreach ($lines as $line) {
            if (strpos($line, 'Set-Cookie') !== 0) {
                continue;
            }
            list($cookie_value, $cookie_rest) = @explode(';', $line, 2);
            if (strpos($cookie_value, $key) === false) {
                continue;
            }
            list($cookie_key, $cookie_value) = @explode('=', $cookie_value, 2);
            return $cookie_value;
        }
        return $default;
    }
}