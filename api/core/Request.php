<?php

namespace LogicLeap\StockManagement\core;

class Request
{
    /**
     * Returns the base path of the requested url.
     * ex:- "/contact?id=1" => "/contact"
     * @return string base path
     */
    public function getPath(): string
    {
        $path = $_SERVER['REQUEST_URI'] ?? '/';
        $position = strpos($path, '?');

        if ($position === false) {
            return $path;
        }
        return substr($path, 0, $position);
    }

    /**
     * Returns the parsed parameters as an array of [key => value] scheme.
     * @return array body of the request.
     */
    public function getBodyParams(): array
    {
        $body = [];
        if ($this->getMethod() === 'get') {
            foreach ($_GET as $key => $value) {
                $body[$key] = filter_input(INPUT_GET, $key, FILTER_SANITIZE_SPECIAL_CHARS);
            }
        }
        if ($this->getMethod() === 'post') {
            $body = json_decode(file_get_contents('php://input'), true);
        }
        if ($body === null)
            $body = [];
        return $body;
    }

    public static function getRequestIp(): string
    {
        $ip = "";
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } //whether ip is from the proxy
        elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } //whether ip is from the remote address
        else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        return $ip;
    }

    /**
     * Returns the method of the request whether GET or POST.
     * @return string method of the request
     */
    public function getMethod(): string
    {
        return strtolower($_SERVER['REQUEST_METHOD']);
    }

    /**
     * Returns true if called method is GET, otherwise returns false
     * @return boolean true|false
     */
    public function isGet(): bool
    {
        return $this->getMethod() === 'get';
    }

    /**
     * Returns true if called method is POST, otherwise returns false
     * @return boolean true|false
     */
    public function isPost(): bool
    {
        return $this->getMethod() === 'post';
    }


}