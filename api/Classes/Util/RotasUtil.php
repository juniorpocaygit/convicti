<?php

namespace Util;

//Classe para processar as rotas utilizadas na api
class RotasUtil
{
    public static function getRotas()
    {
        $urls = self::getUrls();
        $request = [];
        $request['rota'] = strtoupper($urls[1]);
        $request['recurso'] = $urls[2] ?? null;
        $request['id'] = intval($urls[3]) ?? null;
        $request['metodo'] = $_SERVER['REQUEST_METHOD'];
        return $request;
    }
   
    public static function getUrls()
    {
        $uri = str_replace('/'.DIR_PROJETO, '', $_SERVER['REQUEST_URI']);
        return explode('/', trim($uri,'/'));
    }

}