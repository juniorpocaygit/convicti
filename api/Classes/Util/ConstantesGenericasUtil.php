<?php

namespace Util;

abstract class ConstantesGenericasUtil
{
    /* REQUESTS */
    public const TIPO_REQUEST = ['GET', 'POST', 'DELETE', 'PUT'];
    public const TIPO_GET = ['USUARIOS','VENDAS'];
    public const TIPO_POST = ['USUARIOS','VENDAS'];
    public const TIPO_DELETE = ['USUARIOS'];
    public const TIPO_PUT = ['USUARIOS'];

    /* ERROS */
    public const MSG_ERRO_TIPO_ROTA = 'Rota não permitida!';
    public const MSG_ERRO_RECURSO_INEXISTENTE = 'Recurso inexistente!';
    public const MSG_ERRO_GENERICO = 'Algum erro ocorreu na requisição!';
    public const MSG_ERRO_SEM_RETORNO = 'Nenhum registro encontrado!';
    public const MSG_ERRO_NAO_AFETADO = 'Atualização não realizada, nenhum registro encontrado!';
    public const MSG_ERRO_TOKEN_VAZIO = 'É necessário informar um Token!';
    public const MSG_ERRO_TOKEN_NAO_AUTORIZADO = 'Token não autorizado!';
    public const MSG_ERR0_JSON_VAZIO = 'O Corpo da requisiçãoo não pode ser vazio!';

    /* SUCESSO */
    public const MSG_DELETADO_SUCESSO = 'Registro deletado com Sucesso!';
    public const MSG_ATUALIZADO_SUCESSO = 'Registro atualizado com Sucesso!';
    public const MSG_SENHA_ATUALIZADA_SUCESSO = 'Senha atualizada com Sucesso!';
    public const MSG_LOGIN_SUCESSO = 'Login realizado com Sucesso!';

    /* RECURSO USUARIOS */
    public const MSG_ERRO_ID_OBRIGATORIO = 'ID é obrigatório!';
    public const MSG_ERRO_LOGIN_INVALIDO = 'Usuário ou senha incorretos.';
    public const MSG_ERRO_LOGIN_SENHA_OBRIGATORIO = 'Login e Senha são obrigatórios!';
    public const MSG_ERRO_LOGIN_JA_EXISTENTE = 'Esse usuário já existe!';
    public const MSG_ERRO_LOGIN_GERAL = 'Houve um problema ao fazer o cadastro deste usuário.';

    /* RECURSO VENDAS*/
    public const MSG_ERRO_VENDA = 'Sua venda não pode ser inserida no sistema.';
    public const MSG_ERRO_VENDA_DETALHE = 'Venda não encontrada.';
    public const MSG_ERRO_VENDA_GERAL = 'Ocorreu um erro ao inserir sua venda no sistema, tente novamente mais tarde.';
    public const MSG_ERRO_VENDA_CARGO = 'Esse usuário não possui nenhuma venda realizada';

    /* RETORNO JSON */
    const TIPO_SUCESSO = 'sucesso';
    const TIPO_ERRO = 'erro';

    /* OUTRAS */
    public const SIM = 'S';
    public const TIPO = 'tipo';
    public const RESPOSTA = 'resposta';
}

