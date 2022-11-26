<?php

namespace muyomu\dpara\utility;

use muyomu\database\base\Document;
use muyomu\database\exception\RepeatDefinition;
use muyomu\dpara\client\UrlValidate;
use muyomu\dpara\exception\UrlNotMatch;
use muyomu\http\Request;

class DparaHelper implements UrlValidate
{

    /**
     * @throws UrlNotMatch|RepeatDefinition
     */
    public function key_exits(string $key, array $database,Request $request,array $dbClient): array
    {
        //数据收集器
        $dataCollector = array();
        //键值收集器
        $keyCollector = array();

        here:
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

            /*
             * 判断point
             */
            if (is_null($point)){
                throw new UrlNotMatch();
            }

            //保存route到request
            $request_db = $request->getDbClient();
            $document = new Document($dbClient[$point]->getData());
            $request_db->insert("rule",$document);
            return array("key"=>$keyCollector,"value"=>array_reverse($dataCollector));
        }else{
            $next = $this->get_next_url($key,$dataCollector);
            $key = $next['key'];
            $dataCollector = $next['dataCollector'];
            goto here;
        }
    }

    public function get_next_url(string $url, array $dataCollector): array
    {
        $items = explode("/",$url);
        array_shift($items);
        $value = array_pop($items);
        $dataCollector[] = $value;
        $key = $this->get_combined_url($items);
        return array("key"=>$key,"dataCollector"=>$dataCollector);
    }

    public function get_combined_url(array $items): string
    {
        return "/".implode("/",$items);
    }
}