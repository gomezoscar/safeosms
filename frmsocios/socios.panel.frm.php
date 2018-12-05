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
$xHP		= new cHPage("TR.Panel de Control de personas", HP_FORM);
$jxc 		= new TinyAjax();
$xLi		= new cSQLListas();
$ql			= new MQL();
$xImg		= new cHImg();
$xF			= new cFecha();
$xRuls		= new cReglaDeNegocio();
$xODT		= new cHDicccionarioDeTablas();
$xLog		= new cCoreLog();
$xCurrUsr	= new cSystemUser(); $xCurrUsr->init();

$UsarRedir	= $xRuls->getValorPorRegla($xRuls->reglas()->RN_USAR_REDIRECTS);		//regla de negocio
$UsarFotos	= $xRuls->getValorPorRegla($xRuls->reglas()->PERSONAS_USAR_FOTOS);		//regla de negocio
$SePuedenDel= $xRuls->getValorPorRegla($xRuls->reglas()->PERSONAS_SE_ELIMINAN);		//regla de negocio

$jsTabs		= "";
$idempresa	= 0;
$oficial 	= elusuario($iduser);
$idsocio 	= parametro("idsocio", false, MQL_INT); $idsocio 	= parametro("persona", $idsocio, MQL_INT); $idsocio 	= parametro("socio", $idsocio, MQL_INT);
$nuevo		= parametro("nuevo", false, MQL_INT);	//Indica si viene de un registro nuevo
$xJsB		= new jsBasicForm("extrasocios");

function jsaReVivienda($idsocio){
	$xLi		= new cSQLListas();
	
			
		$cTbl = new cTabla($xLi->getListadoDeDireccionesPorPer($idsocio), 0, "idtbllistaviviendaps");
		$cTbl->OButton("TR.Verificar", "jsVerificar(_REPLACE_ID_)", $cTbl->ODicIcons()->SALUD);
		
		$cTbl->addEditar();
		
		//$cTbl->addEliminar();

		
		$cTbl->setKeyField("idsocios_vivienda");
		$cTbl->setEventKey("var xPv=new PersVivGen();xPv.getVerVivienda");
			
		return $cTbl->Show();
}
function jsaReActividadE($idsocio){
	$ql		= new cSQLListas();
		
	$myCab = new cTabla($ql->getListadoDeActividadesEconomicas($idsocio),0, "idtbllistaactividadps");
	$myCab->addEditar(USUARIO_TIPO_OFICIAL_CRED);
	$myCab->addEliminar(USUARIO_TIPO_OFICIAL_CRED);
	$myCab->OButton("TR.Verificar", "jsVerificarAE(_REPLACE_ID_)", $myCab->ODicIcons()->SALUD);
	$myCab->setKeyField("idsocios_aeconomica");
	return  $myCab->Show();
}
function jsaReRelaciones($idsocio){
	//Checar compatibilidad numerica entre los dependientes economicos
	$xDic		= new cHTablaDic();
	
	$xSoc		= new cSocio($idsocio);
	$xSoc->init();
	$tbl0		= $xDic->getHGuardarRelacion($idsocio, "jsGetRelaciones", $xSoc->getEsPersonaFisica());
	
	$sqlL		= new cSQLListas();
	$cBenef		= new cTabla($sqlL->getListadoDeRelacionesPersonales($idsocio),0,"idtbllistarelsps");
	
	//$cBenef->addEditar();
	$cBenef->OButton("TR.BAJA", "var xRP=new PersRelGen();xRP.setBajaRelacion(" . HP_REPLACE_ID . ")", $cBenef->ODicIcons()->BAJA);
	//$cBenef->addEliminar();
	$cBenef->setFieldReplace("clave_de_persona", "_X_PERSONA_");
	$cBenef->OButton("TR.PANEL", "var xP=new PersGen();xP.goToPanel(_X_PERSONA_)", $cBenef->ODicIcons()->PERSONA);
	$cBenef->setOmitidos("domicilio");
	$cBenef->setOmitidos("curp");
	$cBenef->setKeyField("idsocios_relaciones");
	$cBenef->setOmitidos("ocupacion");
	if($xSoc->getEsPersonaFisica() == false){
		$cBenef->setOmitidos("consanguinidad");
	}
	
	$xLi	= new cSQLListas();
	$xT		= new cTabla($xLi->getListadoDeReferenciasBancarias($idsocio),0, "idtbllistarefsbancsps");
	$xT2	= new cTabla($xLi->getListadoDeReferenciasComerciales($idsocio),0,"idtbllistarefscomsps");
	
	$xT->setKeyTable("socios_relaciones");
	$xT->addEliminar();
	
	$xT2->setKeyTable("socios_relaciones");
	$xT2->addEliminar();
	
	
	
	$TBen	= $cBenef->Show("TR.REFERENCIAS_PERSONALES");
	$TBen	= ($cBenef->getRowCount() <=0) ? "" : $TBen ;
	
	
	$TBan	= $xT->Show("TR.REFERENCIAS_BANCARIAS");
	
	$TBan	= ($xT->getRowCount() <=  0) ? "" : $TBan;
	
	$TCom	= $xT2->Show("TR.REFERENCIAS_COMERCIALES");
	$TCom	= ($xT2->getRowCount() <= 0) ? "" : $TCom;
	
	return $tbl0 . $TBen . $TBan . $TCom ;
}

function jsaRePatrimonio($idsocio){
	$ql		= new cSQLListas();
	$myTab = new cTabla($ql->getListaDePatrimonioPorPersona($idsocio),0,"idtbllistapatrimps");
	$myTab->addEditar(USUARIO_TIPO_OFICIAL_CRED);
	$myTab->setKeyField("idsocios_patrimonio");
	
	return $myTab->Show();	
}

function jsaSetDocumentoVerificado(){ }
function jsaSetDocumentoFalso(){ }

