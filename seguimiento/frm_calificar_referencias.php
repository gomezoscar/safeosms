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
$xHP		= new cHPage("TR.REFERENCIAS_PERSONALES", HP_FORM);
$xQL		= new MQL();
$xLi		= new cSQLListas();
$xF			= new cFecha();
$xDic		= new cHDicccionarioDeTablas();
$jxc 		= new TinyAjax();
//$tab = new TinyAjaxBehavior();
//$tab -> add(TabSetValue::getBehavior("idide", $x));
//return $tab -> getString();
//$jxc ->exportFunction('datos_del_pago', array('idsolicitud', 'idparcialidad'), "#iddatos_pago");
//$jxc ->process();
$clave		= parametro("id", 0, MQL_INT); $clave		= parametro("clave", $clave, MQL_INT);
$fecha		= parametro("idfecha-0", false, MQL_DATE); $fecha = parametro("idfechaactual", $fecha, MQL_DATE);  $fecha = parametro("idfecha", $fecha, MQL_DATE);
$persona	= parametro("persona", DEFAULT_SOCIO, MQL_INT); $persona = parametro("socio", $persona, MQL_INT); $persona = parametro("idsocio", $persona, MQL_INT);
$credito	= parametro("credito", DEFAULT_CREDITO, MQL_INT); $credito = parametro("idsolicitud", $credito, MQL_INT); $credito = parametro("solicitud", $credito, MQL_INT);
$cuenta		= parametro("cuenta", DEFAULT_CUENTA_CORRIENTE, MQL_INT); $cuenta = parametro("idcuenta", $cuenta, MQL_INT);
$jscallback	= parametro("callback"); $tiny = parametro("tiny"); $form = parametro("form"); $action = parametro("action", SYS_NINGUNO);
$monto		= parametro("monto",0, MQL_FLOAT); $monto	= parametro("idmonto",$monto, MQL_FLOAT);
$recibo		= parametro("recibo", 0, MQL_INT); $recibo	= parametro("idrecibo", $recibo, MQL_INT);
$empresa	= parametro("empresa", 0, MQL_INT); $empresa	= parametro("idempresa", $empresa, MQL_INT); $empresa	= parametro("iddependencia", $empresa, MQL_INT); $empresa	= parametro("dependencia", $empresa, MQL_INT);
$grupo		= parametro("idgrupo", 0, MQL_INT); $grupo	= parametro("grupo", $grupo, MQL_INT);
$ctabancaria = parametro("idcodigodecuenta", 0, MQL_INT); $ctabancaria = parametro("cuentabancaria", $ctabancaria, MQL_INT);

$observaciones= parametro("idobservaciones");

$xHP->init();

$xFRM		= new cHForm("frmcalificarefs", "./");
$xSel		= new cHSelect();
$xSel2		= new cHSelect();

$xFRM->setTitle($xHP->getTitle());

$xSel2->addOptions(array(
		"0" => "Opinion Personal",
		"10" => "Buena",
		"5" => "Regular",
		"1" => "Indiferente",
		"-5" => "Mala",
		"-10" => "Muy Mala"
));
$xSel2->setDivClass("");
$xSel2->setDefault("0");

if($persona <= DEFAULT_SOCIO){
	//$xFRM->addCreditBasico();
	$xFRM->addPersonaBasico();
	$xFRM->addSubmit();
	
} else {
	$xFRM->addCerrar();
	
	$xSoc	= new cSocio($persona);
	if($xSoc->init() == true){
		$xSoc->getFicha(false, true, "", true);
		$xTabla	= new cTabla($xLi->getListadoDeRelaciones($persona));
		
		$xTabla->setOmitidos("curp");
		$xTabla->setOmitidos("clave_de_persona");
		$xTabla->setOmitidos("domicilio");
		$xTabla->setOmitidos("fecha_de_nacimiento");
		$xTabla->setOmitidos("ocupacion");
		$xSel2->addEvent("jsUpdateOpinion(this, " . HP_REPLACE_ID . ")", "onchange");
		$xTabla->addEspTool($xSel2->get("id-" . HP_REPLACE_ID));
		$xTabla->setWidthTool("300px");
		
		$xFRM->addHElem($xTabla->Show());
		
	}
}

echo $xFRM->get();
?>
<script>
var xG = new Gen();
function jsUpdateOpinion(obj, id){
	
}

</script>
<?php
//$jxc ->drawJavaScript(false, true);
$xHP->fin();
exit;
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
include_once("../core/entidad.datos.php");
include_once("../core/core.deprecated.inc.php");
include_once("../core/core.fechas.inc.php");
include_once("../libs/sql.inc.php");
include_once("../core/core.config.inc.php");

