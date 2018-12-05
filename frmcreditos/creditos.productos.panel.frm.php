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
$xHP		= new cHPage("TR.DATOS DE PRODUCTOS DE CREDITOS", HP_FORM);
$jxc 		= new TinyAjax();

$clave			= parametro("id", 0, MQL_INT); $clave		= parametro("clave", $clave, MQL_INT);$clave		= parametro("producto", $clave, MQL_INT);
$todos			= parametro("todos", false, MQL_BOOL);


$xHP->setIncludeJQueryUI();
function jsaLoadOtrosDatos($idproducto){
	$xLi		= new cSQLListas();
	$xT			= new cTabla($xLi->getListadoDeOtrosDatosPorProductoCred($idproducto, true));
	$xObj		= new cCreditos_productos_otros_parametros();
	$xT->setKeyField($xObj->getKey());
	$xT->setKeyTable($xObj->get());
	
	$xT->addEditar();
	$xT->addEliminar();
	return $xT->Show("",true, "tblListaOtrosDatos");
}
function jsaLoadOtrosCargos($idproducto){
	$xLi		= new cSQLListas();
	$xT	= new cTabla($xLi->getListadoDeCargosPorProductoCred($idproducto, true, true));
	$xT->setKeyField("idcreditos_productos_costos");
	$xT->setKeyTable("creditos_productos_costos");
	$xT->OButton("TR.EDITAR", "jsOtrosCargosEditar(" . HP_REPLACE_ID . ")", $xT->ODicIcons()->EDITAR);
	//$xT->addEditar();
	$xT->addBaja();
	if(MODO_DEBUG){
		$xT->addEliminar();
	}
	return $xT->Show("",true, "tblListaCostos");
}
function jsaLoadEtapas($idproducto){
	$xLi		= new cSQLListas();
	$xT	= new cTabla($xLi->getListadoDeEtapasPorProductoCred($idproducto));
	$xT->setKeyField("");
	$xT->setKeyTable("");
	//$xT->OButton("TR.EDITAR", "jsOtrosCargosEditar(" . HP_REPLACE_ID . ")", $xT->ODicIcons()->EDITAR);
	//$xT->addEditar();
	$xT->setOmitidos("producto");
	
	$xT->addEliminar();
	return $xT->Show("",true, "tblListaEtapas");
}
function jsaLoadRequisitos($idproducto){
	$xLi		= new cSQLListas();
	$xT	= new cTabla($xLi->getListadoDeRequisitosPorProductoCred($idproducto));
	$xT->setKeyField("");
	$xT->setKeyTable("");
	//$xT->OButton("TR.EDITAR", "jsOtrosCargosEditar(" . HP_REPLACE_ID . ")", $xT->ODicIcons()->EDITAR);
	//$xT->addEditar();
	$xT->addEliminar();
	return $xT->Show("",true, "tblListaRequisitos");
}
function jsaLoadPromociones($idproducto){
	$xLi	= new cSQLListas();
	$xT		= new cTabla($xLi->getListadoDePromosPorProductoCred($idproducto));
	$xT->setKeyField("");
	$xT->setKeyTable("");
	$xT->OButton("TR.EDITAR", "jsPromocionesEditar(" . HP_REPLACE_ID . ")", $xT->ODicIcons()->EDITAR);
	//$xT->addEditar();
	$xT->addEliminar();
	return $xT->Show("",true, "tblListaPromos");
}

function jsaLoadReglas($idproducto){
	$xLi	= new cSQLListas();
	$xT		= new cTabla($xLi->getListadoDeReglasPorProductoCred($idproducto));
	$xT->setOmitidos("producto");
	$xT->setKeyField("");
	$xT->setKeyTable("");
	$xT->OButton("TR.EDITAR", "jsReglasEditar(" . HP_REPLACE_ID . ")", $xT->ODicIcons()->EDITAR);
	//$xT->addEditar();
	$xT->addEliminar();
	return $xT->Show("",true, "tblListaPromos");
}


$jxc ->exportFunction('jsaLoadOtrosDatos', array('idproducto'), "#iddivotrosdatos");
$jxc ->exportFunction('jsaLoadOtrosCargos', array('idproducto'), "#iddivotroscargos");
$jxc ->exportFunction('jsaLoadEtapas', array('idproducto'), "#iddivetapas");
$jxc ->exportFunction('jsaLoadRequisitos', array('idproducto'), "#iddivrequisitos");
$jxc ->exportFunction('jsaLoadPromociones', array('idproducto'), "#iddivpromociones");
$jxc ->exportFunction('jsaLoadReglas', array('idproducto'), "#iddivreglas");

