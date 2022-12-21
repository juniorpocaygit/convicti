<?php

namespace Repository;

use DB\MySQL;

class VendasRepository
{

    private object $MySQL;
    public const TABELA = 'vendas';


    /* VendasRepository Construtor*/

    public function __construct()
    {
        setlocale(LC_TIME, 'pt_BR', 'pt_BR.utf-8', 'pt_BR.utf-8', 'portuguese');
        date_default_timezone_set('America/Sao_Paulo');
        $this->MySQL = new MySQL();
    }

    public function insertVenda($cliente, $produto, $valor, $vendedor, $lat, $lon, $unid_prox, $roaming)
    {
        $data = date('Y-m-d H:i:s', time());
        
        $consultaInsert = 'INSERT INTO '. self::TABELA .'(data, cliente, produto, valor, vendedor, lat, lon, unid_prox, roaming) VALUES (:data, :cliente, :produto, :valor, :vendedor, :lat, :lon, :unid_prox, :roaming)';
        $this->MySQL->getDb()->beginTransaction();
        $stmt = $this->MySQL->getDb()->prepare($consultaInsert);
        $stmt->bindParam(':data', $data);
        $stmt->bindParam(':cliente', $cliente);
        $stmt->bindParam(':produto', $produto);
        $stmt->bindParam(':valor', $valor);
        $stmt->bindParam(':vendedor', $vendedor);
        $stmt->bindParam(':lat', $lat);
        $stmt->bindParam(':lon', $lon);
        $stmt->bindParam(':unid_prox', $unid_prox);
        $stmt->bindParam(':roaming', $roaming);
        $stmt->execute();
           
        return $stmt->rowCount();
    }
   
    public function detalhes($vendas)
    {
        $consultaDetalheVenda = 'SELECT  DATE_FORMAT(`data`, "%d/%m/%Y às %H:%i") AS `data`,
        cliente, produto, valor, nome as vendedor, uniuser.unidade, regiao.regiao, uniprox.unidade as unid_prox, roaming, ven.id FROM '. self::TABELA .' as ven 
        JOIN usuarios as user ON ven.vendedor = user.id
        JOIN unidade as uniuser ON user.unidade = uniuser.id 
        JOIN unidade as uniprox ON ven.unid_prox = uniprox.id 
        JOIN regiao ON uniuser.regiao = regiao.id
        WHERE ven.id = :venda '; 
        $this->MySQL->getDb()->beginTransaction();
        $stmt = $this->MySQL->getDb()->prepare($consultaDetalheVenda);
        $stmt->bindParam(':venda', $vendas);
        $stmt->execute();
        $output = $stmt->fetchAll($this->MySQL->getDb()::FETCH_ASSOC);
        $this->MySQL->getDb()->commit();
        return $output;
    }
    
    public function listaPorCargo($id)
    {
        //Verifica o cargo do usuário através do id
        $consultaCargo = 'SELECT * FROM usuarios WHERE id = :id';
        $this->MySQL->getDb()->beginTransaction();
        $stmt = $this->MySQL->getDb()->prepare($consultaCargo);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $output = $stmt->fetch($this->MySQL->getDb()::FETCH_ASSOC);
        $this->MySQL->getDb()->commit();

        //Seleciona o nível do relatório de acordo com o cargo do usuário
        //Diretor Geral -> Cargo 01
        if ($output['cargo'] == 1) {
            $varWhere = '';
        }
        //Diretor -> Cargo 02
        else if ($output['cargo'] == 2){
            $varWhere = 'WHERE regiao.id ='.$output['regiao'];
        }
        //Gerente -> Cargo 03
        else if ($output['cargo'] == 3){
            $varWhere = 'WHERE uniuser.id ='.$output['unidade'];
        }
        //Vendedor -> Cargo 04
        else if ($output['cargo'] ==4){
            $varWhere = 'WHERE ven.vendedor ='.$id;
        }

        $consultaUpdate = 'SELECT  DATE_FORMAT(`data`, "%d/%m/%Y às %H:%i") AS `data`,
        cliente, produto, valor, nome as vendedor, uniuser.unidade, regiao.regiao, uniprox.unidade as unid_prox, roaming, ven.id FROM '. self::TABELA .' as ven 
        JOIN usuarios as user ON ven.vendedor = user.id
        JOIN unidade as uniuser ON user.unidade = uniuser.id 
        JOIN unidade as uniprox ON ven.unid_prox = uniprox.id 
        JOIN regiao ON uniuser.regiao = regiao.id '. $varWhere .'
        ORDER BY data DESC'; 
        $this->MySQL->getDb()->beginTransaction();
        $stmt = $this->MySQL->getDb()->prepare($consultaUpdate);
        $stmt->execute();
        $output =  $stmt->fetchAll($this->MySQL->getDb()::FETCH_ASSOC);
        $this->MySQL->getDb()->commit();
        
        return $output ;
    }

