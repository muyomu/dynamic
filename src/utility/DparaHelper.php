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

    public function key_exits(Request $request,Response $response,array $static_routes_table,array $request_routs_table,array $dbClient,array &$keyCollector,array &$dataCollector): Document
    {
        $keys = array_keys($request_routs_table);
        foreach ($keys as  $key){
            if (array_key_exists($key,$static_routes_table)){
                $dynamic_routes = $static_routes_table[$key];
                $paraLength = count($request_routs_table[$key]);
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
            }
        }
        $this->log4p->muix_log_warn(__CLASS__,__METHOD__,__LINE__,"Url Not Match");
        $response->doExceptionResponse(new UrlNotMatch(),400);
    }
}