<?php
//=====================================================================================================
//=====>	INICIO_H
	include_once("../core/go.login.inc.php");
	include_once("../core/core.error.inc.php");
	include_once("../core/core.html.inc.php");
	include_once("../core/core.init.inc.php");
	$theFile					= __FILE__;
	$permiso					= getSIPAKALPermissions($theFile);
	if($permiso === false){		header ("location:../404.php?i=999");	}
	$_SESSION["current_file"]	= addslashes( $theFile );
//<=====	FIN_H
	$iduser = $_SESSION["log_id"];
//=====================================================================================================
$xHP					= new cHPage("TR.Saldos de Credito", HP_REPORT);
$xF						= new cFecha();
$xQL					= new MQL();
$xLi					= new cSQLListas();

//=====================================================================================================


$periocidad 			= parametro("f1", SYS_TODAS);
$periocidad 			= parametro("periocidad", $periocidad);
$periocidad 			= parametro("frecuencia", $periocidad);

$estado 				= parametro("estado", SYS_TODAS); 
$estado 				= parametro("estatus", $estado);
$producto 				= parametro("convenio", SYS_TODAS);
$producto 				= parametro("producto", $producto);
$fechaInicial			= parametro("on", EACP_FECHA_DE_CONSTITUCION);
$fechaFinal				= parametro("off", fechasys());
$fechaInicial			= $xF->getFechaISO($fechaInicial);
$fechaFinal				= $xF->getFechaISO($fechaFinal);
$formato				= parametro("out", SYS_DEFAULT, MQL_RAW);
$sucursal				= parametro("sucursal", SYS_TODAS, MQL_RAW);
$xRPT					= new cReportes($xHP->getTitle());

$ByProducto				= $xLi->OFiltro()->CreditosPorProducto($producto);
$BySucursal				= $xLi->OFiltro()->CreditosPorSucursal($sucursal);

$idmunicipio			= parametro("municipioactivo", "");
$ByMunicipio			= $xLi->OFiltro()->CreditosPorMunicipioAct($idmunicipio);

$titulo					= $xHP->getTitle();

if($ByMunicipio !== ""){
	$xMun		= new cDomicilioMunicipio(); $xMun->initByIDUnico($idmunicipio);
	$municipio	= $xMun->getNombre();
	$entidadfed	= $xMun->getOEstado()->getNombre();
	$titulo		= $titulo . " / Municipio : $entidadfed - $municipio";
}



$sql					= "
SELECT   `personas`.`nombre`,
         `creditos_tipoconvenio`.`descripcion_tipoconvenio` AS `producto`,
         `creditos_solicitud`.`fecha_ministracion` AS `otorgamiento`,
         `creditos_solicitud`.`fecha_vencimiento` AS `vencimiento`,
         `creditos_solicitud`.`monto_autorizado` AS `capital_entegrado`,
         `creditos_periocidadpagos`.`descripcion_periocidadpagos` AS `frecuencia`,
         
         `creditos_solicitud`.`numero_solicitud`   AS `credito`,
         
         ROUND(((`creditos_solicitud`.`tasa_interes` * 100) / 12) , 2) AS `tasa_interes`,

	/*COUNT(`operaciones_mvtos`.`tipo_operacion`) AS `operaciones`,

	MAX(`operaciones_mvtos`.`fecha_afectacion`) AS `fecha`,
	SUM(
	IF(`operaciones_mvtos`.`tipo_operacion` = 120,	`operaciones_mvtos`.`afectacion_real`, 0	)
	)  AS `abonos`,*/

	(`creditos_solicitud`.`monto_autorizado`  - SUM(
	IF(`operaciones_mvtos`.`tipo_operacion` = 120,	`operaciones_mvtos`.`afectacion_real`, 0	)
	)) AS 'saldo',

         getDiasDeMora(`creditos_solicitud`.`numero_solicitud`,`creditos_solicitud`.`periocidad_de_pago`) AS `dias_vencidos`,
         
         `creditos_solicitud`.`sucursal`
FROM     `operaciones_mvtos` 
INNER JOIN `creditos_solicitud`  ON `operaciones_mvtos`.`docto_afectado` = `creditos_solicitud`.`numero_solicitud` 
INNER JOIN `creditos_tipoconvenio`  ON `creditos_solicitud`.`tipo_convenio` = `creditos_tipoconvenio`.`idcreditos_tipoconvenio` 
INNER JOIN `creditos_periocidadpagos`  ON `creditos_solicitud`.`periocidad_de_pago` = `creditos_periocidadpagos`.`idcreditos_periocidadpagos` 
INNER JOIN `personas`  ON `personas`.`codigo` = `creditos_solicitud`.`numero_socio` 

WHERE
	(
	(`operaciones_mvtos`.`tipo_operacion` =120) 
	OR
	(`operaciones_mvtos`.`tipo_operacion` =110)
	)
	AND
	(`operaciones_mvtos`.`fecha_afectacion` <= '$fechaFinal') 
	$ByProducto $BySucursal $ByMunicipio
	
	AND creditos_solicitud.estatus_actual != " . CREDITO_ESTADO_CASTIGADO . "

GROUP BY
	`operaciones_mvtos`.`docto_afectado`
	
HAVING saldo != 0
";
//$sql				= "CALL sp_saldos_al_cierre('$fechaFinal')";
//exit($sql);
$xTbl					= new cTabla($sql);
$xTbl->setFechaCorte($fechaFinal);
/*
$xTbl->setFootSum(array(
		4 => "monto",
		8 => "abonos",
		9 => "saldo"
));
*/
$xTbl->setColSum("monto");
$xTbl->setColSum("abonos");
$xTbl->setColSum("saldo");


/*$xTbl->setFootSum(array(
	3 => "monto_autorizado",
		52 => "abonos",
		53 => "saldo"
));*/
$body		= $xRPT->getEncabezado($titulo);
$xRPT->setBodyMail($body);
$xRPT->addContent($body);

$xRPT->setTitle($xHP->getTitle());

$xRPT->setSQL($xTbl->getSQL());
$xTbl->setTipoSalida($formato);
$xRPT->setOut($formato);
$xRPT->addContent($xTbl->Show());
//$xRPT->setResponse();
echo $xRPT->render(true);
?>