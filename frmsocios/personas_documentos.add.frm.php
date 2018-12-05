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
$xHP		= new cHPage("TR.CARGA DE DOCUMENTO", HP_FORM);
$xDoc		= new cDocumentos();
$xF			= new cFecha();
$xLi		= new cSQLListas();
$xPDoc		= new cPersonasDocumentacion();

$DDATA		= $_REQUEST;
//$persona	= ( isset($DDATA["persona"]) ) ? $DDATA["persona"] : DEFAULT_SOCIO;
//$action		= ( isset($DDATA["action"]) ) ? $DDATA["action"] : SYS_CERO;

$persona		= parametro("persona", DEFAULT_SOCIO, MQL_INT); $persona = parametro("socio", $persona, MQL_INT); $persona = parametro("idsocio", $persona, MQL_INT);
$jscallback		= parametro("callback"); $tiny = parametro("tiny"); $form = parametro("form"); $action = parametro("action", SYS_NINGUNO);$action	= strtolower($action);
$observaciones	= parametro("idobservaciones"); $observaciones	= parametro("observaciones", $observaciones);

$idcontrato		= parametro("idcontrato", 0, MQL_INT); $idcontrato		= parametro("contrato", $idcontrato, MQL_INT);
$tipodedocto	= parametro("idtipodedocto", 0, MQL_INT); $tipodedocto	= parametro("tipo", $tipodedocto, MQL_INT);

$credito		= parametro("credito", DEFAULT_CREDITO, MQL_INT); $credito = parametro("idsolicitud", $credito, MQL_INT); $credito = parametro("solicitud", $credito, MQL_INT);
$cuenta			= parametro("cuenta", DEFAULT_CUENTA_CORRIENTE, MQL_INT); $cuenta = parametro("idcuenta", $cuenta, MQL_INT);



//$jxc = new TinyAjax();
//$jxc ->exportFunction('datos_del_pago', array('idsolicitud', 'idparcialidad'), "#iddatos_pago");
//$jxc ->process();

$xHP->init();

//$jsb		= new jsBasicForm("frmdocumentos");
//$jxc ->drawJavaScript(false, true);

$ByType			= "";
if($persona<= DEFAULT_SOCIO AND $credito> DEFAULT_CREDITO){
	$xCred		= new cCredito($credito);
	if($xCred->init() == true){
		$idcontrato	= $credito;
		$persona	= $xCred->getClaveDePersona();
	}
}
if($persona > DEFAULT_SOCIO){
	$xSoc	= new cSocio($persona);
	$xSoc->init();
	$ByType	= ($xSoc->getEsPersonaFisica() == true) ? BASE_DOCTOS_PERSONAS_FISICAS : BASE_DOCTOS_PERSONAS_MORALES;
}


$xFRM	= new cHForm("frmfirmas", "personas_documentos.add.frm.php?action=" . SYS_UNO . "&persona=$persona");
$xFRM->setEnc("multipart/form-data");
$xFRM->setTitle($xHP->getTitle());

$xBtn	= new cHButton();
$xTxt	= new cHText();
$xTxt2	= new cHText();
$xTxtF	= new cHText();
$xSel	= new cHSelect();
$xImg	= new cHImg();

$xFRM->setNoAcordion();
$xFRM->setTitle($xHP->getTitle());

