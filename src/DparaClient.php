<?php

namespace muyomu\dpara;

use muyomu\database\DbClient;
use muyomu\dpara\client\Dpara;
use muyomu\dpara\exception\UrlNotMatch;
use muyomu\dpara\utility\DparaHelper;
use muyomu\dpara\utility\ResolveUtility;
use muyomu\http\Request;
use muyomu\http\Response;
use muyomu\log4p\Log4p;

class DparaClient implements Dpara
{

    private DparaHelper $dparaHelper;

    private ResolveUtility $resolveUtility;

    public function __construct()
    {
        $this->dparaHelper = new DparaHelper();
        $this->resolveUtility = new ResolveUtility();
    }


    /**
     * @param Request $request
     * @param Response $response
     * @param DbClient $dbClient
     * @return void
     * @throws UrlNotMatch
     */
    public function dpara(Request $request,Response $response, DbClient $dbClient): void
    {

        /*
         * 静态路由转换
         */
        $static_routes_table = $this->resolveUtility->routeResolver($dbClient->database);

        /*
         * 静态路由查询
         */
        $request_uri = $request->getURL();

        /*
         * 动态路由转换
         */
        $kk = $this->resolveUtility->requestResolver($request_uri);

        //数据收集器
        $dataCollector = array();

        //键值收集器
        $keyCollector = array();

        $result = $this->resolveUtility->checkIntersect(array_keys($static_routes_table),array_keys($kk));
        if (empty($result)){
            throw new UrlNotMatch();
        }else{
            //查找路由
            $document = $this->dparaHelper->key_exits($static_routes_table,$kk,$result[0],$dbClient->database,$keyCollector,$dataCollector);

            /*
             * 将数据保存到request中的rule中
             */
            $document->getData()->setPathpara($dataCollector);
            $document->getData()->setPathkey($keyCollector);
            $request->getDbClient()->insert("rule",$document);
        }
    }
}