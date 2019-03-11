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
$xHP		= new cHPage("", HP_FORM);
$xQL		= new MQL();
$xLi		= new cSQLListas();
$xF			= new cFecha();
$xDic		= new cHDicccionarioDeTablas();
//$jxc 		= new TinyAjax();
//$tab = new TinyAjaxBehavior();
//$tab -> add(TabSetValue::getBehavior("idide", $x));
//return $tab -> getString();
//$jxc ->exportFunction('datos_del_pago', array('idsolicitud', 'idparcialidad'), "#iddatos_pago");
//$jxc ->process();
$clave			= parametro("id", 0, MQL_INT); $clave		= parametro("clave", $clave, MQL_INT);  
$fecha			= parametro("idfecha-0", false, MQL_DATE); $fecha = parametro("idfechaactual", $fecha, MQL_DATE);  $fecha = parametro("idfecha", $fecha, MQL_DATE);
$persona		= parametro("persona", DEFAULT_SOCIO, MQL_INT); $persona = parametro("socio", $persona, MQL_INT); $persona = parametro("idsocio", $persona, MQL_INT);
$credito		= parametro("credito", DEFAULT_CREDITO, MQL_INT); $credito = parametro("idsolicitud", $credito, MQL_INT); $credito = parametro("solicitud", $credito, MQL_INT);
$cuenta			= parametro("cuenta", DEFAULT_CUENTA_CORRIENTE, MQL_INT); $cuenta = parametro("idcuenta", $cuenta, MQL_INT);
$jscallback		= parametro("callback"); $tiny = parametro("tiny"); $form = parametro("form"); $action = parametro("action", SYS_NINGUNO);$action	= strtolower($action);
$monto			= parametro("monto",0, MQL_FLOAT); $monto	= parametro("idmonto",$monto, MQL_FLOAT); 
$recibo			= parametro("recibo", 0, MQL_INT); $recibo	= parametro("idrecibo", $recibo, MQL_INT);
$empresa		= parametro("empresa", 0, MQL_INT); $empresa	= parametro("idempresa", $empresa, MQL_INT); $empresa	= parametro("iddependencia", $empresa, MQL_INT); $empresa	= parametro("dependencia", $empresa, MQL_INT);
$grupo			= parametro("idgrupo", 0, MQL_INT); $grupo	= parametro("grupo", $grupo, MQL_INT);
$ctabancaria 	= parametro("idcodigodecuenta", 0, MQL_INT); $ctabancaria = parametro("cuentabancaria", $ctabancaria, MQL_INT);
$observaciones	= parametro("idobservaciones");

$xHP->init();

/* ===========		FORMULARIO EDICION 		============*/
$xTabla		= new cGeneral_estados();
$xTabla->setData( $xTabla->query()->initByID($clave));
$xFRM	= new cHForm("frmestados", "entidadesfederales.frm.php?action=$action");

$xFRM->setTitle($xHP->getTitle());
$xSel		= new cHSelect();

if($clave <= 0){
	$xTabla->idgeneral_estados('NULL');
	$nuevo	= $xQL->getDataValue("SELECT MAX(`clave_numerica`) +1 AS `nuevo` FROM `general_estados`", "nuevo");
	$xTabla->clave_numerica($nuevo);
}

$xFRM->OHidden("idgeneral_estados", $xTabla->idgeneral_estados()->v());

$xFRM->ONumero("clave_numerica", $xTabla->clave_numerica()->v(), "TR.NUMERO");
$xFRM->OText_13("clave_alfanumerica", $xTabla->clave_alfanumerica()->v(), "TR.CLAVE ALFANUMERICA");
$xFRM->OText_13("clave_en_sic", $xTabla->clave_en_sic()->v(), "TR.CLAVE SIC");

$xFRM->OText("nombre", $xTabla->nombre()->v(), "TR.NOMBRE");


$xFRM->ONumero("codigo_postal_inicial", $xTabla->codigo_postal_inicial()->v(), "TR.CODIGO_POSTAL INICIAL");
$xFRM->ONumero("codigo_postal_final", $xTabla->codigo_postal_final()->v(), "TR.CODIGO_POSTAL FINAL");

$xFRM->OSiNo("TR.ESTATUSACTIVO","operacion_habilitada", $xTabla->operacion_habilitada()->v() );


//$xFRM->addCRUD($xTabla->get(), true);
$xFRM->addCRUDSave($xTabla->get(), $clave, true);


echo $xFRM->get();

//$jxc ->drawJavaScript(false, true);
$xHP->fin();
?>