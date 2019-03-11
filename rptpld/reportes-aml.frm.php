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
$xHP		= new cHPage("TR.REPORTES GENERALES PLD", HP_FORM);

//$jxc = new TinyAjax();
//$jxc ->exportFunction('datos_del_pago', array('idsolicitud', 'idparcialidad'), "#iddatos_pago");
//$jxc ->process();

$persona	= parametro("persona", DEFAULT_SOCIO, MQL_INT); $persona = parametro("socio", $persona, MQL_INT); $persona = parametro("idsocio", $persona, MQL_INT);
$credito	= parametro("credito", DEFAULT_CREDITO, MQL_INT); $credito = parametro("idsolicitud", $credito, MQL_INT); $credito = parametro("solicitud", $credito, MQL_INT);
$cuenta		= parametro("cuenta", DEFAULT_CUENTA_CORRIENTE, MQL_INT); $cuenta = parametro("idcuenta", $cuenta, MQL_INT);
$jscallback	= parametro("callback"); $tiny = parametro("tiny"); $form = parametro("form"); $action = parametro("action", SYS_NINGUNO);

$xHP->init();

$xFRM		= new cHForm("frmrptsamls", "./");
$xBtn		= new cHButton();		
$xTxt		= new cHText();
$xDate		= new cHDate();
$xSel		= new cHSelect();
$xHNot		= new cHNotif();
$xSelT		= $xSel->getListaDeRiesgosAML("idtiporiesgo");
$xSelT->addEspOption(SYS_TODAS);
$xSelT->setOptionSelect(SYS_TODAS);

$xSelN		= $xSel->getListaDeTipoDeRiesgoEnAML("idnaturaleza");
$xSelN->addEspOption(SYS_TODAS);
$xSelN->setOptionSelect(SYS_TODAS);


$xRPT		= new cPanelDeReportes(iDE_AML, "aml");

$xRPT->setTitle($xHP->getTitle());
$xRPT->OFRM()->addHElem( $xSelN->get(true) );
$xRPT->addjsVars("idnaturaleza", "clasificacion");

$xRPT->OFRM()->addHElem( $xSelT->get(true) );
$xRPT->addjsVars("idtiporiesgo", "tipoderiesgo");

$xRPT->addCheckBox("TR.Extenso", "ext");
$xRPT->addCheckBox("TR.DICTAMEN", "condictamen");
$xRPT->addCheckBox("TR.MENSAJE DEL SISTEMA", "consistema");
echo $xRPT->get();
echo $xRPT->getJs(true);
//$jxc ->drawJavaScript(false, true);

$xHP->fin();
?>