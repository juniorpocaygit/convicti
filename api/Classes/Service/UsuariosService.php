<?php

namespace Service;

use Repository\UsuariosRepository;
use Util\ConstantesGenericasUtil;

class UsuariosService
{
    public const TABELA = 'usuarios';
    public const RECURSOS_GET = ['listar'];
    public const RECURSOS_DELETE = [''];
    public const RECURSOS_POST = ['cadastrar','login'];
    public const RECURSOS_PUT = [''];

    private array $dados;
    private array $dadosCorpoRequest = [];

    private object $UsuariosRepository;

    public function __construct($dados = [])
    {
        $this->dados = $dados;
        $this->UsuariosRepository = new UsuariosRepository();
    }

    public function validarGet()
    {
        $retorno = null;
        $recurso = $this->dados['recurso'];
        if (in_array($recurso, self::RECURSOS_GET)) {
           $retorno = $this->dados['id'] > 0 ? $this->getOneByKey() : $this->$recurso();
        } else {
            throw new \InvalidArgumentException(ConstantesGenericasUtil::MSG_ERRO_RECURSO_INEXISTENTE);
        }
        $this->validarRetornoRequest($retorno);
        return $retorno;
    }

    public function validarPost()
    {
        $retorno = null;
        $recurso = $this->dados['recurso'];
        if (in_array($recurso, self::RECURSOS_POST)) {
            $retorno = $this->$recurso();
        } else {
            throw new \InvalidArgumentException(ConstantesGenericasUtil::MSG_ERRO_RECURSO_INEXISTENTE);
        }
        $this->validarRetornoRequest($retorno);
        return $retorno;
    }

    public function validarDelete()
    {
        $retorno = null;
        $recurso = $this->dados['recurso'];
        if (in_array($recurso, self::RECURSOS_DELETE)) {
            $retorno = $this->validarIdObrigatorio($recurso);
        } else {
            throw new \InvalidArgumentException(ConstantesGenericasUtil::MSG_ERRO_RECURSO_INEXISTENTE);
        }
        $this->validarRetornoRequest($retorno);
        return $retorno;
    }

    public function validarPut()
    {
        $retorno = null;
        $recurso = $this->dados['recurso'];
        if (in_array($recurso, self::RECURSOS_PUT)) {
            $retorno = $this->validarIdObrigatorio($recurso);
        } else {
            throw new \InvalidArgumentException(ConstantesGenericasUtil::MSG_ERRO_RECURSO_INEXISTENTE);
        }  
        $this->validarRetornoRequest($retorno);
        return $retorno;
        
    }
  
    public function setDadosCorpoRequest($dadosRequest)
    {
        $this->dadosCorpoRequest = $dadosRequest;
    }
   
    private function cadastrar()
    {
       [$cargo, $unidade, $regiao, $nome, $email, $senha] = 
        [
            $this->dadosCorpoRequest['cargo'], 
            $this->dadosCorpoRequest['unidade'], 
            $this->dadosCorpoRequest['regiao'], 
            $this->dadosCorpoRequest['nome'], 
            $this->dadosCorpoRequest['email'], 
            $this->dadosCorpoRequest['senha']
        ];
        if ($unidade == "") {
            $unidade = null;
        }
        if ($regiao == "") {
            $regiao = null;
        }
       if ($cargo && $nome && $email && $senha) {
            try {
                $this->UsuariosRepository->insertUser($cargo, $unidade, $regiao, $nome, $email, $senha); 
                $idInserido = $this->UsuariosRepository->getMySQL()->getDb()->lastInsertId();
                $this->UsuariosRepository->getMySQL()->getDb()->commit();
                return ['id_inserido' => $idInserido];
            } catch (\Throwable $th) {
                throw new \InvalidArgumentException(ConstantesGenericasUtil::MSG_ERRO_LOGIN_JA_EXISTENTE);
            }
        }
        throw new \InvalidArgumentException(ConstantesGenericasUtil::MSG_ERRO_LOGIN_GERAL);
    }

    private function login()
    {
       [$email, $senha] = 
       [
            $this->dadosCorpoRequest['email'],
            $this->dadosCorpoRequest['senha']
       ];
       if ($email && $senha) {
            if ($this->UsuariosRepository->loginUser($email, $senha)) {
                $retorno = $this->UsuariosRepository->getMySQL()->getDb();
                $this->UsuariosRepository->getMySQL()->getDb()->commit();
                return $this->UsuariosRepository->loginUser($email, $senha);
            }   
            throw new \InvalidArgumentException(ConstantesGenericasUtil::MSG_ERRO_LOGIN_INVALIDO);
        }
        throw new \InvalidArgumentException(ConstantesGenericasUtil::MSG_ERRO_LOGIN_SENHA_OBRIGATORIO);
    }
      
    private function listar()
    {
        return $this->UsuariosRepository->getMySQL()->getAll(self::TABELA);
    }

    private function validarRetornoRequest($retorno): void
    {
        if ($retorno == null) {
            throw new \InvalidArgumentException(ConstantesGenericasUtil::MSG_ERRO_GENERICO);
        }
    }

    private function validarIdObrigatorio($recurso)
    {
        if ($this->dados['id'] > 0) {
            $retorno = $this->$recurso();
        } else {
            throw new \InvalidArgumentException(ConstantesGenericasUtil::MSG_ERRO_ID_OBRIGATORIO);
        }
        return $retorno;
    }
}