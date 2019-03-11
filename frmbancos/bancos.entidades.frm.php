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
$xHP		= new cHPage("TR.Catalogo de Bancos", HP_FORM);
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



$xFRM		= new cHForm("frmbancosentidades", "./");
$xSel		= new cHSelect();
$xFRM->setTitle($xHP->getTitle());
$xFRM->addCerrar();

/* ===========		GRID JS		============*/

$xHG	= new cHGrid("iddiv",$xHP->getTitle());

$xHG->setSQL("SELECT   `bancos_entidades`.`idbancos_entidades` AS `clave`,
         `bancos_entidades`.`nombre_de_la_entidad` AS `nombre`,
         `bancos_entidades`.`nombre_corto` AS `alias`,
         `bancos_entidades`.`clave_alfanumerica`,
         `personas_domicilios_paises`.`nombre_oficial` AS `pais`
FROM     `personas_domicilios_paises` 
INNER JOIN `bancos_entidades`  ON `personas_domicilios_paises`.`clave_de_control` = `bancos_entidades`.`pais_de_origen` ");

$xHG->addList();
$xHG->setOrdenar();

$xHG->addKey("idbancos_entidades");
$xHG->col("clave", "TR.CLAVE", "10%");
$xHG->col("nombre", "TR.NOMBRE", "40%");
$xHG->col("alias", "TR.ALIAS", "20%");
$xHG->col("pais", "TR.PAIS", "20%");



$xHG->OToolbar("TR.AGREGAR", "jsAdd()", "grid/add.png");
//$xHG->OButton("TR.EDITAR", "jsEdit('+ data.record.idbancos_entidades +')", "edit.png");
//$xHG->OButton("TR.ELIMINAR", "jsDel('+ data.record.idbancos_entidades +')", "delete.png");

$xFRM->addHElem("<div id='iddiv'></div>");
$xFRM->addJsCode( $xHG->getJs(true) );
echo $xFRM->get();
?>
<script>
var xG	= new Gen();
function jsEdit(id){
	xG.w({url:"../frm/bancos-entidades.edit.frm.php?clave=" + id, tiny:true, callback: jsLGiddiv});
}
function jsAdd(){
	xG.w({url:"../frm/bancos-entidades.new.frm.php?", tiny:true, callback: jsLGiddiv});
}
function jsDel(id){
	xG.rmRecord({tabla:"bancos_entidades", id:id, callback:jsLGiddiv});
}
</script>
<?php


//$jxc ->drawJavaScript(false, true);
$xHP->fin();


?>