$jxc ->process();

$persona	= parametro("persona", DEFAULT_SOCIO, MQL_INT); $persona = parametro("socio", $persona, MQL_INT); $persona = parametro("idsocio", $persona, MQL_INT);
$credito	= parametro("credito", DEFAULT_CREDITO, MQL_INT); $credito = parametro("idsolicitud", $credito, MQL_INT); $credito = parametro("solicitud", $credito, MQL_INT);
$cuenta		= parametro("cuenta", DEFAULT_CUENTA_CORRIENTE, MQL_INT); $cuenta = parametro("idcuenta", $cuenta, MQL_INT);
$jscallback	= parametro("callback"); $tiny = parametro("tiny"); $form = parametro("form"); $action = parametro("action", SYS_NINGUNO);

$xHP->init();

$xFRM		= new cHForm("frm", "./");
$xSel		= new cHSelect();
$xTab		= new cHTabs();
$xFRM->setTitle($xHP->getTitle());
$xFRM->OButton("TR.Datos Generales", "jsGoToGeneral()", "ejecutar");
$xFRM->OButton("TR.Tasas", "jsGoToTasas()", "tasa");
$xFRM->OButton("TR.Dias", "jsGoToDias()", "fecha");
$xFRM->OButton("TR.cantidades", "jsGoToCantidades()", "moneda");
$xFRM->OButton("TR.Garantias", "jsGoToGarantias()", "garantia");



$xFRM->OButton("TR.Comisiones", "jsGoToComisiones()", "dinero");
$xFRM->OButton("TR.Permisos", "jsGoToPermisos()", "permisos");
$xFRM->OButton("TR.Scripting", "jsGoToScript()", "codigo");

$xFRM->OButton("TR.Contabilidad de Capital", "jsGoToContableCapital()", "contabilidad");
$xFRM->OButton("TR.Contabilidad de Intereses", "jsGoToContableInteres()", "contabilidad");

$xFRM->OButton("TR.Duplicar", "jsClonarProducto()", $xFRM->ic()->EJECUTAR, "idduplicar", "blue2");

$xFRM->OButton("TR.Otros Cargos", "jsOtrosCargos()", $xFRM->ic()->CONTROL);
$xFRM->OButton("TR.Otros parametros", "jsOtrosParametros()", $xFRM->ic()->CONTROL);

$xFRM->OButton("TR.Requisitos", "jsRequisitos()", $xFRM->ic()->CONTROL);
$xFRM->OButton("TR.Reglas", "jsReglas()", $xFRM->ic()->CONTROL);

$xFRM->OButton("TR.Etapas", "jsEtapas()", $xFRM->ic()->CONTROL);
$xFRM->OButton("TR.Promociones", "jsPromociones()", $xFRM->ic()->CONTROL);
$xFRM->OButton("TR.FORMS_Y_DOCS", "jsFormatos()", $xFRM->ic()->FORMATO);
$xFRM->addCerrar();

$xProd	= new cProductoDeCredito($clave);

if($xProd->init() == true){
	$xFRM->OHidden("idproducto", $clave);
	$xFRM->addHElem( $xProd->getFicha() );
} else {
	$xSProd		= $xSel->getListaDeProductosDeCredito("", false, true);
	if($todos == true){
		$xSProd	= $xSel->getListaDeProductosDeCredito();
	}
	$xSProd->addEvent("onblur", "jsLoadInit()");
	$lbl	= $xSProd->getLabel();
	$xSProd->setLabel("");
	$xFRM->addDivSolo( $lbl, $xSProd->get(false), "tx14", "tx34" );
}
$xFRM->addJsInit("jsLoadInit();");


$xTab->addTab("TR.OTROS CARGOS", "<div id='iddivotroscargos'></div>");
$xTab->addTab("TR.REGLAS", "<div id='iddivreglas'></div>");

$xTab->addTab("TR.OTROS DATOS", "<div id='iddivotrosdatos'></div>");
$xTab->addTab("TR.ETAPA", "<div id='iddivetapas'></div>");
$xTab->addTab("TR.REQUISITOS", "<div id='iddivrequisitos'></div>");
$xTab->addTab("TR.PROMOCIONES", "<div id='iddivpromociones'></div>");

$xFRM->addHTML($xTab->get() );
echo $xFRM->get();



