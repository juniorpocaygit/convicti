<?php

namespace Util;

use Util\ConstantesGenericasUtil;

//Classe para processar a requisição e a resposta via JSON
class JsonUtil
{
    public function processarArrayParaRetornar($retorno)
    {
        $dados = [];
        $dados[ConstantesGenericasUtil::TIPO] = ConstantesGenericasUtil::TIPO_ERRO;
          
        if ((is_array($retorno) && count($retorno) > 0) || strlen($retorno) > 10) {
           $dados[ConstantesGenericasUtil::TIPO] = ConstantesGenericasUtil::TIPO_SUCESSO;
           $dados[ConstantesGenericasUtil::RESPOSTA] = $retorno; 
        }
        $this->retornarJson($dados);
    }

    private function retornarJson($json)
    {
        header('Content-Type: application/json');
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Access-Control-Allow-Methods: GET,POST,PUT,DELETE');
        echo json_encode($json);
        exit;
    }

    /*Retorna array|mixed */
    public static function tratarCorpoRequisicaoJson()
    {
        try {
         
            $postJson = json_decode(file_get_contents('php://input'), true);

        } catch (JsonExceptionAlias $exception) {
           throw new InvalidArgumentException(ConstantesGenericasUtil::MSG_ERRO_JSON_VAZIO);
        }

        if (is_array($postJson) && count($postJson) > 0) {
            return $postJson;
        }

    }
    

}