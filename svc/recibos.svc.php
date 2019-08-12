<?php
/**
 * Modulo
 * @author Balam Gonzalez Luis Humberto
 * @version 0.0.01
 * @package
 */
//=====================================================================================================
	include_once("../core/go.login.inc.php");
	include_once("../core/core.error.inc.php");
	include_once("../core/core.html.inc.php");
	include_once("../core/core.init.inc.php");
	include_once("../core/core.db.inc.php");
	$theFile			= __FILE__;
	$permiso			= getSIPAKALPermissions($theFile);
	if($permiso === false){	header ("location:../404.php?i=999");	}
	$_SESSION["current_file"]	= addslashes( $theFile );
//=====================================================================================================
$xHP		= new cHPage("", HP_SERVICE);
//$xQL		= new MQL();
//$xLi		= new cSQLListas();
$xF			= new cFecha();
$xUsr		= new cSystemUser();


$clave			= parametro("id", 0, MQL_INT); $clave		= parametro("clave", $clave, MQL_INT);  
$fecha			= parametro("idfecha-0", false, MQL_DATE); $fecha = parametro("idfechaactual", $fecha, MQL_DATE); $fecha = parametro("idfecha", $fecha, MQL_DATE);

$persona		= parametro("persona", DEFAULT_SOCIO, MQL_INT); $persona = parametro("socio", $persona, MQL_INT); $persona = parametro("idsocio", $persona, MQL_INT);
$credito		= parametro("credito", DEFAULT_CREDITO, MQL_INT); $credito = parametro("idsolicitud", $credito, MQL_INT); $credito = parametro("solicitud", $credito, MQL_INT);
$cuenta			= parametro("cuenta", DEFAULT_CUENTA_CORRIENTE, MQL_INT); $cuenta = parametro("idcuenta", $cuenta, MQL_INT);

$jscallback		= parametro("callback"); $tiny = parametro("tiny"); $form = parametro("form"); $action = parametro("action", SYS_NINGUNO);$action = parametro("cmd", $action);

 
$recibo			= parametro("recibo", 0, MQL_INT); $recibo	= parametro("idrecibo", $recibo, MQL_INT);
$observaciones	= parametro("idobservaciones");
$letra			= parametro("letra", false, MQL_INT);

$action			= strtolower($action);

$xSVC			= new MQLService($action, "");

$rs				= array();
$rs["error"]	= true;
$rs["message"]	= "Sin datos validos";

switch ($action){
	case "fecha":
		$xRec	= new cReciboDeOperacion(false, false, $recibo);
		if($xRec->init() == true){
			
			$xRec->setFecha($fecha, true, true);
			//$xRec->setForceUpdateSaldos(true);
			//$xRec->setFinalizarRecibo(true);
			$rs["error"]	= false;
			$rs["message"] 	= $xRec->getMessages();
		}
		break;
	case "cuadrar":
		$xRec	= new cReciboDeOperacion(false, false, $recibo);
		if($xRec->init() == true){
			
			$xRec->setForceUpdateSaldos(true);
			$xRec->setFinalizarRecibo(true);
			$rs["error"]	= false;
			$rs["message"] 	= $xRec->getMessages();
		}
		break;
	case "listar":
		
		break;
	default:
		$xRec	= new cReciboDeOperacion(false, false, $recibo);
		if($xRec->init() == true){
			
			$rs["recibo"]		= $recibo;
			$rs["descripcion"]	= $xRec->getDescripcion();
			$rs["error"]		= false;
			$rs["message"] 		= $xRec->getMessages();
		}
		break;
}


header('Content-type: application/json');
echo json_encode($rs);
?>