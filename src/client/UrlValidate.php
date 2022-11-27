<?php

namespace muyomu\dpara\client;

use muyomu\database\base\Document;
use muyomu\http\Request;
use muyomu\http\Response;

interface UrlValidate
{
    public function key_exits(Request $request,Response $response,array $static_routes_table,array $request_routs_table,array $dbClient,array &$keyCollector,array &$dataCollector): Document |null;
}