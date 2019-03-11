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
$xHP		= new cHPage("TR.AUTORIZACION DE CREDITOS", HP_FORM);
$xQL		= new MQL();
$xLi		= new cSQLListas();
$xF			= new cFecha();
$xDic		= new cHDicccionarioDeTablas();
//$jxc 		= new TinyAjax();
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
$xHP->addJTableSupport();
$xHP->init();



$xFRM		= new cHForm("frmautorizaceds", "./");
$xSel		= new cHSelect();
$xFRM->setTitle($xHP->getTitle());
$xFRM->addCerrar();




/* ===========		GRID JS		============*/

$xHG	= new cHGrid("iddivcrediaut",$xHP->getTitle());

$xHG->setSQL($xLi->getListaDeCreditosEnProceso(EACP_PER_SOLICITUDES, CREDITO_ESTADO_SOLICITADO, false, false, false, false, false, false));
$xHG->setNoPaginar();

$xHG->col("codigo", "TR.CLAVE_DE_PERSONA", "10%");
$xHG->col("nombre", "TR.NOMBRE", "20%");
$xHG->col("numero_de_solicitud", "TR.CREDITO", "10%");
$xHG->col("empresa", "TR.EMPLEADOR", "15%");

$xHG->col("fecha_de_registro", "TR.FECHA", "10%");
$xHG->col("monto_solicitado", "TR.MONTO", "10%", true);


//$xHG->col("nombre", "TR.", "%");


$xHG->addList();
$xHG->addKey("idcreditos_rechazados");


$xHG->OToolbar("TR.NUEVO CREDITO", "jsAdd()", "grid/add.png");


$xHG->OButton("TR.AUTORIZAR", "jsAutorizar('+ data.record.numero_de_solicitud +')", "done.png");
$xHG->OButton("TR.RECHAZAR", "jsRechazar('+ data.record.numero_de_solicitud +')", "undone.png");

//$xHG->OButton("TR.EDITAR", "jsEdit('+ data.record.idcreditos_rechazados +')", "edit.png");
//$xHG->OButton("TR.ELIMINAR", "jsDel('+ data.record.idcreditos_rechazados +')", "delete.png");
$xFRM->addHElem("<div id='iddivcrediaut'></div>");
$xFRM->addJsCode( $xHG->getJs(true) );
echo $xFRM->get();
?>
<script>
var xG	= new Gen();
function jsEdit(id){
	//xG.w({url:"../frm/autorizacion-de-creditos.edit.frm.php?clave=" + id, tiny:true, callback: jsLGiddivcrediaut});
}
function jsAdd(){
	xG.w({url:"../frmcreditos/frmcreditosautorizados.php?", tiny:true, callback: jsLGiddivcrediaut});
}
function jsDel(id){
	//xG.rmRecord({tabla:"creditos_rechazados", id:id, callback:jsLGiddivcrediaut});
}
function jsAutorizar(id){
	xG.w({url:"../frmcreditos/frmcreditosautorizados.php?credito=" + id, tab:true, callback: jsLGiddivcrediaut});
}
function jsRechazar(id){
	xG.w({url:"../frmcreditos/creditos-rechazados.frm.php?credito=" + id, tab:true, callback: jsLGiddivcrediaut});
}
</script>
<?php



//$jxc ->drawJavaScript(false, true);
$xHP->fin();
?>