<?php
function get_testid_url():int|false
{
    $uri = $_SERVER['REQUEST_URI'];
    if (!isset($uri)){
        return false;
    }
    $path = parse_url($uri,PHP_URL_PATH);
    if (preg_match('#^/api/tests/\d+(($)|(/.*$))#',$path)){
        $elems = explode('/',$uri);
        return (int)$elems[3];
    } else{
        return false;
    }

}
