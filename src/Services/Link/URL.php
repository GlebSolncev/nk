<?php
/**
 * parser
 * Created by: 5-HT.
 * Date: 15.01.2020 22:30
 */
namespace Src\Services\Link;

class URL
{
    private $url;

    public $status;

    public function __construct(string $url)
    {
        $this->url = $url;
        $this->checkHTTP();
    }


    public function checkHTTP()
    {
        $headers = $this->getHeaders();
        $this->status = substr($headers[0], 9, 3)==200?true:false;
    }

    public function getHeaders()
    {
        return @get_headers($this->url);

    }

    public static function checkURL($url)
    {
        $headers = @get_headers($url);
        return substr($headers[0], 9, 3);
    }

    public function path()
    {
        return parse_url($this->url, PHP_URL_PATH);
    }

    public function host()
    {
        return parse_url($this->url, PHP_URL_HOST);
    }

    public function protocol()
    {
        return parse_url($this->url, PHP_URL_SCHEME);
    }

    public function link()
    {
        return (string) $this->url;
    }

}