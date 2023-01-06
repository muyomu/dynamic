<?php

namespace muyomu\dpara\utility;

class ResolveUtility
{
    /*
     * 静态路由解析
     */
    public function routeResolver(array $database):array{
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

    /*
     * 动态路由解析
     */
    public function requestResolver(string $uri):array{
        $one = explode("/",$uri);
        array_shift($one);
        $mdl = $one;
        $collector = array();
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

    public function checkIntersect(array $one,array $two):array{

        return array_intersect($one,$two);
    }
}