    public function filtrarRelatorio($id, $dtInicio, $dtFim, $vendedor, $unidade, $regiao)
    {
        //Verifica o cargo do usuário através do id
        $consultaCargo = 'SELECT * FROM usuarios WHERE id = :id';
        $this->MySQL->getDb()->beginTransaction();
        $stmt = $this->MySQL->getDb()->prepare($consultaCargo);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $output = $stmt->fetch($this->MySQL->getDb()::FETCH_ASSOC);
        $this->MySQL->getDb()->commit();

        //Seleciona o nível do relatório de acordo com o cargo do usuário
        //Diretor Geral -> Cargo 01
        if ($output['cargo'] == 1) {
            $varWhere = '';
        }
        //Diretor -> Cargo 02
        else if ($output['cargo'] == 2){
            $varWhere = 'WHERE regiao.id ='. $output['regiao'];
        }
        //Gerente -> Cargo 03
        else if ($output['cargo'] == 3){
            $varWhere = 'WHERE uniuser.id ='. $output['unidade'];
        }
        //Vendedor -> Cargo 04
        else if ($output['cargo'] == 4){
            $varWhere = 'WHERE ven.vendedor ='. $id;
        }

        //Filtra o relatório de acordo com os parâmetros enviados via formulário
        //Filtra pela data inicial e data final do período selecionado
        if ($dtInicio != 0 && $dtFim != 0) {
            $filterWhere = 'DATE_FORMAT(ven.data,"%d/%m/%Y") BETWEEN "'.$dtInicio.'" AND "'. $dtFim.'"';
        }
        //Filtra todas as vendas de um Vendedor
        else if ($vendedor != 0 && $output['cargo'] != 4){
           $filterWhere = 'ven.vendedor ='.$vendedor;
        }
       //Filtra todas as vendas de uma Unidade
        else if ($unidade != 0 && $output['cargo'] != 4 && $output['cargo'] != 3 ){
            $filterWhere = 'uniuser.id ='. $unidade;
        }
        //Filtra todas as vendas de uma Região
        else if ($regiao != 0 && $output['cargo'] != 4 && $output['cargo'] != 3 && $output['cargo'] != 2 ){
            $filterWhere = 'regiao.id ='.$regiao;
        }
        else {
           return;
        }
        
        $varWhere = $varWhere .' AND '. $filterWhere;

        $consultaUpdate = 'SELECT  DATE_FORMAT(`data`, "%d/%m/%Y às %H:%i") AS `data`,
        cliente, produto, valor, nome as vendedor, uniuser.unidade, regiao.regiao, uniprox.unidade as unid_prox, roaming, ven.id FROM '. self::TABELA .' as ven 
        JOIN usuarios as user ON ven.vendedor = user.id
        JOIN unidade as uniuser ON user.unidade = uniuser.id 
        JOIN unidade as uniprox ON ven.unid_prox = uniprox.id 
        JOIN regiao ON uniuser.regiao = regiao.id '. $varWhere .' 
        ORDER BY data DESC'; 
        $this->MySQL->getDb()->beginTransaction();
        $stmt = $this->MySQL->getDb()->prepare($consultaUpdate);
        $stmt->execute();
        $output =  $stmt->fetchAll($this->MySQL->getDb()::FETCH_ASSOC);
        $this->MySQL->getDb()->commit();
        
        return $output ;
    }

    public function getMySQL()
    {
        return $this->MySQL;
    }
}