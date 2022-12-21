<?php

if($output['cargo'] == 1){
            $consultaUpdate = 'SELECT  DATE_FORMAT(`data`, "%d/%m/%Y às %H:%i") AS `data`,
            cliente, produto, valor, nome as vendedor, uniuser.unidade, regiao.regiao, uniprox.unidade as unid_prox, roaming, ven.id FROM '. self::TABELA .' as ven 
            JOIN usuarios as user ON ven.vendedor = user.id
            JOIN unidade as uniuser ON user.unidade = uniuser.id 
            JOIN unidade as uniprox ON ven.unid_prox = uniprox.id 
            JOIN regiao ON uniuser.regiao = regiao.id
            ORDER BY data DESC'; 
            $this->MySQL->getDb()->beginTransaction();
            $stmt = $this->MySQL->getDb()->prepare($consultaUpdate);
            $stmt->execute();
            $output =  $stmt->fetchAll($this->MySQL->getDb()::FETCH_ASSOC);
            $this->MySQL->getDb()->commit();
        }
         //Lista todas as vendas de uma Região pelo Diretor
        else if($output['cargo'] == 2){
            $varWhere = 'WHERE regiao.id = :id';

            $consultaUpdate = 'SELECT  DATE_FORMAT(`data`, "%d/%m/%Y às %H:%i") AS `data`,
            cliente, produto, valor, nome as vendedor, uniuser.unidade, regiao.regiao, uniprox.unidade as unid_prox, roaming, ven.id FROM '. self::TABELA .' as ven 
            JOIN usuarios as user ON ven.vendedor = user.id
            JOIN unidade as uniuser ON user.unidade = uniuser.id 
            JOIN unidade as uniprox ON ven.unid_prox = uniprox.id 
            JOIN regiao ON uniuser.regiao = regiao.id '. $varWhere .'
            ORDER BY data DESC'; 
            $this->MySQL->getDb()->beginTransaction();
            $stmt = $this->MySQL->getDb()->prepare($consultaUpdate);
            $stmt->bindParam(':id', $output['regiao']);
            $stmt->execute();
            $output =  $stmt->fetchAll($this->MySQL->getDb()::FETCH_ASSOC);
            $this->MySQL->getDb()->commit();
        }
        //Lista todas as vendas de uma unidade pelo Gerente 
        else if($output['cargo'] == 3){
            $consultaUpdate = 'SELECT  DATE_FORMAT(`data`, "%d/%m/%Y às %H:%i") AS `data`,
            cliente, produto, valor, nome as vendedor, uniuser.unidade, regiao.regiao, uniprox.unidade as unid_prox, roaming, ven.id FROM '. self::TABELA .' as ven 
            JOIN usuarios as user ON ven.vendedor = user.id
            JOIN unidade as uniuser ON user.unidade = uniuser.id 
            JOIN unidade as uniprox ON ven.unid_prox = uniprox.id 
            JOIN regiao ON uniuser.regiao = regiao.id
            WHERE uniuser.id = :id ORDER BY data DESC '; 
            $this->MySQL->getDb()->beginTransaction();
            $stmt = $this->MySQL->getDb()->prepare($consultaUpdate);
            $stmt->bindParam(':id', $output['unidade']);
            $stmt->execute();
            $output =  $stmt->fetchAll($this->MySQL->getDb()::FETCH_ASSOC);
            $this->MySQL->getDb()->commit();
        }
        //Lista todas as vendas pelo Vendedor
        else if($output['cargo'] == 4){
            $consultaUpdate = 'SELECT  DATE_FORMAT(`data`, "%d/%m/%Y às %H:%i") AS `data`,
            cliente, produto, valor, nome as vendedor, uniuser.unidade, regiao.regiao, uniprox.unidade as unid_prox, roaming, ven.id FROM '. self::TABELA .' as ven 
            JOIN usuarios as user ON ven.vendedor = user.id
            JOIN unidade as uniuser ON user.unidade = uniuser.id 
            JOIN unidade as uniprox ON ven.unid_prox = uniprox.id 
            JOIN regiao ON uniuser.regiao = regiao.id
            WHERE ven.vendedor = :id ORDER BY data DESC'; 
            $this->MySQL->getDb()->beginTransaction();
            $stmt = $this->MySQL->getDb()->prepare($consultaUpdate);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            $output =  $stmt->fetchAll($this->MySQL->getDb()::FETCH_ASSOC);
            $this->MySQL->getDb()->commit();
        }