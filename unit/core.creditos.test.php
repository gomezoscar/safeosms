<?php
//=====================================================================================================
//=====>	INICIO_H
	include_once("../core/go.login.inc.php");
	include_once("../core/core.error.inc.php");
	include_once("../core/core.html.inc.php");
	include_once("../core/core.init.inc.php");
	$theFile					= __FILE__;
	//$permiso					= getSIPAKALPermissions($theFile);
	//if($permiso === false){		header ("location:../404.php?i=999");	}
	$_SESSION["current_file"]	= addslashes( $theFile );
//<=====	FIN_H
//=====================================================================================================
$xP		= new cHPage("Pruebas de la Clase Creditos", HP_FORM);
$xUtils	= new cUtileriasParaCreditos();

echo $xP->getHeader();
echo $xP->setBodyinit();
//
$fecha		=parametro("fecha" , fechasys());
/*
Fecha_Cierre_Credito,Forma_Pago_Mop ( Que solo puede salir viendo cuantos dias transcurridos  del credito han pasado) y Monto_Ultimo_Pago creo que con eso ya sale para hoy
 * */
$credito_de_pruebas		= parametro("credito", 209999801, MQL_INT);
//Crear formularios
$xFRM	= new cHForm("frmTest", "./test.php");

$xHTxt	= new cHText("");
//====================================================================================================
$xFRM->addHElem( "<p class='aviso'>Pruebas de la Clase Creditos</p>" );

//$xCred			= new cCredito($credito_de_pruebas);
//$xCred->init();

//$xFRM->addHElem( $xCred->getFicha(true, "", true, true) );
//$xFRM->addCreditoComandos($credito_de_pruebas);

//$xFRM->addHElem($xCred->setDetermineDatosDeEstatus(fechasys(), true));
//
//$xFRM->addAviso($xCred->getMessages(), "id2", false, "warning");
//$xFRM->addAviso(, "id2", false, "warning");
//$xFRM->addLog($xUtils->getMessages());
/*$xFRM->addHElem( "<p class='aviso'>Vencimiento : " . $xCred->setDetermineDatosDeEstatus(false, true) . "</p>" );

$xFRM->addHElem( "<p class='aviso'>fecha de ultimo pago de CAPITAL : " . $xCred->getFechaUltimoMvtoCapital() . "</p>" );

$xFRM->addHElem( "<p class='aviso'>Monto de Parcialidad : " . $xCred->getMontoDeParcialidad() . "</p>" );

$xFRM->addHElem( "<p class='aviso'>Saldo Actual Normal : " . $xCred->getSaldoActual() . "</p>" );

$xFRM->addHElem( "<p class='aviso'>Saldo Actual FORZADO: " . $xCred->getSaldoActual($fecha) . "</p>" );
$xFRM->addHElem( "<p class='aviso'>Monto de Ultimo Pago : " . $xCred->getMontoUltimoPago() . "</p>" );
$xFRM->addHElem( "<p class='aviso'>fecha de Ultimo Pago S: " . $xCred->getFechaUltimoDePago() . "</p>" );
$xFRM->addHElem( "<p class='aviso'>Saldo Insoluto Integrado : " . $xCred->getSaldoIntegrado($fecha) . "</p>" );
$xFRM->addHElem( "<p class='aviso'>Saldo Vencido : " . $xCred->getSaldoVencido() . "</p>" );
*/
/*$xPlan	= new cPlanDePagos();
$xPlan->initByCredito($xCred->getNumeroDeCredito());
$xPlan->calcular();
$xFRM->addAviso( $xPlan->getMessages() );*/
/*$sql	= "SELECT * FROM creditos_solicitud LIMIT 0,100";
$xQL	= new MQL();
$xTCred	= new cCreditos_solicitud();
$rs		= $xQL->getRecordset($sql);

while($rw = $rs->fetch_assoc()){
	$xTCred->setData($rw);
	echo "<code>" . $xTCred->numero_solicitud()->v() . "</code> <p />";
}*/

$fechaop		= fechasys();
$xF				= new cFecha();
$idsocio			= 1901154;
$xODT				= new cHDicccionarioDeTablas();

//$LVig2				= $xODT->getCreditosPorMinistrar(false, $idsocio,"TR.AUTORIZADO");

$LVig3				= $xODT->getCreditosPorAutorizar(false, $idsocio, "TR.SOLICITADO");

//$xFRM->addHElem($LVig2);
$xFRM->addHElem($LVig3);

/*CUtils		= new cUtileriasParaCreditos();
//$xCUtils->setEstatusDeCreditos(false, fechasys(), false, true, false, true);
if(getEnCierre() == false){
	$xFRM->addAviso("ES FALSO");
}

if(getEnCierre(true) == true){
	$xFRM->addAviso("ES VERDADERO");
}

if(getEnCierre(false) == false){
	$xFRM->addAviso("ES FALSO");
}
getEnCierre(true);
if(getEnCierre() == true){
	$xFRM->addAviso("ES VERDADERO");
}*/
//$xCUtils->setReestructurarSDPM_Planes(true, false, false, $fechaop, $xF->getDiaInicial(), true);
//$xFRM->addFooterBar(  );
//$xFRM->addLog($xUtils->setCambiarPersonaDeCredito($credito_de_pruebas, "1901549"));

//$xFRM->addAviso($xCred->getMessages());
//producto
/*$xPDT			= new cProductoDeCredito(200);
$xPDT->init();

$xFRM->addHElem( "<p class='aviso'>Saldo Vencido : " . $xPDT->getPathPagare($credito_de_pruebas) . "</p>" );
*/
echo $xFRM->get();


echo $xP->setBodyEnd();
echo $xP->end();
//=====================================================================================================

?>