if($action == SYS_NINGUNO){
	$xFRM->addGuardar();
	
	$xFRM->addSeccion("iddotros", "TR.DATOS");
	if($tipodedocto == $xPDoc->TIPO_FOTO){
		$xFRM->OHidden("idtipodedocto", $tipodedocto);
		$xFRM->OHidden("idfechacarga", false);
		$xFRM->OHidden("idnumeropagina", 0);
		$xFRM->OHidden("idcontrato",0);
		$xFRM->setTitle($xFRM->getT("TR.FOTOGRAFIA"));
	} else {
		$xFRM->ODate("idfechacarga", false, "TR.FECHA_DE_EMISION");
		
		if($tipodedocto>0){
			$xFRM->OHidden("idtipodedocto", $tipodedocto);
			$xTD	= new cPersonasDocumentacionTipos($tipodedocto); $xTD->init();
			$xFRM->ODisabled("idtdoc", $xTD->getNombre(), "TR.TIPO_DE DOCUMENTO");			
			
			$xFRM->setTitle($xTD->getNombre());
		} else {
			$xFRM->addHElem( $xSel->getTiposDeDoctosPersonalesArch("", $ByType, $xSoc->getClaveDePersona())->get(true) );
		}
		
		
		$xFRM->OText_13("idnumeropagina", 0, "TR.PAGINA");

		if($idcontrato>0){
			$xFRM->OHidden("idcontrato", $idcontrato);
			$xFRM->addTag($xFRM->getT("TR.CONTRATO") . " : $idcontrato");
		} else {
			$xSelCP			= $xSel->getListaDeContratosPorPers("", "0", $xSoc->getClaveDePersona());
			$xSelCP->addEspOption("0",  $xFRM->getT("TR.NINGUNO"));
			$selcontratos	= $xSelCP->get(true);
			if($xSelCP->getCountRows()>1){
				$xFRM->addHElem($selcontratos);
			} else {
				$xFRM->OHidden("idcontrato", 0);
			}
		}
	}
	$xFRM->addObservaciones();
	$xFRM->endSeccion();
	$xFRM->addSeccion("iddivar", "TR.ARCHIVO");
	$xTxtF->setDivClass("");
	//$xTxtF->setProperty("class", "")
	if($tipodedocto == $xPDoc->TIPO_FOTO){
		$xFRM->OFileImages("idnuevoarchivo","", "");
	} else {
		$xFRM->OFileDoctos("idnuevoarchivo","", "");
	}
	//accept=".csv, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel"
	$itemsPDF	= count($xDoc->FTPListFiles("", ".pdf"));
	
	if($itemsPDF > 0){
		$xFRM->OText("nombrearchivo", "", "TR.Nombre del Archivo", true, $xImg->get24("common/search.png", " onclick='jsGetDocto()' "));
	} else {
		$xFRM->OHidden("nombrearchivo", "");
	}
	$xFRM->endSeccion();
	
	//$xFRM->ODate("idfechavencimiento", $xF->getFechaMaximaOperativa(), "TR.FECHA_DE VENCIMIENTO");
	

	
} else {
	$xFRM->addCerrar("", 3);
	$nombrearchivo	= parametro("nombrearchivo", "", MQL_RAW);
	//$observaciones	= (isset($DDATA["idobservaciones"]) ) ? $DDATA["idobservaciones"] : "";
	//$tipodedocto	= (isset($DDATA["idtipodedocto"]) ) ? $DDATA["idtipodedocto"] : "";
	$pagina			= parametro("idnumeropagina", "");
	$archivonuevo	= (isset($_FILES["idnuevoarchivo"])) ? $_FILES["idnuevoarchivo"] : null;
	$fechacarga		= parametro("idfechacarga", false, MQL_DATE);
	$fechavenc		= false; //parametro("idfechavencimiento", $xF->getFechaMaximaOperativa(), MQL_DATE);
	
	if(isset($_FILES["idnuevoarchivo"])){
		if(trim($_FILES["idnuevoarchivo"]["name"]) == ""){ $archivoenviado = null; }
	}
	$xSoc		= new cSocio($persona);
	if($xSoc->init() == true){
	//if($doc1 !== false){
		$ready		= $xSoc->setGuardarDocumento($tipodedocto, $nombrearchivo, $pagina, $observaciones, $fechacarga, $archivonuevo, $fechavenc, $idcontrato);
		if($ready == true){
			$xFRM->addAvisoRegistroOK($xSoc->getMessages());
		} else {
			$xFRM->addAvisoRegistroError($xSoc->getMessages());
		}
	}
	//if(MODO_DEBUG == true){ $xFRM->addLog($xSoc->getMessages(OUT_TXT) ); }
}
echo $xFRM->get();

//$jsb->show();
?>
<!-- HTML content -->
<script>
var xG	= new Gen();
function jsGetDocto(){
	xG.w({
		url : "../frmutils/docs.explorer.php?callback=jsSetDocto", tiny:true
		});
}
function jsSetDocto(mfile){
	$("#nombrearchivo").val(mfile);
}
</script>
<?php
$xHP->fin();
?>