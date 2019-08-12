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
$xHP				= new cHPage("TR.Cierre del dia");

$eacp				= EACP_CLAVE;
$observaciones 		= parametro("idobservaciones");
$fecha				= parametro("idfecha-0", false, MQL_RAW);	// fecha de Trabajo
$forzar				= parametro("idforzar", false, MQL_BOOL);
$xF					= new cFecha();
$fecha				= $xF->getFechaISO($fecha);
$action 			= parametro("action", SYS_NINGUNO);
$msg				= "";
$xCierre			= new cCierreDelDia($fecha);
$xCierre->setForzar($forzar);
setFoliosAlMaximo();


$msg		= "EL CIERRE SE EFECTUARA UNA VEZ POR DIA, SI EL CIERRE YA ESTA HECHO NO SE ADMITIRAN MAS OPERACIONES.\r\nTENGA CUIDADO. EL SISTEMA SE BLOQUEARA DESPUES DEL CIERRE.\r\nEL PROCESO TARDARA UNOS MINUTOS\r\n";
$msg		.= "FECHA DE CIERRE $fecha .LA FECHA DE INICIO EN EL SISTEMA ES " . FECHA_INICIO_OPERACIONES_SISTEMA. "\r\n";

$jxc = new TinyAjax();
function jsaGetListadoCierres($fecha){
	$xT			= new cTipos();
	$xLi		= new cSQLListas();	
	$xF			= new cFecha();
	$fecha		= $xF->getFechaISO($fecha);

	
	$sqlList	= $xLi->getListadoDeRecibos(12, "", "", $fecha);
	
	$xTab		= new cTabla($sqlList);
	$xTab->OButton("TR.Reporte", "var xR = new RecGen(); xR.reporte(" . HP_REPLACE_ID . ")", $xTab->ODicIcons()->REPORTE);
	$xTab->OButton("TR.Panel", "var xR = new RecGen(); xR.panel(" . HP_REPLACE_ID . ")", $xTab->ODicIcons()->CONTROL);
	$xBtn		= new cHButton();
	
	$sql		=  $xLi->getListadoDeCajasConUsuario(TESORERIA_CAJA_ABIERTA, $fecha);
	
	$T2			= new cTabla($sql);
	$T2->addEspTool($xBtn->getBasic("", "jsToCerrarCorte('$fecha')", "bloquear", "idcerrar", true));
	
	
	$html		= $xTab->Show("TR.LISTADO DE CIERRES");
	if(MODULO_CAJA_ACTIVADO == true){ 
		$html 		.= $T2->Show("TR.Cajas Abiertas");
		$itemsAbier	= $T2->getRowCount();
		$html		.= "<input type='hidden' id='idabiertas' value='$itemsAbier' />"; 
	} else {
		$html		.= "<input type='hidden' id='idabiertas' value='0' />";
	}
	
	return  $html; 	

}
$jxc ->exportFunction('jsaGetListadoCierres', array('idfecha-0'), "#listados");
$jxc ->process();


