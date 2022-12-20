<?php

namespace Validator;

use Util\ConstantesGenericasUtil;
use Util\JsonUtil;
use Repository\TokensAutorizadosRepository;
use Service\UsuariosService;
use Service\VendasService;

class RequestValidator
{
    private $request;
    private array $dadosRequest = [];
    private object $tokensAutorizadosRepository;
    
    const GET = 'GET';
    const DELETE = 'DELETE';
    const USUARIOS = 'USUARIOS';
    const VENDAS = 'VENDAS';
      
    public function __construct($request)
    {
       $this->request = $request;
       $this->tokensAutorizadosRepository = new TokensAutorizadosRepository();
    }

    public function processarRequest()
    {
     
        $retorno = utf8_encode(ConstantesGenericasUtil::MSG_ERRO_TIPO_ROTA);

        $this->request['metodo'] == 'GET';
        if(in_array($this->request['metodo'], ConstantesGenericasUtil::TIPO_REQUEST, true)){
            $retorno = $this->direcionarRequest();
        }

        return $retorno;

    }

    private function direcionarRequest()
    {
        if ($this->request['metodo'] !== self::GET && $this->request['metodo'] !== self::DELETE){
           $this->dadosRequest = JsonUtil::tratarCorpoRequisicaoJson();
        }

        $headers = getallheaders();
        $this->tokensAutorizadosRepository->validarToken($headers['Authorization']);

        $metodo = $this->request['metodo'];
        return $this->$metodo();
    }

    private function get()
    {
        $retorno = utf8_encode(ConstantesGenericasUtil::MSG_ERRO_TIPO_ROTA);
        if (in_array($this->request['rota'], ConstantesGenericasUtil::TIPO_GET)) {
            switch($this->request['rota']){
                case self::USUARIOS:
                    $UsuariosService = new UsuariosService($this->request);
                    $retorno = $UsuariosService->validarGet();
                    break;
                case self::VENDAS:
                    $VendasService = new VendasService($this->request);
                    $retorno = $VendasService->validarGet();
                    break;
                default:
                    throw new \InvalidArgumentException(ConstantesGenericasUtil::MSG_ERRO_RECURSO_INEXISTENTE);
            }
        }
        return $retorno;
    }
    
    private function delete()
    {
        $retorno = utf8_encode(ConstantesGenericasUtil::MSG_ERRO_TIPO_ROTA);
        if (in_array($this->request['rota'], ConstantesGenericasUtil::TIPO_DELETE)) {
            switch($this->request['rota']){
                case self::USUARIOS:
                    $UsuariosService = new UsuariosService($this->request);
                    $retorno = $UsuariosService->validarDelete();
                    break;
                default:
                    throw new \InvalidArgumentException(ConstantesGenericasUtil::MSG_ERRO_RECURSO_INEXISTENTE);
            }
        }
        return $retorno;
    }

    private function post()
    {
        
        $retorno = utf8_encode(ConstantesGenericasUtil::MSG_ERRO_TIPO_ROTA);
        if (in_array($this->request['rota'], ConstantesGenericasUtil::TIPO_POST)) {
            switch($this->request['rota']){
                case self::USUARIOS:
                    $UsuariosService = new UsuariosService($this->request);
                    $UsuariosService->setDadosCorpoRequest($this->dadosRequest);
                    $retorno = $UsuariosService->validarPost();
                    break;
                case self::VENDAS:
                    $VendasService = new VendasService($this->request);
                    $VendasService->setDadosCorpoRequest($this->dadosRequest);
                    $retorno = $VendasService->validarPost();
                    break;
                default:
                    throw new \InvalidArgumentException(ConstantesGenericasUtil::MSG_ERRO_RECURSO_INEXISTENTE);
            }
        }
        return $retorno;
    }

    private function put()
    {
        $retorno = utf8_encode(ConstantesGenericasUtil::MSG_ERRO_TIPO_ROTA);
        if (in_array($this->request['rota'], ConstantesGenericasUtil::TIPO_PUT)) {
            switch($this->request['rota']){
                case self::USUARIOS:
                    $UsuariosService = new UsuariosService($this->request);
                    $UsuariosService->setDadosCorpoRequest($this->dadosRequest);
                    $retorno = $UsuariosService->validarPut();
                    break;
                default:
                    throw new \InvalidArgumentException(ConstantesGenericasUtil::MSG_ERRO_RECURSO_INEXISTENTE);
            }
        }
        
        return $retorno;
    }
}