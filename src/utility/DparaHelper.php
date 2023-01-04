<?php

namespace muyomu\dpara\utility;

use muyomu\database\base\Document;
use muyomu\dpara\client\UrlValidate;
use muyomu\dpara\exception\UrlNotMatch;
use muyomu\http\Request;
use muyomu\http\Response;

class DparaHelper implements UrlValidate
{
    /**
     * @throws UrlNotMatch
     */
    public function key_exits(Request $request, Response $response, array $static_routes_table, array $request_routs_table, array $dbClient, array &$keyCollector, array &$dataCollector): Document |null
    {
        $keys = array_keys($request_routs_table);

        $point = null;

        foreach ($keys as  $key){
            if (array_key_exists($key,$static_routes_table)){

                $dynamic_routes = $static_routes_table[$key];

                $paraLength = count($request_routs_table[$key]);

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
                        goto here;
                    }
                }

	            here:
                if (is_null($point)){
                    return null;
                }

                //保存route到request
                return new Document($dbClient[$point]->getData());
            }
        }
        return null;
    }
}