$oficial = elusuario($iduser);
$action =	$_GET["a"];
//require_once("." . TINYAJAX_PATH . "/TinyAjax.php");
//$jxc = new TinyAjax();
//$jxc ->exportFunction('datos_del_pago', array('idsolicitud', 'idparcialidad'), "#iddatos_pago");	
//$jxc ->process();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title></title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link href="<?php echo CSS_GENERAL_FILE; ?>" rel="stylesheet" type="text/css">
</head>
<?php
//$jxc ->drawJavaScript(false, true); 
jsbasic("frmScoreReferences", "", ".");
?>
<body>
<?php
if(!isset($action)){
?>
<form name="frmScoreReferences" method="post" action="frm_calificar_referencias.php?a=1">
<fieldset>
	<legend><script> document.write(document.title); </script></legend>
	<table border='0' width='100%'  >
		<tbody>
		<tr>
			<td>Clave de Persona</td><td><input type='text' name='idsocio' value='' onchange="envsoc();" class='mny' size='12' />
			<?php echo CTRL_GOSOCIO; ?></td>
			<td>Nombre Completo</td><td><input name='nombresocio' type='text' disabled value='' size="50"></td>
		</tr>
		<tr>
			<th colspan="4"><input type="button" onclick="frmSubmit();" value="Iniciar Calificacion" /></th>
		</tr>
		</tbody>
	</table>
</fieldset>
</form>
<?php
} elseif($action==1){
	//procedimientos PHP/AJAX para Calificar Referencias
	$socio = $_POST["idsocio"];
	if(!isset($socio)){
		$socio = $_GET["i"];
		if(!isset($socio)){
			//ATRAS
		}
	}
	?>
<form name="frmScoreReferences" method="post" action="frm_calificar_referencias.php?a=2">
<fieldset>
	<legend><script> document.write(document.title); </script></legend>
	<?php
		$cFich = new cFicha(iDE_SOCIO, $socio);
		$cFich->setTableWidth();
		$cFich->show();
	?>
	<table border='0' width='100%'  >
		<tbody>
		<tr>
			<th>Control</th>
			<th>Nombre</th>
			<th>Tipo de Referencia</th>
			<th>Opinion Personal</th>
			<th>Tiempo de conocerlo</th>
			<th>Guardar Calificaci&oacute;n</th>
		</tr>
<?php
	$sql = "SELECT
	`socios_relaciones`.`idsocios_relaciones`              AS `control`,
	CONCAT(`socios_relaciones`.`nombres`, ' ',
	`socios_relaciones`.`apellido_paterno`, ' ',
	`socios_relaciones`.`apellido_materno`) AS 'nombre_completo',
	`socios_relacionestipos`.`descripcion_relacionestipos` AS `tipo_de_relacion`
	,
	`socios_relaciones`.`telefono_residencia`,
	`socios_relaciones`.`telefono_movil` 
FROM
	`socios_relaciones` `socios_relaciones` 
		INNER JOIN `socios_relacionestipos` `socios_relacionestipos` 
		ON `socios_relaciones`.`tipo_relacion` = `socios_relacionestipos`.
		`idsocios_relacionestipos`
WHERE
	(`socios_relaciones`.`socio_relacionado` =$socio)
	AND
	(`socios_relacionestipos`.`subclasificacion` =2)";
	$rs = mysql_query($sql, cnnGeneral());
		while($rw = mysql_fetch_array($rs)){
			$control	= $rw["control"];
			
			$defSelOpinion = "<select name=\"cScoreOpinion$control\" id=\"idScoreOpinion$control\" size=\"3\">
								<option value=\"10\">Buena</option>
								<option value=\"5\">Regular</option>
								<option value=\"0\" selected='true' >Indiferente</option>
								<option value=\"-5\">Mala</option>
								<option value=\"-10\">Muy Mala</option>
							</select>
							";
			$sql			= "SELECT
								`socios_tiempo`.`valor_calificacion_por_referencia`,
								`socios_tiempo`.`descripcion_tiempo` 
							FROM
							`socios_tiempo` `socios_tiempo`";
			$SelTiempo	= new cSelect("ScoreTiempo$control", "", $sql);
			$SelTiempo->setEsSql();
			$SelTiempo->setNRows(3);
			$defSelTiempo	= $SelTiempo->show();
			//Datos de la DB
			$nombre		= $rw["nombre_completo"];
			$relacion	= $rw["tipo_de_relacion"];
			
			echo "<tr id =\"tr-$control\">
					<th>$control</th>
					<td>$nombre</td>
					<td>$relacion</td>
					<td>$defSelTiempo</td>
					<td>$defSelOpinion</td>
					<th><a class='button' onclick=\"setScoreReference($control);\">Guardar Calificaci&oacute;n</a></th>
				</tr>";
		}
?>
		</tbody>
	</table>
</fieldset>
</form>	
<?php
}
?>
</body>
<script  >
var jsTmpFile 	= "../js/socios.common.js.php";
var jsDivChar	= '<?php echo STD_LITERAL_DIVISOR; ?>';
function setScoreReference(varID){
	<?php
		//Obtiene el Maximo de ScoreTiempo
		$SqlMaxTime = "SELECT max(valor_calificacion_por_referencia) AS 'score'
						FROM socios_tiempo";
		$mxTime		= mifila($SqlMaxTime, "score");
	?>
	var ScoreNeto		= 0;
	var ScoreTiempo		= document.getElementById("idScoreTiempo" + varID).value;
	var ScoreOpinion	= document.getElementById("idScoreOpinion" + varID).value;
	var maxScoreTiempo	= Number(<?php echo $mxTime; ?>);
	<?php
	$mForm = new cFormula("score_reference");
	echo $mForm->getFormula();
	?>
	//alert(ScoreNeto);
	var Params	= varID + jsDivChar + ScoreNeto;
		jsrsExecute(jsTmpFile, msgbox,'Common_97de3870795ecc1247287ab941d9719b', Params);
}
</script>
</html>