function jsaValidarDocumentacion($persona){
	$xAml	= new cAMLPersonas($persona);
	$xAml->init($persona);
	$xAml->setVerificarDocumentosCompletos();
	$xAml->setVerificarDocumentosVencidos();
	return $xAml->getMessages(OUT_HTML);
}
function jsaValidarRiesgo($persona){
	$xAml	= new cAMLPersonas($persona);
	$xAml->init($persona);
	$xAml->setAnalizarNivelDeRiesgo();
	//$xAml->setVerificarDocumentosCompletos();
	//$xAml->setVerificarDocumentosVencidos();
	return $xAml->getMessages(OUT_HTML);
}

function jsaValidarPerfilTransaccional($persona){
	$xAml		= new cAMLPersonas($persona);
	$xAml->init();
	$validar	= false; //(MODO_DEBUG == true) ? true : false;
	$xAml->setVerificarPerfilTransaccional(false, $validar);
	$xAml->setVerificarOperacionesSemestrales();
	
	return $xAml->getMessages(OUT_HTML);
}

function jsaCumplimiento($idsocio){
	$xAl		= new cAml_alerts();
	$xlistas	= new cSQLListas();
	$sql		= $xlistas->getListadoDeAlertas(false, false, false, $idsocio);
	$xT			= new cTabla($sql,0, "idlistaalertaspldps");
	$xT->setKeyField( $xAl->getKey() );
	$xT->setKeyTable( $xAl->get() );
	//if(getEsModuloMostrado($tipo_de_usuario))
	if(getSePuedeMostrar(MMOD_AML, MQL_MOD)){
		$xT->addEditar();
	}
	if(getSePuedeMostrar(MMOD_AML, MQL_DEL)){
		$xT->addEliminar();
	}
	return $xT->Show();	
}


function jsaAddDescuento($idpersona, $descuento){
	$xSoc		= new cSocio($idpersona); $xSoc->init();
	$xSoc->setMontoAhorroPreferente($descuento);
	return $xSoc->getMessages();
}

/*function jsaAddDescuentoDesdeEmpresa($idpersona, $descuento){
	$xSoc		= new cSocio($idpersona);
	if($xSoc->init() == true){
		$xSoc->setMontoAhorroPreferente($descuento);
	}
	return $xSoc->getMessages();
}*/


function jsaSetEnviarParaAsociada($idpersona){
	$xSoc		= new cSocio($idpersona);
	if($xSoc->init() == true){
		$xSoc->getExportarAsociada(TPERSONAS_GENERALES);
		$xSoc->getExportarAsociada(TPERSONAS_DIRECCIONES);
		$xSoc->getExportarAsociada(TPERSONAS_ACTIVIDAD_ECONOMICA);
	}
	
	return $xSoc->getMessages(OUT_HTML);	
}

function jsaGetOperaciones($idpersona, $fecha){
	
}
function jsaGetListadoDeNominas($idempresa){
	$xEmp		= new cEmpresas($idEmpresa);
	$xF			= new cFecha();
	$ql			= new MQL();
	$xl			= new cSQLListas();	
}
/*function jsaActualizarEmpresa($idempresa){
	$xEmp	= new cEmpresas($idempresa);
	$xEmp->init();
	$xEmp->setActualizarPorPersona();
	return $xEmp->getMessages(OUT_HTML);
}*/

function jsaActualizarSucursal($idsucursal){
	$xSuc	= new cSucursal($idsucursal);
	if($xSuc->init() == true){
		$xSuc->setActualizarPorPersona();
	}
	return $xSuc->getMessages(OUT_HTML);
}
function jsaActualizarUsuario($idusuario){
	$xUser	= new cSystemUser($idusuario);	
	$xUser->setActualizarPorPersona();
	return $xUser->getMessages(OUT_HTML);
}
function jsaCambiarFiguraJuridica($idpersona){
	$xSoc	= new cSocio($idpersona);
	if($xSoc->init() == true){
		if($xSoc->getEsPersonaFisica() == false){
			$xSoc->setEsPersonaFisica();
		} else {
			$xSoc->setEsPersonaMoral();
		}
	}
}
$jxc ->exportFunction('jsaRePatrimonio', array('idsocio'), "#tab-patrimonio");
$jxc ->exportFunction('jsaReActividadE', array('idsocio'), "#tab-actividad");
$jxc ->exportFunction('jsaReRelaciones', array('idsocio'), "#tab-relaciones");
$jxc ->exportFunction('jsaCumplimiento', array('idsocio'), "#tab-cumplimiento");
$jxc ->exportFunction('jsaReVivienda', array('idsocio'), "#tab-domicilio");
$jxc ->exportFunction('jsaValidarDocumentacion', array('idsocio' ), "#idavisos");
$jxc ->exportFunction('jsaValidarRiesgo', array('idsocio' ), "#idavisos");
$jxc ->exportFunction('jsaValidarPerfilTransaccional', array('idsocio' ), "#idavisos");
$jxc ->exportFunction('jsaCambiarFiguraJuridica', array('idsocio'), "#idavisos");

$jxc ->exportFunction('jsaAddDescuento', array('idsocio', 'iddescuento'), "#idavisos");
//$jxc ->exportFunction('jsaAddDescuentoDesdeEmpresa', array('idmodificado', 'idcantidad'), "#idavisos");
$jxc ->exportFunction('jsaSetEnviarParaAsociada', array('idsocio'), "#idavisos");
//$jxc ->exportFunction('jsaActualizarEmpresa', array('idempresa' ), "#idavisos");
$jxc ->exportFunction('jsaActualizarSucursal', array('idsucursal'), "#idavisos");
$jxc ->exportFunction('jsaActualizarUsuario', array('idusuario'), "#idavisos");

$jxc ->process();

//$xHP->addJsFile("../jsrsClient.js");

echo $xHP->getHeader();

//echo $xJsB->setIncludeJQuery(); 

//$xJsB	= new jsBasicForm("extrasocios");
?>
<body>
<?php

