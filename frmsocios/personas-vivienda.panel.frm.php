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
$xHP		= new cHPage("TR.PERSONAS VIVIENDA", HP_FORM);
$xQL		= new MQL();
$xLi		= new cSQLListas();
$xF			= new cFecha();
$xDic		= new cHDicccionarioDeTablas();
$jxc 		= new TinyAjax();
function jsaMarkInactive($id){
	$xViv	= new cPersonasVivienda();
	$xViv->setID($id);
	if($xViv->init() == true){ $xViv->setInactivo(); }
	return $xViv->getMessages();
}
function jsaMarkPrincipal($id){
	$xViv	= new cPersonasVivienda();
	$xViv->setID($id);
	if($xViv->init() == true){ $xViv->setPrincipal(); }
	return $xViv->getMessages();
}
function jsaMarkSeConstruye($id){
	$xViv	= new cPersonasVivienda();
	$xViv->setID($id);
	if($xViv->init() == true){ $xViv->setSeConstruye(); }
	return $xViv->getMessages();
}
function jsaEliminarVivienda($id){
	$xViv	= new cPersonasVivienda();
	$xViv->setID($id);
	if($xViv->init() == true){
		$xViv->setEliminar();
	}
	return $xViv->getMessages();
}
$jxc ->exportFunction('jsaMarkInactive', array('id'), "#idaviso");
$jxc ->exportFunction('jsaMarkPrincipal', array('id'), "#idaviso");
$jxc ->exportFunction('jsaMarkSeConstruye', array('id'), "#idaviso");
$jxc ->exportFunction('jsaEliminarVivienda', array('id'), "#idaviso");


$jxc ->process();
$clave		= parametro("id", 0, MQL_INT); $clave		= parametro("clave", $clave, MQL_INT);  
$fecha		= parametro("idfecha-0", false, MQL_DATE); $fecha = parametro("idfechaactual", $fecha, MQL_DATE);  $fecha = parametro("idfecha", $fecha, MQL_DATE);
$persona	= parametro("persona", DEFAULT_SOCIO, MQL_INT); $persona = parametro("socio", $persona, MQL_INT); $persona = parametro("idsocio", $persona, MQL_INT);
$credito	= parametro("credito", DEFAULT_CREDITO, MQL_INT); $credito = parametro("idsolicitud", $credito, MQL_INT); $credito = parametro("solicitud", $credito, MQL_INT);
$cuenta		= parametro("cuenta", DEFAULT_CUENTA_CORRIENTE, MQL_INT); $cuenta = parametro("idcuenta", $cuenta, MQL_INT);
$jscallback	= parametro("callback"); $tiny = parametro("tiny"); $form = parametro("form"); $action = parametro("action", SYS_NINGUNO);
$monto		= parametro("monto",0, MQL_FLOAT); $monto	= parametro("idmonto",$monto, MQL_FLOAT); 
$recibo		= parametro("recibo", 0, MQL_INT); $recibo	= parametro("idrecibo", $recibo, MQL_INT);
$empresa	= parametro("empresa", 0, MQL_INT); $empresa	= parametro("idempresa", $empresa, MQL_INT); $empresa	= parametro("iddependencia", $empresa, MQL_INT);
$grupo		= parametro("idgrupo", 0, MQL_INT); $grupo	= parametro("grupo", $grupo, MQL_INT);
$ctabancaria = parametro("idcodigodecuenta", 0, MQL_INT); $ctabancaria = parametro("cuentabancaria", $ctabancaria, MQL_INT);

$observaciones= parametro("idobservaciones");

$xHP->init();

$xFRM		= new cHForm("frmpersonasvivpanel", "./");
$xSel		= new cHSelect();
$xFRM->setTitle($xHP->getTitle());
if($clave>0){ 
	$xViv		= new cPersonasVivienda(false, false);
	$xViv->setID($clave);
	if($xViv->init()){
		$xFRM->addHElem($xViv->getFicha("", true));
		$xFRM->addCerrar();
		$xFRM->addAviso("", "idaviso");
		$xFRM->OHidden("id", $clave);
		$xFRM->OButton("TR.MARCARCOMO INACTIVA", "jsConfirmarBaja()", $xFRM->ic()->DESCARTAR, "cmdinactive", "gris");
		
		if($xViv->getEsPrincipal() == false){
			$xFRM->OButton("TR.MARCARCOMO PRINCIPAL", "jsaMarkPrincipal()", $xFRM->ic()->VALIDAR, "cmdmarkprincipal", "green");
		}
		
		
		if($xViv->getSeConstruye() == true){
			
		} else {
			$xFRM->OButton("TR.MARCARCOMO ENCONSTRUCCION", "jsaMarkSeConstruye()", $xFRM->ic()->VALIDAR, "", "yellow");
		}
		$xFRM->addEliminar("jsConfirmarEliminar()");
	}
} else {
	$xFRM->addCerrar();
}

echo $xFRM->get();
$jxc ->drawJavaScript(false, true);
?>
<script>
var xG	= new Gen();

function jsConfirmarEliminar(){
	xG.confirmar({msg : "CONFIRMA_ELIMINAR", callback:jsaEliminarVivienda });
}
function jsConfirmarBaja(){
	xG.confirmar({msg : "CONFIRMA_BAJA", callback:jsaMarkInactive });
}
</script>
<?php
$xHP->fin();
?>