<?php
    try {
        require_once 'conection.php';

        //R::fancyDebug( TRUE );

        $action = $_REQUEST['action'];
        switch($action){
            case 1: getAll(); break;
        }

        } catch (Throwable $e) {
            $responseData= array(
                 "status" => "Error interno",
                 "data"=> $e->getLine().":".$e->getMessage()
            );
            echo "error >>".$e->getLine().":".$e->getMessage();
        }


    function getAll(){
        $movimientos = R::findAll('stockmaterialmedico');
		 foreach ($movimientos as $movimiento) {
			$medicamento = R::findOne('materialmedico','id=?',[$movimiento->materialmedico]);
			$movimiento->medicamento=$medicamento->nombre;    				
		 }
		
        echo json_encode(array_values($movimientos));
    }
?>