if ( setNoMenorQueCero($idsocio) <= DEFAULT_SOCIO){
	$xFRM	= new cHForm("frmsociospanel", "socios.panel.frm.php","frmsociospanel");
	$xBtn	= new cHButton();
	$xTxt	= new cHText();
	
	if($UsarRedir == true){
		$xHP->goToPageX("../utils/frmbuscarsocio.php?next=panel");
	}
	
	$xFRM->setTitle( $xHP->getTitle() );
	
	$xFRM->addPersonaBasico();
	$xFRM->addSubmit();
	
	echo $xFRM->get();

} else {
	$xSoc 		= new cSocio($idsocio, true);
	if($xSoc->init() == true){
		getPersonaEnSession($idsocio);
		//$xFRM->addJsReload();
	}
	$xHTabs		= new cHTabs();
	$xBtn		= new cHButton("");
	$xFRM		= new cHForm("frmsociospanel", "","frmsociospanel");
	
	$xHSel		= new cHSelect();
	$xNotif		= new cHNotif();
	
	$xFRM->setTitle( $xHP->getTitle() );
	
	$xFRM->OButton("TR.Recargar", "jsRecargar()", $xFRM->ic()->RECARGAR, "", "blue");
	$xFRM->addHElem( $xSoc->getFicha(true) );


	
	if(getEsModuloMostrado(USUARIO_TIPO_CAJERO, MMOD_COLOCACION) ){ 
		//Agregar otra opciones
		$xFRM->OButton("TR.Actualizar Datos", "updateDat()", $xFRM->ic()->EDITAR, "edit-socio", "editar");
		
		if(PERSONAS_CONTROLAR_POR_EMPRESA == true AND MODULO_CAPTACION_ACTIVADO == true){
			$xFRM->addToolbar( $xBtn->getBasic("TR.Agregar Descuento Solicitado", "jsAddDescuento()", "dinero", "edit-descuento", false ) );
		}
		if(PERSONAS_COMPARTIR_CON_ASOCIADA == true){
			if($xSoc->getEsExportado() == false){
				$xFRM->OButton("TR.EXPORTAR", "jsSetEnviarParaAsociada()", $xBtn->ic()->EXPORTAR , "edit-aasoc", "yellow");
			}
		}
	}

	//===============================================================================	
	$setSql4	= $xLi->getListadoDeNotas($idsocio);
	$c4Tbl 		= new cTabla($setSql4,0, "idlistanotaspersps");
	
	$c4Tbl->setKeyField("idsocios_memo");
	$c4Tbl->addEliminar(USUARIO_TIPO_OFICIAL_CRED);
	$c4Tbl->addEditar(USUARIO_TIPO_OFICIAL_CRED);
	$c4Tbl->setEventKey("var xP=new PersGen();xP.getVerNota");
	$HNotas		= $c4Tbl->Show();
	if($c4Tbl->getRowCount()>0){ $xHTabs->addTab($xFRM->lang("NOTAS"), $HNotas, "tab-notas"); }
	
	$xHTabs->addTab("TR.DOMICILIO", "", "tab-domicilio" );
	$xHTabs->addTab(PERSONAS_TITULO_PARTES, "", "tab-relaciones");
	$xHTabs->addTab("TR.ACTIVIDAD_ECONOMICA", "", "tab-actividad" );
	$xHTabs->addTab("TR.PATRIMONIO", "", "tab-patrimonio");
	//=======================================================================
	$cnt		= "";
	$xB			= new cBases();
	$mems		= ($xSoc->getEsPersonaFisica() == true) ? $xB->getMembers_InArray(false, BASE_DOCTOS_PERSONAS_FISICAS) : $xB->getMembers_InArray(false, BASE_DOCTOS_PERSONAS_MORALES);
	$xTblD		= new cTabla($xLi->getListadoDePersonasDoctos($idsocio, true), 0, "idtbllistadoctosps");
	
	$xTblD->addEliminar(USUARIO_TIPO_GERENTE);
	
	$xTblD->setKeyField("clave");
	$xTblD->setKeyTable("personas_documentacion");
	$xTblD->setOmitidos("archivo_de_documento");
	
	$xTblD->OButton("TR.VER", "var xP=new PersGen();xP.getDocumento({id:" . HP_REPLACE_ID . "})", $xTblD->ODicIcons()->VER, "idview");
	
	$xHTabs->addTab("TR.DOCUMENTOS", $xTblD->Show()); //tabs
	
	if(MODULO_AML_ACTIVADO == true){
		if(getEsModuloMostrado(USUARIO_TIPO_OFICIAL_AML) == true){
			$xDiv3		= new cHDiv("tx1", "msgcumplimiento");
			if($xSoc->getEsGrupoSolidario() == false AND $xSoc->getEsEmpresaConConvenio() == false){
				$xFRM->OButton("TR.validar documentos", "jsaValidarDocumentacion()", $xFRM->ic()->VALIDAR, "cmdvalidadoc", "green" );
				$xFRM->OButton("TR.validar perfil_transaccional", "jsaValidarPerfilT()", $xFRM->ic()->VALIDAR, "cmdvalidaperfil", "green");
				$xFRM->OButton("TR.validar riesgo", "jsaValidarRiesgo()", $xFRM->ic()->VALIDAR, "cmdvalidariesgo", "green");
			}
			$xFRM->OButton("TR.Actualizar Nivel de Riesgo", "jsActualizarNivelDeRiesgo($idsocio)", $xFRM->ic()->RIESGO, "cmdactualizarriesgo");
			
			$xFRM->OButton("TR.Consulta en LISTAS", "var xAML = new AmlGen(); xAML.getConsultaListas($idsocio)", $xFRM->ic()->REGISTROS, "cmdconsultalistas");
	
			
			$xHTabs->addTab("TR.cumplimiento", $xDiv3->get(), "tab-cumplimiento"); //tab6
			$jsTabs	.= ",\n selected: 6\n";
		}
	}
	//========================== Datos de personas Morales.
	if($xSoc->getEsPersonaFisica() == false){
		$xFRM->OButton("TR.DATOS PERSONA_MORAL", "var xP=new PersGen();xP.goToDatosPM($idsocio, true)", $xFRM->ic()->LEGAL, "cmddatospm", "blue2");
	}
	//Arbol de relaciones y perfil transaccional
	if(MODULO_AML_ACTIVADO == true){
		$xFRM->OButton("TR.ARBOL_DE_RELACIONES", "jsSigmaRelaciones()", $xFRM->ic()->EXPORTAR);
		$xT		= new cTabla($xLi->getListadoDePerfil($idsocio), 0,"idtblarbolrelsps");
		$xT->addEliminar();
		$xHTabs->addTab("TR.perfil_transaccional", $xT->Show() );
		
		//Agregar Consulta Listas
		$ttl	= "";
		$xTLNI	= new cTabla($xLi->getListadoDePersonasConsultasLInt($idsocio),0,"idtbllistapersintsps");
		$xTLNI->setOmitidos("nombre");$xTLNI->setOmitidos("persona");$xTLNI->setOmitidos("observaciones"); $xTLNI->setTitulo("clave_interna", "CLAVE"); $xTLNI->setTitulo("estatus", "ESTATUSACTIVO");
		$ttl 	.= $xTLNI->Show("TR.LISTA_NEGRA INTERNA");
		$xTLBI	= new cTabla($xLi->getListadoDePersonasConsultasBInt($idsocio),0,"idtbllistapersbintps");
		$xTLBI->setOmitidos("nombre");$xTLBI->setTitulo("clave_de_motivo", "MOTIVO");
		
		$ttl 	.= $xTLBI->Show("TR.LISTA_OMITIDOS");
		$xHTabs->addTab("TR.LISTASINTERNAS", $ttl );
	}
	if(getEsModuloMostrado(USUARIO_TIPO_OFICIAL_CRED)){
		$xFRM->OButton("TR.Riesgo de Credito", "var xP= new PersGen();xP.getRiesgoDeCredito($idsocio)", $xFRM->ic()->RIESGO);
	}
//================= Empresa con Convenio
	if($xSoc->getEsEmpresaConConvenio(true) == true){

	}
	if($xSoc->getEsSucursal() == true){
		$xFRM->OButton("TR.Actualizar Sucursal", "jsaActualizarSucursal()", $xFRM->ic()->EJECUTAR);
		$xFRM->OHidden("idsucursal", $xSoc->getIDSucursalAsociada());
	}
	if($xSoc->getEsUsuario(true) == true){
		$xFRM->OButton("TR.Actualizar Usuario", "jsaActualizarUsuario()", $xFRM->ic()->EJECUTAR);
		$xFRM->OHidden("idusuario", $xSoc->getOUsuario()->getID());
		if($xSoc->getOUsuario()->getID() == getUsuarioActual()){
			$xFRM->OButton("TR.Actualizar password", "jsActualizarPassword($idsocio)", $xFRM->ic()->PASSWORD);
		}
	}

	//Agregar Creditos
	$InfoCreds			= "";
	$xTListaCreds		= new cTabla($xLi->getListadoDeCreditos($idsocio,false, false, false, "", true), 0, "idtbllistacredsps");
	$xTListaCreds->OButton("TR.Panel", "jsGoToPanelCredito(" . HP_REPLACE_ID . ")", $xTListaCreds->ODicIcons()->CONTROL);
	$xTListaCreds->OButton("TR.PLAN_DE_PAGOS", "var xC=new CredGen();xC.getImprimirPlanPagosPorCred(" . HP_REPLACE_ID . ")", $xTListaCreds->ODicIcons()->IMPRIMIR);
	$xTListaCreds->setColSum("monto");
	$xTListaCreds->setColSum("saldo");
	

	
	$LVig				= $xTListaCreds->Show("TR.VIGENTE");
	$InfoCreds			.= ($xTListaCreds->getRowCount()<= 0) ? "" : $LVig;
	
	//Creditos por Autorizar
	
	$LVig				= $xODT->getCreditosPorMinistrar(false, $idsocio,"TR.AUTORIZADO");
	$InfoCreds			.= ($xODT->getNumeroItems()<=0) ? "" : $LVig;
	
	$LVig				= $xODT->getCreditosPorAutorizar(false, $idsocio, "TR.SOLICITADO");
	$InfoCreds			.= ($xODT->getNumeroItems()<=0) ? "" : $LVig;
	//Creditos Pagados
	$xTListaCredsP		= new cTabla($xLi->getListadoDeCreditosPagados($idsocio, false, true), 0, "idlistacredspagsps");
	$xTListaCredsP->OButton("TR.Panel", "jsGoToPanelCredito(" . HP_REPLACE_ID . ")", $xTListaCredsP->ODicIcons()->CONTROL);
	//$xTListaCreds->OButton("TR.PLAN_DE_PAGOS", "var xC=new CredGen();xC.getImprimirPlanPagosPorCred(" . HP_REPLACE_ID . ")", $xTListaCreds->ODicIcons()->IMPRIMIR);
	$xTListaCredsP->setColSum("monto");
	$xTListaCredsP->setColSum("saldo");
	//$xTListaCredsP->setOmitidos("saldo");
	$xTListaCredsP->setOmitidos("vencimiento");
	
	$LVig				= $xTListaCredsP->Show("TR.PAGADO");
	$InfoCreds			.= ($xTListaCredsP->getRowCount()<= 0) ? "" : $LVig;
	
	$xHTabs->addTab("TR.Creditos", $InfoCreds );
	
	
	//Creditos Castigados
	$xTCredCast	= $xODT->getCreditosCastigados(false, $idsocio, "TR.CASTIGADOS");
	if($xODT->getNumeroItems()>0){
		$xHTabs->addTab("TR.CASTIGADOS", $xTCredCast );
	}
	
	//Creditos rechazados
	
	$xTCredRech	= $xODT->getCreditosRechazados(false, $idsocio, "TR.RECHAZADO");
	if($xODT->getNumeroItems()>0){
		$xHTabs->addTab("TR.RECHAZADO", $xTCredRech );
	}
	
	
	if(MODULO_CAPTACION_ACTIVADO == true){
		//agregar cuenta de ahorro
		$xTListaCapt	= new cTabla($xLi->getListadoDeCuentasDeCapt($idsocio),0, "idlistactasahorrops");
		$xTListaCapt->OButton("TR.Panel", "var xC= new CaptGen();xC.goToPanel(" . HP_REPLACE_ID . ")", $xFRM->ic()->CONTROL);
		$xHTabs->addTab("TR.Captacion", $xTListaCapt->Show() );
	}
	//Actualizar Descuentos
	$xDiv2				= new cHDiv("inv", "iddivdescuento");
	$xFRM10 			= new cHForm("frmdescuento");
	$xFRM10->addSubmit("", "jsGuardarDescuento()", "jsCancelarAccion()");
	$xFRM10->OMoneda("iddescuento", 0, "TR.Monto");
	//======================================== 			RECIBOS
	$xFRM->OButton("TR.ESTADO_DE_CUENTA OTROSINGRESOS", "var xP=new PersGen(); xP.getReportePagosNoDoc($idsocio)", $xFRM->ic()->REGISTROS);
	//Agregar Listado de Recibos
	$xLi->setInvertirOrden();
	$cTblx			= new cTabla($xLi->getListadoDeRecibosConDocto("", $idsocio),0,"idtbllistarecsps");
	$cTblx->setKeyField("idoperaciones_recibos");
	$cTblx->setTdClassByType();
	$cTblx->setEventKey("jsGoPanelRecibos");
	$cTblx->setFootSum(array(5 => "total"));
	$cTblx->setOmitidos("nombre");
	$cTblx->setOmitidos("socio");
	$cntOI	= $cTblx->Show();
	if($cTblx->getRowCount()>0){
		$xHTabs->addTab("TR.RECIBOS", $cntOI);
	}
	//======================================== 			Tabla de Operaciones
	$sql		= $xLi->getListadoDeOperaciones($idsocio);
	$cEdit		= new cTabla($sql,0, "idtbllistaopsps");
	$cEdit->addTool(SYS_UNO);
	$cEdit->addTool(SYS_DOS);
	$cEdit->setTdClassByType();
	$cEdit->setKeyField("idoperaciones_mvtos");
	$HOperaciones=$cEdit->Show();
	if($cEdit->getRowCount()>0){ $xHTabs->addTab("TR.Operaciones", $HOperaciones); }
	
	//======================================== AML

	/*Validacion*/
	if(MODO_DEBUG == true){
		$xHTabs->addTab("TR.Validacion", $xSoc->getValidacion(OUT_HTML));
		$xFRM->OButton("TR.Reporte SIC", "jsGetCirculoDeCredito()", $xBtn->ic()->REPORTE);
		
		//Acciones de Correccion
		if($xSoc->getEsPersonaFisica() == false){
			$xFRM->OButton("TR.CAMBIAR A PERSONA_FISICA", "jsaCambiarFiguraJuridica()", $xFRM->ic()->EXPORTAR, "", "red");
		} else {
			$xFRM->OButton("TR.CAMBIAR A PERSONA_MORAL", "jsaCambiarFiguraJuridica()", $xFRM->ic()->EXPORTAR, "", "red" );
		}
	}
	
	if((MODO_CORRECION == true OR MODO_MIGRACION == true OR MODO_DEBUG == true) OR (getUsuarioActual(SYS_USER_NIVEL) >= USUARIO_TIPO_GERENTE) ){
		$xStats			= new cPersonasEstadisticas($idsocio);
		$xStats->initDatosDeCredito(true);
		$xFRM->OButton("TR.BAJA PERSONA", "var xP=new PersGen();xP.setBaja($idsocio)", $xFRM->ic()->PARAR, "cmdbajapersona", "orange");
		if($xStats->getTotalCompromisos()== 0){
			if($SePuedenDel == true){
				$xFRM->OButton("TR.ELIMINAR PERSONA", "jsEliminarPersona($idsocio)", $xFRM->ic()->ELIMINAR, "cmdeliminarpersona", "red");
			}
		} else {
			//$xFRM->addAviso(, "", true, "warning");
			$xFRM->addTag("Compromisos #<strong>" . $xStats->getTotalCompromisos() . "</strong>.No Eliminar", "error");
			if(MODO_DEBUG == true){
				$xFRM->OButton("TR.ELIMINAR PERSONA", "jsEliminarPersona($idsocio)", $xFRM->ic()->ELIMINAR, "cmdeliminarpersona", "red");
			}
		}
		
		//Agregar Lista de Parecidos
		$sqlTT	= $xLi->getListadoDeBusquedaSocios($xSoc->getNombre(), $xSoc->getApellidoPaterno(), $xSoc->getApellidoMaterno(), "", "", $xSoc->getCodigo());
		$xTT2	= new cTabla($sqlTT, 0 ,"idtblistacoincidenciasps");
		$xTT2->OButton("TR.Unificar", "var xP=new PersGen();xP.setUnificar($idsocio, ". HP_REPLACE_ID .  ")", $xFRM->ic()->EXPORTAR);
		$xHTabs->addTab("TR.Validacion", $xTT2->Show());
	}
	//===================================== Oficial propietario
	$xUsr		= new cSystemUser($xSoc->getClaveDeUsuario());
	
	

	//====================================== Datos extranjero
	if($xSoc->getEsExtranjero() == true){
		$xFRM->OButton("TR.DATOS_EXTRANJEROS", "var xP=new PersGen();xP.setFormaDatosExt($idsocio)", $xFRM->ic()->GRUPO);
		if($xSoc->getNacionalidad() == ""){
			$xFRM->addJsInit("jsRequiereDatosExtranjero($idsocio);");
			//$xFRM->addJsInit("");
		}
	}
	$xDiv2->addHElem($xFRM10->get());
	//===================================== Recibos de Otros Ingresos
	$sql98		= "SELECT
			`operaciones_mvtos`.`idoperaciones_mvtos`     AS `clave`,
			`operaciones_recibos`.`idoperaciones_recibos` AS `recibo`,
			`operaciones_recibos`.`fecha_operacion`       AS `fecha`,
			`operaciones_tipos`.`descripcion_operacion`   AS `operacion`,
			`operaciones_recibos`.`tipo_pago`             AS `tipo_de_pago`,
			`operaciones_mvtos`.`afectacion_real`         AS `monto`
		FROM
			`operaciones_mvtos` `operaciones_mvtos` 
				INNER JOIN `operaciones_recibos` `operaciones_recibos` 
				ON `operaciones_mvtos`.`recibo_afectado` = `operaciones_recibos`.
				`idoperaciones_recibos` 
					INNER JOIN `operaciones_recibostipo` `operaciones_recibostipo` 
					ON `operaciones_recibos`.`tipo_docto` = `operaciones_recibostipo`.
					`idoperaciones_recibostipo` 
						INNER JOIN `operaciones_tipos` `operaciones_tipos` 
						ON `operaciones_mvtos`.`tipo_operacion` = `operaciones_tipos`.
						`idoperaciones_tipos` 
		WHERE
			(`operaciones_mvtos`.`socio_afectado` =$idsocio)  AND
			(`operaciones_tipos`.`recibo_que_afecta` =" . RECIBOS_TIPO_OINGRESOS . ") 
		ORDER BY
			`operaciones_recibos`.`fecha_operacion`";
	$xTT98	= new cTabla($sql98,1, "idtbllistarecsoingps");
	$xTT98->OButton("TR.RECIBO", "var xR=new RecGen();xR.formato(". HP_REPLACE_ID .  ")", $xFRM->ic()->IMPRIMIR);
	$tt98	= $xTT98->Show();
	if($xTT98->getRowCount()>0){
		$xHTabs->addTab("TR.OTROSINGRESOS", $tt98);
	}
	
	//===================================== APORTACIONES y CUOTAS
	if(PERSONAS_CONTROLAR_POR_APORTS == true ){
		//if($xSoc->getMembresiaDiaPag() == $xF->dia()){
		$xFRM->OButton("TR.COBRO MEMBRESIA", "var xP=new PersGen();xP.setCobroMembresia($idsocio," . $xF->mes() . ");", $xFRM->ic()->COBROS);
		$xTLC	= new cTabla($xLi->getListadoDePersonaPerfilCuotas($idsocio),0, "idtbllistapcuotasps");
		$xTLC->setOmitidos("clave_de_persona");
		$xTLC->setOmitidos("fecha_de_aplicacion");
		$xHTabs->addTab("TR.MEMBRESIA", $xTLC->Show());
		//}
	}
	//===================================== Lineas de Credito
	$xTBLC	= new cTabla($xLi->getListadoDeLineasDeCred($idsocio), 0,"idtbllistalineascredsps");
	//$xTBLC->setOmitidos("");
	$xTBLC->setOmitidos("clave_de_persona");
	$xTBLC->setOmitidos("nombre");
	$xTBLC->setOmitidos("estado");
	$xTBLC->setKeyField("idcreditos_lineas");
	
	$xTBLC->OButton("TR.PANEL", "var xC=new CredGen();xC.getPanelDeLinea({id:" . HP_REPLACE_ID . "})", $xFRM->ic()->CONTROL);
	$xTBLC->OButton("TR.REPORTE", "var xC=new CredGen();xC.getReporteDeLinea({id:" . HP_REPLACE_ID . "})", $xFRM->ic()->REPORTE);
	$ctblc	= $xTBLC->Show();
	if($xTBLC->getRowCount()>0){
		$xHTabs->addTab("TR.CREDITOS_LINEAS", $ctblc);
	}
	//===================================== Creditos Castigados Activos
	
	
	//===================================== Cotizaciones
	
	//===================================== Registro Nuevo
	if($nuevo == true){
		
	}
	//===================================== Tags
	
	if($xUsr->init() == true){
		$xFRM->addTag("Creado por : <strong>" . $xUsr->getAlias() . "</strong>", "notice");
		
	}
	$xEjec	= new cSystemUser($xSoc->getClaveDeEjecutivo()); 
	if($xEjec->init() == true){
		$xFRM->addTag("Ejecutivo : <strong>" . $xEjec->getAlias() . "</strong>", "notice");
	}
	
	
	if($xSoc->getPermisoParaOperar() == true){
		$xFRM->addPersonaComandos($idsocio);
	} else {
		$xFRM->addTag("Esta Persona : <strong>No Puede hacer operaciones</strong>", "error");
		//$xLog->add("ERROR\tEsta Persona esta en Baja\r\n");
		//TODO: Agregar reactivacion de personas
		//TODO: Agregar Razon de Baja
	}
	//Agregar Sucursal
	$xFRM->addTag("Sucursal : <strong>" . $xSoc->getSucursal() . "</strong>", "notice");
	if($xSoc->getEsEmpresaConConvenio() == true){
		$xFRM->addTag("Maneja <strong>Nominas</strong>", "warning");
	}
	if($xSoc->getEsExtranjero() == true){
		$xFRM->addTag("Es <strong>Extranjero</strong>", "warning");
	}
	if($xSoc->getEsPersonaPoliticamenteExpuesta() ==true){
		$xFRM->addTag("Es <strong>Politicamente Expuesta</strong>", "warning");
	}
	if($xSoc->getEsGrupoSolidario() == true){
		$xFRM->addTag("Es <strong>Grupo Solidario</strong>", "notice");
	}
	if($xSoc->getEsPersonaRiesgosa() == true){
		$xFRM->addTag("Es <strong>Persona Bloqueada</strong>", "error");
	}
	if($xSoc->getEsSucursal() == true){
		$xFRM->addTag("Es <strong>Sucursal</strong> Activa", "warning");
	}
	if($xSoc->getEsUsuario() == true){
		$xFRM->addTag("Es <strong>Usuario en Sistema</strong>", "warning");
	}
	if($xSoc->getAportacionesSociales()>0){
		$mny	= getFMoney($xSoc->getAportacionesSociales());
		$xFRM->addTag("Es <strong>$ $mny</strong>", "notice");
	}
	if($xSoc->getAlias() !== ""){
		$xFRM->addTag("Alias <strong>" . $xSoc->getAlias() . "</strong>", "notice");
	}
	$xFRM->addTag("Identificado con <strong>" . $xSoc->getClaveDeIdentificacion() . "</strong>", "notice");
	if($xSoc->getCreditoMaximo()>0){
		$mny	= getFMoney($xSoc->getCreditoMaximo());
		$xFRM->addTag("Credito Maximo <strong>$ $mny</strong>", "notice");
		
	}
	if($xSoc->getCreditosComprometidos()>0){
		$mny	= getFMoney($xSoc->getCreditosComprometidos());
		$xFRM->addTag("Creditos Avalados <strong>$ $mny</strong>", "warning");
	}
	$GFisicas	= $xSoc->getGarantiasFisicasDepositadas();
	if($GFisicas>0){
		$mny	= getFMoney($GFisicas);
		$xFRM->addTag("Garantias Reales: <strong>$ $mny</strong>", "success");
	}
	$GMonto		= $xSoc->getGarantiasLiquidasDepositadas();
	if($GMonto > 0){
		$mny	= getFMoney($GMonto);
		$xFRM->addTag("Garantia Liquida: <strong>$ $mny</strong>", "success");
	}
	//===================================== Agregar Fotos
	if($UsarFotos == true AND $xSoc->getEsPersonaFisica() == true){
		$xTT	= new cHTabla("idimagenes", "listado");

		$xFRM->OButton("TR.AGREGAR FOTOGRAFIA", "jsAddNewPhoto()", "fa-camera", "addnewphoto");
		$xHPers	= new cHPersona($xSoc->getClaveDePersona());
		$xTT->addTH("TR.FOTOGRAFIA");
		$xTT->addTH("TR.FIRMA");
		$xTT->initRow();
		$xTT->addTD($xHPers->getFotografia(), " style='max-width:33%;with:300px' ");
		$xTT->addTD($xHPers->getFirma(), " style='max-width:50%;width:300px;' ");
		$xTT->endRow();
		
		$xFRM->addHElem( $xTT->get() );
	}
	$xTTE	= new cTabla($xLog->getListadoDeEventosSQL($idsocio), 0, "idtbllistaeventossysps");
	if(MODO_DEBUG == false){
		$xTTE->setOmitidos("texto");$xTTE->setOmitidos("tipo");
	}
	$hhe	= $xTTE->Show("", true, "idlistaeventos");
	if($xTTE->getRowCount()>0){
		$xFRM->addHTML($xHTabs->addTab("TR.EVENTOS", $hhe));
	}
	//==================================== Personas COmpartidas
	if(PERSONAS_COMPARTIR_CON_ASOCIADA == true){
		$xTS	= new cTabla("", 0, "idtbllistashareps");
		$xTS->setSQL("SELECT   `personas_share`.`idpersonas_share` AS `clave`,`personas_share`.`persona_id` AS `persona`,`personas_share`.`personas_share_id` AS `compartida`,getFechaMXByInt(`personas_share`.`tiempo`) AS `fecha`, `personas_share`.`url_share` AS `origen` FROM     `personas_share`");
		$xTS->setOmitidos("persona");
		$xTS->setWithMetaData();
		$xTS->OButton("TR.ABRIR SHARE", "jsOpenShare(" .  HP_REPLACE_ID. ")", $xFRM->ic()->AUTOMAGIC, "idtopensharecmd");
		$hhe	= $xTS->Show("", true, "idsharelst");
		if($xTS->getRowCount()>0){
			
			$xFRM->addHTML($xHTabs->addTab("TR.SHARE", $hhe));
		}
	}
	//===================================== 
	$xFRM->addHTML($xHTabs->get());
	$xFRM->addHTML($xDiv2->get());
	
	
	$xFRM->OHidden("idsocio", $idsocio); $xFRM->OHidden("idmodificado", ""); $xFRM->OHidden("idcantidad", "0");

	$xFRM->addAviso($xLog->getMessages(), "idavisos");
	
	$xFRM->addJsInit("initComponents();");
	$xFRM->addCerrar();

	
	
	echo $xFRM->get();
}
?>
</body>
<script>
var mSocio		= <?php echo  ($idsocio === false) ? "0" : $idsocio; ?>;
var xG			= new Gen();
var xPG			= new PersGen();
var xRec		= new RecGen();




