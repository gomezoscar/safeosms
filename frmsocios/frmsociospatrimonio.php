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
$xHP		= new cHPage("TR.Relacion Patrimonial", HP_FORM);
$xQL		= new MQL();
$xLi		= new cSQLListas();
$xF			= new cFecha();

//$jxc = new TinyAjax();
//$jxc ->exportFunction('datos_del_pago', array('idsolicitud', 'idparcialidad'), "#iddatos_pago");
//$jxc ->process();

$fecha			= parametro("idfecha-0", false, MQL_DATE); $fecha = parametro("idfechaactual", $fecha, MQL_DATE);
$persona		= parametro("persona", DEFAULT_SOCIO, MQL_INT); $persona = parametro("socio", $persona, MQL_INT); $persona = parametro("idsocio", $persona, MQL_INT);
$credito		= parametro("credito", DEFAULT_CREDITO, MQL_INT); $credito = parametro("idsolicitud", $credito, MQL_INT); $credito = parametro("solicitud", $credito, MQL_INT);
$cuenta			= parametro("cuenta", DEFAULT_CUENTA_CORRIENTE, MQL_INT); $cuenta = parametro("idcuenta", $cuenta, MQL_INT);
$jscallback		= parametro("callback"); $tiny = parametro("tiny"); $form = parametro("form"); $action = parametro("action", SYS_NINGUNO);


$monto			= parametro("idmonto", 0, MQL_FLOAT);
$descripcion	= parametro("iddescripcion");
$documento		= parametro("iddocumento");
$tipo			= parametro("idtipodepatrimonio", 0, MQL_INT);
$estado			= parametro("idestadodepatrimonio", 0, MQL_INT);
$observaciones	= parametro("idobservaciones");
$idvencimiento	= parametro("idvencimiento", false, MQL_DATE);
$unidades		= parametro("unidades", 1, MQL_INT);
$medida			= parametro("medida", 0, MQL_INT);



$xHP->init();

$xFRM			= new cHForm("frmpatrimonio", "frmsociospatrimonio.php?persona=$persona&tiny=$tiny");
$xFRM->setTitle($xHP->getTitle());
$xSel			= new cHSelect();

$xSoc			= new cSocio($persona);
if($xSoc->init() == true AND $persona > DEFAULT_SOCIO){

	if($monto >  0 and $persona > 0 and $tipo > 0 and $estado > 0 and $documento != "") {
		$xSocPa	= new cPersonasPatrimonio();
		$rs		= $xSocPa->add($xSoc->getClaveDePersona(), $tipo, $descripcion, $monto, $documento, $observaciones, $unidades, $medida, $estado,$idvencimiento);
		$xFRM->setResultado($rs);		

		if(MODO_DEBUG == true){ $xFRM->addLog($xSoc->getMessages()); }
	}
}
$xFRM->addHElem($xSel->getListaDeTiposDePatrimonioPersonal()->get(true));
$xFRM->addHElem($xSel->getListaDeEstadosDePatrimonioPersonal()->get(true));
$xFRM->OText("iddocumento", "", "TR.Documento de_referencia");
$xFRM->OText("iddescripcion", "", "TR.Descripcion");
$xFRM->OFechaLarga("idvencimiento", $fecha, "TR.Fecha de Vencimiento");

$xFRM->ONumero("unidades", 0, "TR.TAMANNIO");
$xMeds	= $xSel->getListaDeCatalogoGenerico("catalogo_unidades", "medida");
$xFRM->addHElem( $xMeds->get("TR.MEDIDA", true));

$xFRM->addValor($monto);


$xFRM->addObservaciones();
$xFRM->addGuardar();
//agregar lista
$xT		= new cTabla($xLi->getListaDePatrimonioPorPersona($persona));
$xT->addEliminar();
$xFRM->addHTML( $xT->Show() );


$xFRM->addJsReloadForm("frmsociospanel");

echo $xFRM->get();

//$jxc ->drawJavaScript(false, true);
$xHP->fin();
?>