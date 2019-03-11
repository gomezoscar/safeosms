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
$xHP		= new cHPage("TR.Panel de Cuentas", HP_FORM);
$mSQL		= new cSQLListas();
$xHP->setIncludeJQueryUI();



$persona	= parametro("persona", DEFAULT_SOCIO, MQL_INT); $persona = parametro("socio", $persona, MQL_INT); $persona = parametro("idsocio", $persona, MQL_INT);
$credito	= parametro("credito", DEFAULT_CREDITO, MQL_INT); $credito = parametro("idsolicitud", $credito, MQL_INT); $credito = parametro("solicitud", $credito, MQL_INT);
$cuenta		= parametro("cuenta", 0, MQL_INT); $cuenta = parametro("idcuenta", $cuenta, MQL_INT);
$jscallback	= parametro("callback"); $tiny = parametro("tiny"); $form = parametro("form"); $action = parametro("action", SYS_NINGUNO);

$xHP->init();
$xJs				= new jsBasicForm("frm");
$xJs->setIncludeJQuery();

$xFRM		= new cHForm("frm", "cuentas.panel.frm.php");
$xBtn		= new cHButton();		
$xTxt		= new cHText();
$xDate		= new cHDate();
$xSel		= new cHSelect();

$xFRM->setTitle( $xHP->getTitle() );

if($cuenta <= 0){
	
	$xFRM->addJsBasico(iDE_CAPTACION);
	$xFRM->addCuentaCaptacionBasico(true);
	$xFRM->addSubmit();
	
} else {
	$xFRM->addCerrar();
	$xCta	= new cCuentaDeCaptacion($cuenta);
	
	$xFRM->addRefrescar("jsRecargar($cuenta)");
	//Actualizar Saldo
	$xCta->setCuandoSeActualiza();
	//
	$xCta->init();
	$xFRM->OHidden("idcuenta", $cuenta);
	$xFRM->OHidden("idcuentacaptacion", $cuenta);

	/*$xFRM->OButton("TR.Actualizar Saldo", "jsaSetActualizar()", $xFRM->ic()->SALDO);*/
	$xFRM->addHTML($xCta->getFicha(true, "", true) );
	
	
	//$xFRM->addToolbar( $xBtn->getBasic("TR.refrescar", "jsRecargar()", "refrescar", "refrescar", false ) );
	//$xFRM->addToolbar( $xBtn->getBasic("TR.imprimir contrato", "jsRecargar()", "refrescar", "refrescar", false ) );
	
	$xFRM->addCaptacionComandos($cuenta);
	
	$xHTabs	= new cHTabs();
	$mSQL->setInvertirOrden();
	
	$cTblx	= new cTabla($mSQL->getListadoDeRecibosV101("", $xCta->getClaveDePersona(), $xCta->getNumeroDeCuenta() ));
	$cTblx->setKeyField("idoperaciones_recibos");
	$cTblx->setTdClassByType();
	
	$cTblx->setOmitidos("socio");
	$cTblx->setOmitidos("nombre");
	$cTblx->setOmitidos("documento");
	$cTblx->setOmitidos("operacion");
	$cTblx->setTitulo("periodo", "CAPTPER");
	$cTblx->setColSum("total");
	$cTblx->setEventKey("jsGoPanelRecibos");
	$xHTabs->addTab("TR.RECIBOS", $cTblx->Show());
	//Operaciones
	$mSQL->setInvertirOrden();
	$xTBM	= new cTabla($mSQL->getListadoDeOperaciones(false, $xCta->getNumeroDeCuenta()));
	$xTBM->setOmitidos("socio");
	$xTBM->setOmitidos("documento");
	$xTBM->setOmitidos("operacion");
	$xTBM->setColSum("monto");
	$xTBM->setTitulo("periodo", "CAPTPER");
	$xTBM->setKeyField("idoperaciones_mvtos");
	$xTBM->setKeyTable("operaciones_mvtos");
	
	
	//$xTBM->addEditar();
	//$xTBM->addEliminar();
	
	$xHTabs->addTab("TR.OPERACIONES", $xTBM->Show());
	$numeroops	= $xTBM->getRowCount();
	//Saldos Promedios
	$mSQL->setInvertirOrden();
	$xTBS	= new cTabla($mSQL->getListadoDeSDPMCaptacion($xCta->getNumeroDeCuenta()));
	$xTBS->setKeyField("idcaptacion_sdpm_historico");
	$xTBS->setKeyTable("captacion_sdpm_historico");
	$xTBS->setOmitidos("numero_de_socio");
	
	//$xTBS->addEditar();
	//$xTBS->addEliminar();
	
	$xHTabs->addTab("TR.SALDOS", $xTBS->Show());	
	$xFRM->addHTML( $xHTabs->get() );
	
	$xFRM->addAviso("", "idmsg");

	
	/*
	 * <fieldset>
				<legend>Barra de Acciones</legened>
					<table  align='center'>
						<tr>
							<td>
								<input type='button' name='printcontrato' value='IMPRIMIR CONTRATO DE CAPTACION' onClick='printrec();'>
							</td>
							<td>
								<input type='button' name='command' value='Ver/Guardar Firmas' onClick='captura_firmas();'>
							</td>
							<td>
								<input type='button' name='cmd_edit' value='Editar Datos del Contrato' onClick='feditar_cuenta();'>
							</td>
							<td>
								<a class='button' name='cmd_printMandato'  onClick='printMandato();'>Imprimir Mandato</a>
							</td>
					</table>
			</fieldset>
	 * */
	$xFRM->OButton("TR.ACTUALIZAR", "jsEditar($cuenta);", $xFRM->ic()->EDITAR, "idcmdedit", "yellow");
}

echo $xFRM->get();


//echo $xJs->get();
?>
<script>
var xRec	= new RecGen();
var xCred	= new CredGen();
var xCta	= new CaptGen();
var xG		= new Gen();

function jsGoPanelRecibos(id){ var xRec = new RecGen(); xRec.panel(id); }
function jsRecargar(idcuenta){ 
	xG.go({url: "../frmcaptacion/cuentas.panel.frm.php?cuenta=" + idcuenta});
}
function jsEditar(idcuenta){
	xG.w({tiny:true, url: "../frmcaptacion/cuentas.panel.edicion.frm.php?cuenta=" + idcuenta});
}
</script>
<?php

$xHP->fin();
?>