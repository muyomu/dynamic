<?php

namespace muyomu\dpara\utility;

use muyomu\database\base\Document;
use muyomu\dpara\client\UrlValidate;

class DparaHelper implements UrlValidate
{
    public function key_exits(array $static_routes_table,array $request_routs_table, string $key, array $dbClient, array &$keyCollector, array &$dataCollector): Document |null
    {
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

                $dataCollector = $request_routs_table[$key];

                $point = $route;
                break;
            }
        }
        //保存route到request
        if ($point !== null){
            return new Document($dbClient[$point]->getData());
        }else{
            return null;
        }
    }
}