function initComponents(){
	jsaReVivienda();
}

if (mSocio != 0) {

session(ID_PERSONA, mSocio); //Asignar Socio en Session

$(function() {
	$( "#tab" ).tabs({
			select: function(event, ui){
				selected = ui.panel.id;
					switch (selected){
					case "tab-notas":
						
						break;
					case "tab-domicilio":
						jsaReVivienda();
						break;
					case "tab-relaciones":
						jsaReRelaciones();
						break;
					case "tab-actividad":
						jsaReActividadE();
						break;
					case "tab-patrimonio":
						jsaRePatrimonio();
						break;
					case "tab-cumplimiento":
						jsaCumplimiento();
						break;
				}
		    }<?php echo $jsTabs; ?>	
		});
});

}

function addPatrim(){
	var srURL = "../frmsocios/frmsociospatrimonio.php?socio=<?php echo $idsocio; ?>";
	xG.w({ url: srURL, tiny : true });
}
function updateDat(){
	var srUp = "../frmsocios/frmupdatesocios.php?persona=<?php echo $idsocio; ?>";
	xG.w({ url: srUp, tab: true });
}
function addHistorial(){
	var sDiv	= "<?php echo STD_LITERAL_DIVISOR; ?>";
	var srURL 	= "../frmsocios/frmhistorialdesocios.php?d=1" + sDiv + <?php echo $idsocio; ?> + sDiv + "1" + sDiv + "99" + sDiv + "NOTA_DEL_SOCIO" ;
	xG.w({ url: srURL, tiny : true });
}	
function jsVerificar(id){
	var URIL	= "../frmsocios/socios.verificacion.frm.php?t=d&s=" + mSocio +"&i=" + id;
	xG.w({ url: URIL, tiny : true });		
}
function jsVerificarAE(id){
	var URIL	= "../frmsocios/socios.verificacion.frm.php?t=t&s=" + mSocio +"&i=" + id;
	xG.w({ url: URIL, tiny : true });		
}
function jsUp(t, f, id) {
	var url = "../utils/frm8db7028bdcdf054882ab54f644a9d36b.php?t=" + t + "&f=" + f + "=" + id;
	xG.w({ url: url, tiny : true });
}
function jsDel(t, f, id) {
	var siXtar = confirm("Desea en Realidad Eliminar \n el Registro Seleccionado");
	if(siXtar==true){
		var sURL = "../utils/frm9d23d795f8170f495de9a2c3b251a4cd.php?t=" + t + "&f=" + f + "=" + id;
			delme = window.open(sURL, "", "width=300,height=300,scrollbars=yes,dependent");
			//delme.focus();
			document.getElementById("tr-" + t + "-" + id).innerHTML = "";
	} else {
			if( window.console ) { window.console.log( '' ); }
			window.statusText = "Operacion Cancelada";

	}
}
function jsAddDocumentos(){
	var sURL = "../frmsocios/personas_documentos.frm.php?persona=" + mSocio;
	xG.w({ url: sURL, tiny : true });
}
function jsToImage(uxl){
	var xrl		= "../frmsocios/documento.png.php?persona=" + uxl;
	xG.w({ url: xrl, tiny : true });  
}
function jsaValidarPerfilT(){	jsaValidarPerfilTransaccional();	}
function jsActualizarNivelDeRiesgo(id){	
	var xML = new AmlGen(); xML.goToCambiarNivel(id);
}
/*function jsModificarAhorro(evt, idpersona){
	if(flotante(evt.value) >= 0 ){
		$("#idmodificado").val(idpersona);
		$("#idcantidad").val(flotante(evt.value));
		var siguarda	= confirm("DESEA GUARDAR EL DESCUENTO PREFERENTE POR " + evt.value);
		if(siguarda){ jsaAddDescuentoDesdeEmpresa();	}
	}
}*/
function jsCancelarAccion(){ xG.cerrarDialogos(); }
function jsAddDescuento(){ getModalTip(window, $("#iddivdescuento"), xG.lang(["actualizar", "descuento"]));	}
function jsGuardarDescuento(){	jsaAddDescuento();	xG.postajax("jsCancelarAccion()"); }
/*

function jsGetCedulaDeAhorro(){
	var EmpG	= new EmpGen();
	var idemp	= $("#idempresa").val();
	EmpG.getCedulaAhorro(idemp);
}
function jsGetEmpresaCaptacion(){
	var EmpG	= new EmpGen();
	var idemp	= $("#idempresa").val();
	EmpG.getTablaDeCaptacion(idemp);
}*/
/*function jsCedulaAhorroExcel(){ 
var idemp	= $("#idempresa").val();
var xrl		= "../frmempresas/layout-cedula.frm.php?empresa=" + idemp;
xG.w({ url: xrl, tiny : true }); 	
}*/
function jsGetCirculoDeCredito(){
	var ff 		= window.prompt("Fecha de Corte:");
	var xrl		= "../rptlegal/circulo_de_credito.rpt.php?persona=" + mSocio  + "&fechafinal=" + ff;
	xG.w({ url: xrl, tab : true });  
}
function jsGetOperaciones(){ 	}
function jsGoToPanelCredito(idx){ var xCred = new CredGen(); xCred.goToPanelControl(idx); }
//function jsListaDeNominas(idnomina){ var EmpG	= new EmpGen(); EmpG.getOrdenDeCobranza(idnomina);	}
function jsRecargar(){ window.location = "socios.panel.frm.php?persona=" + mSocio; }


