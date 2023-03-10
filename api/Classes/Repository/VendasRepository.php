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

    function getDistance($latitude1, $longitude1, $latitude2, $longitude2) {
        $earth_radius = 6371;
        $dLat = deg2rad($latitude2 - $latitude1);
        $dLon = deg2rad($longitude2 - $longitude1);

        $a = sin($dLat/2) * sin($dLat/2) + cos(deg2rad($latitude1)) * cos(deg2rad($latitude2)) * sin($dLon/2) * sin($dLon/2);
        $c = 2 * asin(sqrt($a));
        $d = $earth_radius * $c;

        return $d;
    }

    //Seleciona todas as unidades
    public function selTodasUnidades(){
        $consultaCoordenadas = 'SELECT * FROM unidade'; 
        $this->MySQL->getDb()->beginTransaction();
        $stmt = $this->MySQL->getDb()->prepare($consultaCoordenadas);
        $stmt->execute();
        $output = $stmt->fetchAll($this->MySQL->getDb()::FETCH_ASSOC);
        $this->MySQL->getDb()->commit();
        return $output;
    }

    public function insertVenda($cliente, $produto, $valor, $vendedor, $lat, $lon)
    {
        //Seleciona todas as unidades para fazer a aferição da distância entre elas e a coordenada da venda
        $output = $this->selTodasUnidades();

        //Verifica a distância entre uma venda e todas unidades e retorna a unidade mais próxima
        $count = count($output);
        $distancia = [];
        for ($i=0; $i < $count ; $i++) { 
           $distancia[$output[$i]['unidade']] = $this->getDistance($output[$i]['lat'], $output[$i]['lon'], $lat, $lon);
        }    
        $distMin = min($distancia);

        //Especifica qual a unidade mais próxima da venda
        $unidade = array_search($distMin, $distancia);

        //Seleciona a unidade do vendedor e caso a distancia seja mais próxima d eoutra unidade cria a venda em roaming
        $consultaCoordenadas = 'SELECT uni.unidade FROM usuarios as usu
        JOIN unidade as uni ON uni.id = usu.unidade WHERE usu.id = :vendedor'; 
        $this->MySQL->getDb()->beginTransaction();
        $stmt = $this->MySQL->getDb()->prepare($consultaCoordenadas);
        $stmt->bindParam(':vendedor', $vendedor);
        $stmt->execute();
        $outputVendedor = $stmt->fetchAll($this->MySQL->getDb()::FETCH_ASSOC);
        $this->MySQL->getDb()->commit();
        
        //Compara a unidade do vendedor e a unidade mais próxima da venda, caso sejam diferentes cria a venda em roaming
        if ($unidade != $outputVendedor) {
            $roaming = "S";
        } else {
            $roaming = "N";
        }

        //Busca no banco de dados o id da unidade mais próxima
        $consultaIdUnidade = 'SELECT id FROM unidade WHERE unidade =:unidade'; 
        $this->MySQL->getDb()->beginTransaction();
        $stmt = $this->MySQL->getDb()->prepare($consultaIdUnidade);
        $stmt->bindParam(':unidade', $unidade);
        $stmt->execute();
        $outputUnidade = $stmt->fetchAll($this->MySQL->getDb()::FETCH_ASSOC);
        $this->MySQL->getDb()->commit();

        $data = date('Y-m-d H:i:s', time());

        //Insere a venda no banco de dados
        $consultaInsert = 'INSERT INTO '. self::TABELA .' (data, cliente, produto, valor, vendedor, lat, lon, unid_prox, roaming) VALUES (:data, :cliente, :produto, :valor, :vendedor, :lat, :lon, :unid_prox, :roaming)';
        $this->MySQL->getDb()->beginTransaction();
        $stmt = $this->MySQL->getDb()->prepare($consultaInsert);
        $stmt->bindParam(':data', $data);
        $stmt->bindParam(':cliente', $cliente);
        $stmt->bindParam(':produto', $produto);
        $stmt->bindParam(':valor', $valor);
        $stmt->bindParam(':vendedor', $vendedor);
        $stmt->bindParam(':lat', $lat);
        $stmt->bindParam(':lon', $lon);
        $stmt->bindParam(':unid_prox', $outputUnidade[0]['id']);
        $stmt->bindParam(':roaming', $roaming);
        $stmt->execute();

        return $stmt->rowCount();
    }
   
    //Traz os detalhes de cada venda pelo ID da venda
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

    public function querySomaVendas($id)
    {
        $consultaCoordenadas = 'SELECT uni.id, uni.unidade, uni.lat, uni.lon,
        sum(ven.valor) as total_vendas FROM unidade as uni
        JOIN usuarios as usu ON usu.unidade = uni.id
        JOIN vendas as ven ON ven.vendedor = usu.id
        WHERE uni.id = :id AND ven.valor != 0'; 
        $this->MySQL->getDb()->beginTransaction();
        $stmt = $this->MySQL->getDb()->prepare($consultaCoordenadas);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $outputSoma = $stmt->fetchAll($this->MySQL->getDb()::FETCH_ASSOC);
        $this->MySQL->getDb()->commit();
        return $outputSoma;
    }
    
    //Soma todas as vendas por unnidade
    public function somaVendas()
    {
        $outputUni = $this->selTodasUnidades();
 
        $todasUnidades = [];
        for ($i=0; $i < count($outputUni) ; $i++) { 
            $todasUnidades[$i] = $this->querySomaVendas($outputUni[$i]['id']); 
        }
        return $todasUnidades;
    }

    //Verificar o cargo do usuário pelo ID
    public function verificaCargo($id){
        $consultaCargo = 'SELECT * FROM usuarios WHERE id = :id';
        $this->MySQL->getDb()->beginTransaction();
        $stmt = $this->MySQL->getDb()->prepare($consultaCargo);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $output = $stmt->fetch($this->MySQL->getDb()::FETCH_ASSOC);
        $this->MySQL->getDb()->commit();
        return $output;
    }

    //Seleciona as vendas pelo cargo do usuário
    public function listaPorCargo($id)
    {
        //Verifica o cargo do usuário através do id
        $output = $this->verificaCargo($id);

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

       $output = $this->buscaVendas($varWhere);
       return $output;
    }

    public function buscaVendas($varWhere){
        $consultaPorCargo = 'SELECT  DATE_FORMAT(`data`, "%d/%m/%Y às %H:%i") AS `data`,
        cliente, produto, valor, nome as vendedor, uniuser.unidade, regiao.regiao, uniprox.unidade as unid_prox, roaming, ven.id FROM '. self::TABELA .' as ven 
        JOIN usuarios as user ON ven.vendedor = user.id
        JOIN unidade as uniuser ON user.unidade = uniuser.id 
        JOIN unidade as uniprox ON ven.unid_prox = uniprox.id 
        JOIN regiao ON uniuser.regiao = regiao.id '. $varWhere .'
        ORDER BY data DESC'; 
        $this->MySQL->getDb()->beginTransaction();
        $stmt = $this->MySQL->getDb()->prepare($consultaPorCargo);
        $stmt->execute();
        $output =  $stmt->fetchAll($this->MySQL->getDb()::FETCH_ASSOC);
        $this->MySQL->getDb()->commit();  
        return $output ;
    }

    // Filtros --------------------------------------------------------------------------
    public function filtrarRelatorio($id, $dtInicio, $dtFim, $vendedor, $unidade, $regiao)
    {
        //Verifica o cargo do usuário através do id
        $output = $this->verificaCargo($id);

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

        $output = $this->buscaVendas($varWhere);
        return $output;
    }

    public function getMySQL()
    {
        return $this->MySQL;
    }
}