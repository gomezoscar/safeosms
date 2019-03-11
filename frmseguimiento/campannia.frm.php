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
$xHP		= new cHPage("TR.CAMPANNIA", HP_FORM);
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



$xFRM		= new cHForm("frmsegcampannia", "./");
$xSel		= new cHSelect();
$xFRM->setTitle($xHP->getTitle());





/* ===========		GRID JS		============*/

$xHG	= new cHGrid("iddivcampannia",$xHP->getTitle());

$xHG->setSQL("SELECT * FROM `mercadeo_campana` LIMIT 0,100");
$xHG->addList();
$xHG->addKey("idmercadeo_campana");
$xHG->col("nombre", "TR.NOMBRE", "10%");
$xHG->col("fecha_inicial", "TR.FECHA INICIAL", "10%");
$xHG->col("fecha_final", "TR.FECHA FINAL", "10%");
$xHG->col("oficial", "TR.OFICIAL", "10%");
$xHG->col("idusuario", "TR.IDUSUARIO", "10%");

$xHG->OToolbar("TR.AGREGAR", "jsAdd()", "grid/add.png");
$xHG->OButton("TR.EDITAR", "jsEdit('+ data.record.idmercadeo_campana +')", "edit.png");
$xHG->OButton("TR.ELIMINAR", "jsDel('+ data.record.idmercadeo_campana +')", "delete.png");
$xFRM->addHElem("<div id='iddivcampannia'></div>");
$xFRM->addJsCode( $xHG->getJs(true) );
echo $xFRM->get();
?>
<script>
var xG	= new Gen();
function jsEdit(id){
	xG.w({url:"../frmseguimiento/campannia.edit.frm.php?clave=" + id, tiny:true, callback: jsLGiddivcampannia});
}
function jsAdd(){
	xG.w({url:"../frmseguimiento/campannia.new.frm.php?", tiny:true, callback: jsLGiddivcampannia});
}
function jsDel(id){
	xG.rmRecord({tabla:"mercadeo_campana", id:id, callback:jsLGiddivcampannia});
}
</script>
<?php
	






//$jxc ->drawJavaScript(false, true);
$xHP->fin();
?>