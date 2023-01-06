<?php

namespace muyomu\dpara\client;

use muyomu\database\base\Document;
use muyomu\http\Request;
use muyomu\http\Response;

interface UrlValidate
{
    public function key_exits(array $static_routes_table,array $request_routs_table,string $key,array $dbClient,array &$keyCollector,array &$dataCollector): Document |null;
}