<?php
    header('Access-Control-Allow-Origin: *');
    header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method");
    header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
    header("Allow: GET, POST, OPTIONS, PUT, DELETE");
    
    require_once 'conection.php';

    $action = $_REQUEST['action'];
    
    switch($action){
        case 1: getMedicamentos(); break;
        case 2: getHistoricoMaterialmedico(); break;
        case 3: putMaterialmedico(); break;
        case 4: getMaterialmedico(); break;
        case 5: addStockMaterialmedico(); break;
        case 6: removeStockMaterialmedico(); break;
    }



    function getMedicamentos(){
        $materialmedico = R::find('materialmedico','oculto = ? AND tipo != ? ORDER BY nombre ASC',[0,'m']);
		foreach ($materialmedico as $mm) {
			$stock = R::getRow( 'SELECT sum(cantidad) as stock FROM stockmaterialmedico WHERE materialmedico = ?',[ $mm->id ] );
			$preciomayor = R::getRow( 'SELECT max(precio) as preciomayor FROM stockmaterialmedico WHERE materialmedico = ? ',[ $mm->id ]);
			if($stock['stock']==null){
				$stock['stock'] =0;
			}
			$mm->stock = $stock['stock'];	

			if($preciomayor['preciomayor']==null){
				$preciomayor['stock'] =0.00;
			}
			$mm->precio = $preciomayor['preciomayor'];				
		}
		//$materialmedico = R::
        //echo json_encode(array_values($materialmedico));
        echo json_encode(R::exportAll($materialmedico, TRUE));
    }

    function getHistoricoMaterialmedico(){
        $historicomaterialmedico = R::find('stockmaterialmedico','materialmedico = ?',[$_REQUEST["idmedicamento"]]);
		foreach ($historicomaterialmedico as $hmm) {			
			//$mm->precio = $preciomayor['preciomayor'];				
		}
		
        echo json_encode(array_values($historicomaterialmedico));
    }

   function getMaterialmedico(){
        $materialmedico = R::findOne('materialmedico','id=?',[$_REQUEST['id']]);
		$stock = R::getRow( 'SELECT sum(cantidad) as stock FROM stockmaterialmedico WHERE materialmedico = ? ',[ $materialmedico->id ]);
		$materialmedico->stock = $stock['stock'];
        echo json_encode($materialmedico);
   }

    function putMaterialmedico(){
        try{
        $materialmedico = R::findOne('materialmedico','id=?',[$_REQUEST['idmaterial']]);
        if($materialmedico==null){
            $materialmedico = R::dispense('materialmedico');
        }
        $columns = R::getAll('SHOW COLUMNS FROM materialmedico');
        
         foreach ($columns as $column) {
           if($column["Field"]!='id' && $column["Field"]!='status'){
            if($_REQUEST[$column["Field"]]!=null){
                $materialmedico[$column["Field"]]=$_REQUEST[$column["Field"]];
            }
           }
         }
        $id = R::store($materialmedico);
        }catch(Exception $e){
            echo 'Excepción capturada: ',  $e->getMessage(), "\n";
        }
        echo json_encode($materialmedico);
   }
   
	function addStockMaterialmedico(){
        try{
		$stockmaterialmedico = R::dispense('stockmaterialmedico');
        $columns = R::getAll('SHOW COLUMNS FROM stockmaterialmedico');
         foreach ($columns as $column) {
           if($column["Field"]!='id' && $column["Field"]!='status'){
            if($_REQUEST[$column["Field"]]!=null){
                if($column["Field"]=='cantidad'){
					$materialmedico = R::findOne('materialmedico','id=?',[$_REQUEST['materialmedico']]);
					$stockmaterialmedico[$column["Field"]]=$_REQUEST[$column["Field"]]*$materialmedico->unidades;
				}else{
					$stockmaterialmedico[$column["Field"]]=$_REQUEST[$column["Field"]];
				}				
            }
           }
         }
        $id = R::store($stockmaterialmedico);
            }catch(Exception $e){
                echo 'Excepción capturada: ',  $e->getMessage(), "\n";
            }
        echo $stockmaterialmedico;
   }
   function removeStockMaterialmedico(){}
?>