?>
<script >
var xG		= new Gen();
function jsLoadInit(){
	jsaLoadOtrosCargos();
	jsaLoadOtrosDatos();
	jsaLoadEtapas();
	jsaLoadRequisitos();
	jsaLoadPromociones();
	jsaLoadReglas();

}
function jsGoToGeneral(){jsLoadObject("generales"); }
function jsGoToTasas(){jsLoadObject("tasas"); }
function jsGoToDias(){jsLoadObject("dias"); }
function jsGoToCantidades(){jsLoadObject("cantidades"); }
function jsGoToPermisos(){ jsLoadObject("permisos"); }
function jsGoToScript(){ jsLoadObject("codigo"); }
function jsGoToComisiones(){ jsLoadObject("comisiones"); }
function jsGoToGarantias(){ jsLoadObject("garantias"); }
function jsGoToContableCapital(){ jsLoadObject("contablecapital"); }
function jsGoToContableInteres(){ jsLoadObject("contableinteres"); }
function jsLoadObject(tema){
	var idproducto = $("#idproducto").val();
	sURI	= "../frmcreditos/creditos.productos.frm.php?tema="  + tema  + "&id=" + idproducto; xG.w({url: sURI, tiny : true});
}
function jsClonarProducto(){
	var idproducto = $("#idproducto").val();
	sURI	= "../frmcreditos/creditos.productos.add.frm.php?producto=" + idproducto;
	 xG.w({url: sURI, tiny : true, w: 400, callback: jsRecargar});
}
function jsRecargar(){ window.location = "frmdatos_de_convenios_de_creditos.xul.php"; }
function jsOtrosParametros(){ var idproducto = $("#idproducto").val(); xG.w({url: "../frmcreditos/creditos.productos.otros-datos.frm.php?producto=" + idproducto, tiny : true, w: 600, callback:jsaLoadOtrosDatos}); }
function jsOtrosCargos(){ var idproducto = $("#idproducto").val(); xG.w({url: "../frmcreditos/creditos.productos.otros-cargos.frm.php?producto=" + idproducto, tiny : true, w: 600, callback: jsaLoadOtrosCargos}); }
function jsOtrosCargosEditar(id){ var idproducto = $("#idproducto").val(); xG.w({url: "../frmcreditos/creditos.productos.otros-cargos.frm.php?producto=" + idproducto + "&clave=" + id, tiny : true, w: 600, callback: jsaLoadOtrosCargos}); }



function jsRequisitos(){ var idproducto = $("#idproducto").val();xG.w({url: "../frmcreditos/creditos.productos.requisitos.frm.php?producto=" + idproducto, tiny : true, w: 600, callback: jsaLoadRequisitos}); }
function jsEtapas(){ var idproducto = $("#idproducto").val();xG.w({url: "../frmcreditos/creditos.productos.etapas.frm.php?producto=" + idproducto, tiny : true, w: 600, callback: jsaLoadEtapas}); }

function jsReglas(){
	var idproducto = $("#idproducto").val();
	xG.w({url: "../frmcreditos/creditos.productos.reglas.new.frm.php?producto=" + idproducto, tiny : true, w: 600, callback: jsaLoadEtapas}); 
}

function jsPromociones(){ var idproducto = $("#idproducto").val();xG.w({url: "../frmcreditos/creditos.productos.promociones.frm.php?producto=" + idproducto, tiny : true, w: 600, callback: jsaLoadPromociones}); }

function jsPromocionesEditar(id){ 
	var idproducto = $("#idproducto").val(); 
	xG.w({
		url: "../frmcreditos/creditos.productos.promociones.editar.frm.php?producto=" + idproducto + "&clave=" + id, 
		tiny : true, w: 600, callback: jsaLoadOtrosCargos
		}); 
}

function jsEtapasEditar(id){ 
	var idproducto = $("#idproducto").val(); 
	xG.w({
		url: "../frmcreditos/creditos.productos.etapas.editar.frm.php?producto=" + idproducto + "&clave=" + id, 
		tiny : true, w: 600, callback: jsaLoadOtrosCargos
		}); 
}

function jsReglasEditar(id){ 
	var idproducto = $("#idproducto").val(); 
	xG.w({
		url: "../frmcreditos/creditos.productos.reglas.edit.frm.php?producto=" + idproducto + "&clave=" + id, 
		tiny : true, w: 600, callback: jsaLoadReglas
		});
}

function jsFormatos(){ var idproducto = $("#idproducto").val();
	xG.w({url: "../frmutils/contratos-editor.frm.php?producto=" + idproducto, tab: true }); 
}

</script>
<?php
$jxc ->drawJavaScript(false, true);
$xHP->fin();
?>