function jsGetRelaciones(){ jsaReRelaciones(); }
function jsSigmaRelaciones(){ 
	var xrl		= "../frmsocios/socios.relaciones.sigma.frm.php?persona=" + mSocio;
	xG.w({ url: xrl, tiny : true }); 	
}
function jsGoPanelRecibos(id){ xRec.panel(id); }
function jsEliminarPersona(id){
	xPG.eliminar(id);
}
function jsActualizarPassword(){ 
	var xrl		= "../frmsocios/socios.usuario.frm.php?persona=" + mSocio;
	xG.w({ url: xrl, tiny : true }); 	
}
function jsRequiereDatosExtranjero(id){
	xG.requiere({
		callback: function(){ 
			var xP=new PersGen();
			xP.setFormaDatosExt(id);
		},
		msg : 'PERSONA_FALTA_DEXTRA'
	});
}
function jsAddNewPhoto(){
	var xrl		= "../frmsocios/personas_documentos.frm.php?tipo=710&persona=" + mSocio;
	xG.w({ url: xrl, tiny : true }); 	
}
function jsSetEnviarParaAsociada(){
	xG.confirmar({msg: "MSG_CONFIRMA_ENVIO", callback : jsaSetEnviarParaAsociada});
}

function jsOpenShare(id){
	var dd 		= processMetaData("#tr-personas_share-" + id);
	var idpers 	= dd.compartida;
	var urlx 	= dd.origen;
	var ctx 	= "<?php echo $xCurrUsr->getCTX(); ?>";
	
	var sURL	= urlx + "frmsocios/socios.panel.frm.php?persona=" + idpers + "&ctx=" + ctx;
	//setLog(sURL);
	window.open(sURL, "socios-panel-" + idpers, "fullscreen=yes,scrollbars=yes");
	
	//xG.w({url: url + "frmsocios/socios.panel.frm.php?ctx=" + ctx + "&persona=" + idpers, tab:true });
}
</script>
<?php
//echo $xJsB->get();
$jxc->drawJavaScript(false, true);
if($idsocio>DEFAULT_SOCIO){
	$xHP->addReload();
}
?>
</html>