if($action == SYS_UNO){
	if($xCierre->checkCierre($fecha) == true ){
		$msg	.= $xCierre->getMessages();
	} else {
		$status		= $xCierre->check5Cierres($fecha, true);
		if($status[SYS_ESTADO] == false ){
			$msg	.= $xCierre->getMessages();
		} else {
			header("Location: ../frmutils/cierre_de_colocacion.frm.php?k=" . MY_KEY . "&s=true&f=" . $fecha);
			exit;
		}
	}
	$fecha 		= $xCierre->getFechaUltima();
} else {
	if($xCierre->checkCierre($fecha) == true ){
		$msg	.= $xCierre->getMessages();
	} else {
		$status		= $xCierre->check5Cierres($fecha, true);
		if($status[SYS_ESTADO] == false ){
			$msg	.= $xCierre->getMessages();
		}
	}
	$fecha 		= $xCierre->getFechaUltima();
}



	$xHP->init('jsaGetListadoCierres()');
	
	$xFRM		= new cHForm("frmcierre", "frmcierredeldia.php?action=1");
	$xBtn		= new cHButton();
	$xTxt		= new cHText();
	$xDate		= new cHDate();
	$xSel		= new cHSelect();
	
	$xFRM->setTitle($xHP->getTitle());
	
	$xFRM->OButton("TR.Ejecutar Cierre", "jsChecarAbiertas()", $xFRM->ic()->EJECUTAR);
	
	
	$xFRM->addCerrar();
	$xFRM->addJsBasico();
	
	

	$xDate->addEvents(" onchange='jsGetListaDeCierres()' ");
	$xFRM->addHElem( $xDate->get("TR.Fecha de corte", $fecha) );
	$xSelP	= $xSel->getListaDePeriodosDeCredito("periodo_actual", false, EACP_PER_SOLICITUDES);
	$xSelPH	= $xSelP->get(true);
	
	if($xSelP->getCountRows()>0){
		$xFRM->addHElem( $xSelPH );
	} else {
		
		$xFRM->OHidden("periodo_actual",  EACP_PER_SOLICITUDES);
	}
	

	
	$xFRM->addObservaciones();
	if(MODO_DEBUG == true){
		$xFRM->OCheck("TR.FORZAR", "idforzar");
		
		$xFRM->OButton("TR.CREDITOS", "jsCierreDeColocacion()", $xFRM->ic()->DINERO);
		
		if(MODULO_CAPTACION_ACTIVADO == true){
			$xFRM->OButton("TR.Captacion", "jsCierreDeCaptacion()", $xFRM->ic()->AHORRO);
		}
		if(MODULO_CONTABILIDAD_ACTIVADO == true){
			$xFRM->OButton("TR.Contabilidad", "jsCierreDeContabilidad()", $xFRM->ic()->CONTABLE);
		}
		if(MODULO_SEGUIMIENTO_ACTIVADO == true){
			$xFRM->OButton("TR.Seguimiento", "jsCierreDeSeguimiento()", $xFRM->ic()->COBROS);
		}
		if(MODULO_AML_ACTIVADO == true){
			$xFRM->OButton("TR.Riesgos", "jsCierreDeRiesgos()", $xFRM->ic()->RIESGO);
		}
		$xFRM->OButton("TR.Sistema", "jsCierreDeSistema()", $xFRM->ic()->SALUD);
	} else {
		$xFRM->OHidden("idforzar", "false");
	}
	$xFRM->addHTML("<div id='listados'></div>");
	
	$xFRM->addAviso($msg);

	$xFRM->OButton("TR.AGREGAR Periodo de mesa_de_credito", "var xG=new Gen();xG.w({url:'../frmcreditos/cambiarperiodo.frm.php?a=1', tiny:true});", $xFRM->ic()->CALENDARIO);
	$xFRM->OButton("TR.Salir del Sistema", "var xG = new Gen(); xG.salir();", "salir", "idcmdsalir", "red");
	
	echo $xFRM->get();
?>
<script>
var kk 	= "<?php echo MY_KEY; ?>";
var xG	= new Gen();
function jsToCerrarCorte(f){ var xT = new TesGen(); xT.goCerrarCaja(f);  }
function jsGetListaDeCierres() {   jsaGetListadoCierres();  }
	
function jsChecarAbiertas(){
	var idomitir	= $('#idforzar').prop('checked');
	var itms		= $("#idabiertas").val();
	if(entero(itms) > 0 && idomitir == false){
		alert("EXISTEN CAJAS ABIERTAS!!");
	} else {
		xG.spinInit();
		frmcierre.submit();
	}
}
function jsCierreDeColocacion(){ jsExeCierre("cierre_de_colocacion.frm.php"); }
function jsCierreDeCaptacion(){ jsExeCierre("cierre_de_captacion.frm.php"); }
function jsCierreDeContabilidad(){ jsExeCierre("cierre_de_contabilidad.frm.php"); }
function jsCierreDeSeguimiento(){ jsExeCierre("cierre_de_seguimiento.frm.php"); }
function jsCierreDeRiesgos(){
	jsExeCierre("cierre_de_riesgos.frm.php");
}
function jsExeCierre(pp){
	var idfecha		= $("#idfecha-0").val();
	var idomitir	= $('#idforzar').prop('checked');
	var ff			= (idomitir == true) ? "&forzar=true" : "";
	xG.w({url:"../frmutils/" + pp + "?f=" + idfecha + "&k=" + kk + ff});
}
function jsCierreDeSistema(){
	var idfecha		= $("#idfecha-0").val();
	var idomitir	= $('#idforzar').prop('checked');
	var ff			= (idomitir == true) ? "&force=true" : "";
	xG.w({url:"../frmutils/cierre_de_sistema.frm.php?s=true&f=" + idfecha + "&k=" + kk + ff});
}
</script>
<?php
$jxc ->drawJavaScript(false, true); 

$xHP->fin();
?>