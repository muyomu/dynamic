<?php

namespace muyomu\dpara\utility;

use muyomu\database\base\Document;
use muyomu\dpara\client\UrlValidate;
use muyomu\dpara\exception\UrlNotMatch;
use muyomu\http\Request;
use muyomu\http\Response;
use muyomu\log4p\Log4p;

class DparaHelper implements UrlValidate
{
    private Log4p $log4p;

    public function __construct()
    {
        $this->log4p = new Log4p();
    }


    /**
     * @throws UrlNotMatch
     */
    public function key_exits(string $key, array $database,Request $request,Response $response,array $dbClient,array &$keyCollector,array &$dataCollector): Document |null
    {
        if (array_key_exists($key,$database)){
            //获取到所有的动态路由
            $dynamic_routes = $database[$key];
            $paraLength = count($dataCollector);
            //通过数据长度匹配动态路由
            $point = null;
            foreach ($dynamic_routes as $route){
                $match = array();
                preg_match_all("/\/:([a-zA-Z]+)/m",$route,$match);
                if (empty($match[1])){
                    $length = 0;
                }else{
                    $length = count($match[1]);
                }
                if ($length == $paraLength){
                    foreach ($match[1] as $value){
                        $keyCollector[] = $value;
                    }
                    $point = $route;
                    break;
                }
            }

            if (is_null($point)){
                $this->log4p->muix_log_warn(__CLASS__,__METHOD__,__LINE__,"Url Not Match");
                $response->doExceptionResponse(new UrlNotMatch(),400);
            }

            //保存route到request
            return new Document($dbClient[$point]->getData());
        }else{
            return null;
        }
    }

    public function get_next_url(string $url, array &$dataCollector,Response $response): string
    {
        $items = explode("/",$url);
        array_shift($items);
        $value = array_pop($items);
        if ($value == ""){
            $this->log4p->muix_log_warn(__CLASS__,__METHOD__,__LINE__,"Url Not Match");
            $response->doExceptionResponse(new UrlNotMatch(),400);
        }
        $dataCollector[] = $value;
        return $this->get_combined_url($items);
    }

    public function get_combined_url(array $items): string
    {
        return "/".implode("/",$items);
    }
}