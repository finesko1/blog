<?php


class Request
{
    private static $request;

    private function __construct(){}
    private function __clone(){}
    public function __wakeup(){}

    public static function sendRequest(String $url): String
    {
        try {
            self::$request = curl_init($url);
            curl_setopt_array(self::$request, [
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_RETURNTRANSFER => true
            ]);

            return curl_exec(self::$request);
        }
        catch (\Exception $e)
        {
            return $e;
        }
    }

    public function __destruct()
    {
        if (is_resource($this->request) || $this->request instanceof \CurlHandle)
        {
            curl_close($this->request);
        }
    }
}