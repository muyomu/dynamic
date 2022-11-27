<?php

namespace muyomu\dpara;

use muyomu\database\DbClient;
use muyomu\dpara\client\Dpara;
use muyomu\dpara\exception\UrlNotMatch;
use muyomu\dpara\utility\DparaHelper;
use muyomu\http\Request;
use muyomu\http\Response;
use muyomu\log4p\Log4p;

class DparaClient implements Dpara
{

    private DparaHelper $dparaHelper;

    private Log4p $log4p;

    public function __construct()
    {
        $this->dparaHelper = new DparaHelper();
        $this->log4p = new Log4p();
    }


    /**
     * @param Request $request
     * @param Response $response
     * @param DbClient $dbClient
     * @return void
     */
    public function dpara(Request $request,Response $response, DbClient $dbClient): void
    {

        /*
         * 静态路由转换
         */
        $static_routes_table = $this->routeResolver($dbClient->database);

        /*
         * 静态路由查询
         */
        $request_uri = $request->getURL();

        $kk = $this->requestResolver($request_uri);

        //数据收集器
        $dataCollector = array();
        //键值收集器
        $keyCollector = array();

        $document = $this->dparaHelper->key_exits($request,$response,$static_routes_table,$kk,$dbClient->database,$keyCollector,$dataCollector);

        if (is_null($document)){
            $this->log4p->muix_log_warn(__CLASS__,__METHOD__,__LINE__,"Url Not Match");
            $response->doExceptionResponse(new UrlNotMatch(),400);
        }

        /*
         * 将数据保存到request中的rule中
         */
        $document->getData()->setPathpara($dataCollector);
        $document->getData()->setPathkey($keyCollector);
        $request->getDbClient()->insert("rule",$document);
    }

    /*
     * 静态路由解析
     */
    private function routeResolver(array $database):array{
        $routes = array_keys($database);
        $routes_str = implode("|-|",$routes);
        $match = array();
        preg_match_all("/(\/[a-zA-Z]+)+/im",$routes_str,$match);

        //获取到所有的静态路由除开根目录
        $static_routes = $match[0];
        $static_routes = array_unique($static_routes);

        //获取到所有的静态路由对应的动态路由
        $list = array();
        foreach ($static_routes as $route){
            $ck = str_replace("/","\/",$route);
            preg_match_all("/{$ck}(\/:[a-zA-Z]*)*/im",$routes_str,$match);
            $list[$route] = $match[0];
        }
        return $list;
    }

    private function requestResolver(string $uri):array{
        $one = explode("/",$uri);
        array_shift($one);
        $mdl = $one;
        $collector = array();
        $index = count($one);
        $uk = '/';
        foreach ( $one as $item){
            if ($uk  == "/"){
                $uk .= $item;
            }else{
                $uk .= "/".$item;
            }
            array_shift($mdl);
            $collector[$uk] = $mdl;
        }
        return $collector;
    }
}