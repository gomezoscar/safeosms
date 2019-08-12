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
$xHP		= new cHPage("TR.Importar Catalogo CW", HP_FORM);
$esqueleto	= "XX|XXXXXXXXXXXXXXXXXXXXXXXXXXXXXX|XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX|XXXXXXXXXXXXXXXXXXXXXXXXXXXXXX|X|X|X|X|XXXXXXXX|XXXXX|XXXX|X|XXXX|X";
//C  101000000000000000             DISPONIBILIDADES                                                                                      100000000000000000             A 0 4 0 20140708 11    1    0 0    0
//$jxc = new TinyAjax();
//$jxc ->exportFunction('datos_del_pago', array('idsolicitud', 'idparcialidad'), "#iddatos_pago");
//$jxc ->process();

$persona	= parametro("persona", DEFAULT_SOCIO, MQL_INT); $persona = parametro("socio", $persona, MQL_INT); $persona = parametro("idsocio", $persona, MQL_INT);
$credito	= parametro("credito", DEFAULT_CREDITO, MQL_INT); $credito = parametro("idsolicitud", $credito, MQL_INT); $credito = parametro("solicitud", $credito, MQL_INT);
$cuenta		= parametro("cuenta", DEFAULT_CUENTA_CORRIENTE, MQL_INT); $cuenta = parametro("idcuenta", $cuenta, MQL_INT);
$jscallback	= parametro("callback"); $tiny = parametro("tiny"); $form = parametro("form"); $action = parametro("action", SYS_NINGUNO);

$xHP->init();

$xFRM		= new cHForm("frm", "contabilidad_import-catalogocw.frm.php?action=" . MQL_TEST);
$xFil		= new cHFile();
$xChk		= new cHCheckBox();
$msg		= "";
if($action == SYS_NINGUNO ){
	$xFRM->OFile("idarchivo");
	$xFRM->addHElem( $xChk->get("TR.Afectar Base_de_Datos", "idaplicar") );
	$xFRM->OText("idmascara", $esqueleto, "TR.Mascara");
} else {
	//
	$doc1					= (isset($_FILES["idarchivo"])) ? $_FILES["idarchivo"] : false;
	$xFi					= new cFileImporter();
	$xT						= new cTipos();
	class cTmp {
		public $CUENTA		= 2;
		public $NATURALEZA	= 5;
		public $NIVEL		= 3; //NA
		public $CTA1 		= 3;
		public $CTA2 		= 5; //NA
		public $CTA3 		= 6; //NA
		public $CTA4 		= 7; //NA
		public $CTA5 		= 8; //NA
		public $CTA6 		= 9; //NA
	}
	$catImport				= array();
	$aplicar				= parametro("idaplicar", false, MQL_BOOL);
	if($aplicar == true ){ $action = MQL_ADD; }
	
	//Cedula de Identidad
	$tmp					= new cTmp();
	$xFi->setCharDelimiter("|");
	//$xFi->setLimitCampos(9);
	$xFi->setToUTF8();
	//var_dump($_FILES["f1"]);
	$xFi->setExo($esqueleto);
	//var_dump($_FILES);
	if($xFi->processFile($doc1) == true){
		$data				= $xFi->getData();
		$conteo				= 1;
		foreach ($data as $rows){
			if($conteo > 1){
				$xFi->setDataRow($rows);
				$cuenta		= $xFi->getV($tmp->CUENTA, "");
				
				$xEQ		= new cCuentaContableEsquema($cuenta);
				$xCW		= new cCatalogoCompacW();
				//echo $xEQ->CUENTA_FORMATEADA . "<br/>" . $xEQ->CUENTARAW ."<br />" . $xCW->getEquivalencia( $xFi->getV($tmp->NATURALEZA, "") )  . $xEQ->NIVEL_ACTUAL . "<hr />";
				$cuenta		= $xEQ->CUENTA_FORMATEADA;
				$xCCont		= new cCuentaContable($cuenta);
				$nivel	= $xCCont->determineNivel($cuenta);
				$sucess		= true;
				$nombre		= $xFi->getV($tmp->CTA1, "");
				$nombre		= trim($nombre);
				$superior	= $xEQ->CUENTA_SUPERIOR;// $xCCont->getInmediatoSuperior();
				$cuenta		= $xCCont->getCuentaCompleta($cuenta);
				
				$nombre		= $xT->setNoAcentos($nombre);
				$nombre		= str_replace("'", "", $nombre);
				$nombre		= strtoupper($nombre);
				$naturaleza	= $xCW->getEquivalencia( $xFi->getV($tmp->NATURALEZA, "") );

				if( ($sucess == true) AND ($action == MQL_ADD) ){
					if(setNoMenorQueCero($cuenta) > 0){
						$xCta		= new cCuentaContable($cuenta);
						$xCta->add($nombre, $naturaleza, false, false, $nivel, false, false, $superior);
				
						$msg		.= $xCta->getMessages(OUT_TXT);
					}
				}
								

			} else {
				//$msg		.= "$conteo\t===============\r\n";
			}
			$conteo++;
		}

		$msg		.= $xFi->getMessages(OUT_TXT);
		if(MODO_DEBUG == true){
			$xLog		= new cFileLog();
			$xLog->setWrite($msg);
			$xLog->setClose();
			$xFRM->addToolbar( $xLog->getLinkDownload("TR.Archivo del proceso", ""));
		}
	}
}


$xFRM->addJsBasico();
//$xFRM->addCreditBasico();

$xFRM->addSubmit();

echo $xFRM->get();

//$jxc ->drawJavaScript(false, true);
$xHP->fin();
?>