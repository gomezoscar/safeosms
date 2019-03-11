<?php
/**
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
$xHP		= new cHPage("TR.Llamadas", HP_FORM);
$xQL		= new MQL();
$xLi		= new cSQLListas();
$xF			= new cFecha();
$jxc 	= new TinyAjax();
function jsaSetEstadoLlamada($id, $estado, $notas){
	$msg	= "";
	$xCall	= new cLlamadas($id);
	$xNot	= new cHNotif();
	$xCall->init();
	$rs		= $xCall->setEstado($estado, $notas);
	return ($rs == false) ? "ERROR" : "OK";
}
$jxc ->exportFunction('jsaSetEstadoLlamada', array('idllamada', "idestadodellamada", "idobservaciones"), "#idmsgs");
//$jxc ->exportFunction('datos_del_pago', array('idsolicitud', 'idparcialidad'), "#iddatos_pago");
$jxc ->process();

$clave		= parametro("id", 0, MQL_INT); $clave		= parametro("clave", $clave, MQL_INT);
$fecha		= parametro("idfecha-0", false, MQL_DATE); $fecha = parametro("idfechaactual", $fecha, MQL_DATE); 
$persona	= parametro("persona", DEFAULT_SOCIO, MQL_INT); $persona = parametro("socio", $persona, MQL_INT); $persona = parametro("idsocio", $persona, MQL_INT);
$credito	= parametro("credito", DEFAULT_CREDITO, MQL_INT); $credito = parametro("idsolicitud", $credito, MQL_INT); $credito = parametro("solicitud", $credito, MQL_INT);
$cuenta		= parametro("cuenta", DEFAULT_CUENTA_CORRIENTE, MQL_INT); $cuenta = parametro("idcuenta", $cuenta, MQL_INT);
$jscallback	= parametro("callback"); $tiny = parametro("tiny"); $form = parametro("form"); $action = parametro("action", SYS_NINGUNO);


$estado		= parametro("estado", "", MQL_RAW);

$xHP->init();
$xLLam		= new cLlamadas($clave);
$xLLam->init();

$xFRM		= new cHForm("frmllamadascambiarestado", "./");
$xFRM->setTitle($xHP->getTitle());
$xSel		= new cHSelect();

if($estado == ""){ $estado = $xLLam->getEstado(); }
$xFRM->addHElem( $xSel->getListaDeEstadoDeLlamada("", $estado)->get(true) );
$xFRM->addObservaciones("",$xLLam->getObservaciones());
$xFRM->addGuardar("jsaSetEstadoLlamada()");
$xFRM->OHidden("idllamada", $clave);
$xFRM->addAviso("");

echo $xFRM->get();
$jxc ->drawJavaScript(false, true);
$xHP->fin();
?>