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
$xHP		= new cHPage("", HP_SERVICE);
$xQL		= new MQL();
$xLi		= new cSQLListas();
$xF			= new cFecha();
$xRuls		= new cReglaDeNegocio();
$SinOtros	= $xRuls->getValorPorRegla($xRuls->reglas()->CREDITOS_PLAN_SIN_OTROS);	//regla de negocio
$SinAnual	= $xRuls->getValorPorRegla($xRuls->reglas()->CREDITOS_PLAN_SIN_ANUAL);	//regla de negocio
$ConPagEs	= $xRuls->getValorPorRegla($xRuls->reglas()->CREDITOS_PLAN_CON_PAGESP);	//regla de negocio
$ConPago0	= $xRuls->getValorPorRegla($xRuls->reglas()->CREDITOS_PLAN_CON_CEROS);	//regla de negocio
$SinAjustF	= $xRuls->getValorPorRegla($xRuls->reglas()->CREDITOS_PLAN_SIN_FINAL);	//regla de negocio


$clave		= parametro("id", 0, MQL_INT); $clave		= parametro("clave", $clave, MQL_INT);  
$fecha		= parametro("idfecha-0", false, MQL_DATE); $fecha = parametro("idfechaactual", $fecha, MQL_DATE); 
$persona	= parametro("persona", DEFAULT_SOCIO, MQL_INT); $persona = parametro("socio", $persona, MQL_INT); $persona = parametro("idsocio", $persona, MQL_INT);
$credito	= parametro("credito", DEFAULT_CREDITO, MQL_INT); $credito = parametro("idsolicitud", $credito, MQL_INT); $credito = parametro("solicitud", $credito, MQL_INT);
$cuenta		= parametro("cuenta", DEFAULT_CUENTA_CORRIENTE, MQL_INT); $cuenta = parametro("idcuenta", $cuenta, MQL_INT);
$jscallback	= parametro("callback"); $tiny = parametro("tiny"); $form = parametro("form"); $action = parametro("action", SYS_NINGUNO);
$monto		= parametro("monto",0, MQL_FLOAT); $monto	= parametro("idmonto",$monto, MQL_FLOAT); 
$recibo		= parametro("recibo", 0, MQL_INT); $recibo	= parametro("idrecibo", $recibo, MQL_INT);
$observaciones= parametro("idobservaciones");
$letra		= parametro("letra", false, MQL_INT);

$rs				= array();
$rs["error"]	= true;
$rs["message"]	= "No se agrega el pago";
if($monto >= 0){
	$xCred	= new cCredito($credito);
	if($xCred->init() == true){
		$xProd		= new cProductoDeCredito($xCred->getClaveDeConvenio());
		if($xProd->init() == true){
			if($xProd->getAplicaPagosEsp() == true){
				$_SESSION["$credito-$letra-" . OPERACION_CLAVE_PAGO_CAPITAL . ""] = $monto;
				//$xPagEsp	= new cCreditosPlanPagoEsp();
				//if($xPagEsp->add($credito, $letra, $monto) > 0){
				$rs["error"]	= false;
				$rs["message"]	= "Agregado Pago Especial a la Parcialidad $letra del Credito $credito por un monto $monto";
				//}
				//setLog($_SESSION["$credito-$letra-" . OPERACION_CLAVE_ANUALIDAD_C . ""]);
			}
		}
	}
	

}
header('Content-type: application/json');
echo json_encode($rs);
?>