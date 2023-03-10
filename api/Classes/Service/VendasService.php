<?php

namespace Service;

use Repository\VendasRepository;
use Util\ConstantesGenericasUtil;

class VendasService
{
    public const TABELA = 'vendas';
    public const RECURSOS_GET = ['relatorio'];
    public const RECURSOS_DELETE = [''];
    public const RECURSOS_POST = ['inserir', 'detalhes','filtrar'];
    public const RECURSOS_PUT = [''];

    private array $dados;
    private array $dadosCorpoRequest = [];

    private object $VendasRepository;

    public function __construct($dados = [])
    {
        $this->dados = $dados;
        $this->VendasRepository = new VendasRepository();
    }

    public function validarGet()
    {
        $retorno = null;
        $recurso = $this->dados['recurso'];
        if (in_array($recurso, self::RECURSOS_GET)) {
           $retorno = $this->dados['id'] > 0 ? $this->listaPorCargo() : $this->relatorio();
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
      
    private function inserir()
    {
       [$cliente, $produto, $valor, $vendedor, $lat, $lon] = 
        [
            $this->dadosCorpoRequest['cliente'], 
            $this->dadosCorpoRequest['produto'], 
            $this->dadosCorpoRequest['valor'], 
            $this->dadosCorpoRequest['vendedor'], 
            $this->dadosCorpoRequest['lat'], 
            $this->dadosCorpoRequest['lon']
        ];
        if ($cliente && $produto && $valor && $vendedor) {
            try {
                $this->VendasRepository->insertVenda($cliente, $produto, $valor, $vendedor, $lat, $lon); 
                $idInserido = $this->VendasRepository->getMySQL()->getDb()->lastInsertId();
                $this->VendasRepository->getMySQL()->getDb()->commit();
                return ['id_inserido' => $idInserido];
            } catch (\Throwable $th) {
                throw new \InvalidArgumentException(ConstantesGenericasUtil::MSG_ERRO_VENDA);
            }
        }
        throw new \InvalidArgumentException(ConstantesGenericasUtil::MSG_ERRO_VENDA_GERAL);
    }

    private function login()
    {
       [$filter] = 
       [
            $this->dadosCorpoRequest['email'],
            $this->dadosCorpoRequest['senha']
       ];
       if ($email && $senha) {
            if ($this->VendasRepository->loginUser($email, $senha)) {
                $retorno = $this->VendasRepository->getMySQL()->getDb();
                $this->VendasRepository->getMySQL()->getDb()->commit();
                return $this->VendasRepository->loginUser($email, $senha);
            }   
            throw new \InvalidArgumentException(ConstantesGenericasUtil::MSG_ERRO_LOGIN_INVALIDO);
        }
        throw new \InvalidArgumentException(ConstantesGenericasUtil::MSG_ERRO_LOGIN_SENHA_OBRIGATORIO);
    }

    private function listaPorCargo()
    {
        if ($this->VendasRepository->listaPorCargo($this->dados['id'])) {
            $retorno = $this->VendasRepository->getMySQL()->getDb();
            return $this->VendasRepository->listaPorCargo($this->dados['id']);
        }   
        throw new \InvalidArgumentException(ConstantesGenericasUtil::MSG_ERRO_VENDA_CARGO);
    }
   
    private function filtrar()
    {
       [$dtInicio, $dtFim, $vendedor, $unidade, $regiao] = 
       [
            $this->dadosCorpoRequest['dtInicio'] ? $this->dadosCorpoRequest['dtInicio'] : 0,
            $this->dadosCorpoRequest['dtFim'] ? $this->dadosCorpoRequest['dtFim'] : 0,
            $this->dadosCorpoRequest['vendedor'] ? $this->dadosCorpoRequest['vendedor'] : 0,
            $this->dadosCorpoRequest['unidade'] ? $this->dadosCorpoRequest['unidade'] : 0,
            $this->dadosCorpoRequest['regiao'] ? $this->dadosCorpoRequest['regiao'] : 0
       ];
            if ($this->VendasRepository->filtrarRelatorio($this->dados['id'], $dtInicio, $dtFim, $vendedor, $unidade, $regiao)) {
                $retorno = $this->VendasRepository->getMySQL()->getDb();
                return $this->VendasRepository->filtrarRelatorio($this->dados['id'], $dtInicio, $dtFim, $vendedor, $unidade, $regiao);
            }   
            throw new \InvalidArgumentException(ConstantesGenericasUtil::MSG_ERRO_VENDA_FILTRO);
    }
    
    private function detalhes()
    {
       [$venda] = [$this->dadosCorpoRequest['venda']];
       if ($venda) {
            if ($this->VendasRepository->detalhes($venda)) {
                return $this->VendasRepository->detalhes($venda);
            }   
            throw new \InvalidArgumentException(ConstantesGenericasUtil::MSG_ERRO_VENDA_DETALHE);
        }
        throw new \InvalidArgumentException(ConstantesGenericasUtil::MSG_ERRO_VENDA_DETALHE);
    }
  
    private function relatorio()
   {
       return $this->VendasRepository->somaVendas();
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