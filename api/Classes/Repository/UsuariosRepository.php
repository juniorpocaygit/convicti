<?php

namespace Repository;

use DB\MySQL;

class UsuariosRepository
{

    private object $MySQL;
    public const TABELA = 'usuarios';


    /* UsuáriosRepository Construtor*/

    public function __construct()
    {
        $this->MySQL = new MySQL();
    }

    public function insertUser($cargo, $unidade, $regiao, $nome, $email, $senha)
    {
        $hash = password_hash($senha, PASSWORD_DEFAULT);

        $consultaInsert = 'INSERT INTO '. self::TABELA .' (cargo, unidade, regiao, nome, email, senha) VALUES (:cargo, :unidade, :regiao, :nome, :email, :senha)';
        $this->MySQL->getDb()->beginTransaction();
        $stmt = $this->MySQL->getDb()->prepare($consultaInsert);
        $stmt->bindParam(':cargo', $cargo);
        $stmt->bindParam(':unidade', $unidade);
        $stmt->bindParam(':regiao', $regiao);
        $stmt->bindParam(':nome', $nome);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':senha', $hash);
        $stmt->execute();
           
        return $stmt->rowCount();
    }

    public function loginUser($email, $senha)
    {
        $consultaUpdate = 'SELECT * FROM '. self::TABELA .' WHERE email = :email';
        $this->MySQL->getDb()->beginTransaction();
        $stmt = $this->MySQL->getDb()->prepare($consultaUpdate);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $output =  $stmt->fetch($this->MySQL->getDb()::FETCH_ASSOC);
        if (!password_verify($senha, $output['senha'] )) {
            return;        
        } else {
            return "ID: ". $output['id'];
        }
    }

    public function updateUser($id, $dados)
    {
        $consultaUpdate = 'UPDATE '. self::TABELA .' SET nome = :nome, whatsapp = :whatsapp WHERE id = :id';
        $this->MySQL->getDb()->beginTransaction();
        $stmt = $this->MySQL->getDb()->prepare($consultaUpdate);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':nome', $dados['nome']);
        $stmt->bindParam(':whatsapp', $dados['whatsapp']);
        $stmt->execute();
        $this->MySQL->getDb()->commit();

        $consultaSelect = 'SELECT * FROM '. self::TABELA .' WHERE id = :id';
        $this->MySQL->getDb()->beginTransaction();
        $stmt = $this->MySQL->getDb()->prepare($consultaSelect);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $output['data'] = $stmt->fetch();
     
        session_start();
        $_SESSION["id"] = $output["data"]["id"];
        $_SESSION["email"] = $output["data"]["email"];
        $_SESSION["usuario"] = $output["data"]["nome"]; 
        $_SESSION["whats"] = $output["data"]["whatsapp"]; 
        return $output;
    }
       
    public function getMySQL()
    {
        return $this->MySQL;
    }
}