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
//=====================================================================================================
$xHP	     = new cHPage("TR.Consulta en PEPS", HP_SERVICE);
include_once("../libs/aml.jaro-winkler.inc.php");
include_once("../libs/wikipedia.php");
@include_once("../libs/dompdf/autoload.inc.php");

$txt		= "";
$nombre		= parametro("nombre"); $nombre	= parametro("n", $nombre);
$apaterno	= parametro("apaterno"); $apaterno	= parametro("p", $apaterno);
$amaterno	= parametro("amaterno"); $amaterno	= parametro("m", $amaterno);

$nombreR	= parametro("nombre", "", MQL_RAW); $nombreR	= parametro("n", $nombreR, MQL_RAW);
$apaternoR	= parametro("apaterno", "", MQL_RAW); $apaternoR	= parametro("p", $apaternoR, MQL_RAW);
$amaternoR	= parametro("amaterno", "", MQL_RAW); $amaternoR	= parametro("m", $amaternoR, MQL_RAW);
//sanear la variable para obtener el primer id.
$nombre		= getPalabraDeFrase($nombre);
$apaterno	= getPalabraDeFrase($apaterno);
$amaterno	= getPalabraDeFrase($amaterno);
//
$UseMeta	= parametro("metaphone", false, MQL_BOOL);
$UseJW		= parametro("jarowinkler", false, MQL_BOOL);
$umbral		= parametro("umbral", 80, MQL_INT);
$getPDF		= parametro("report", false, MQL_BOOL);
$ret		= parametro("return", false, MQL_BOOL);
$mails		= getEmails($_REQUEST);
//st VARCHAR(55)) RETURNS varchar(128) CHARSET utf8
//jaro_winkler_similarity`(in1 VARCHAR(255),in2 VARCHAR(255)) RETURNS FLOAT

//agregar PDF
$xFMT			= new cFormato(8802);
$xRuls			= new cReglaDeNegocio();
$NoWiki			= $xRuls->getValorPorRegla($xRuls->reglas()->AML_NO_WIKI);
$xLog			= new cCoreLog();


$txtLst			= "";
$ByTipoPersona	= "";

//TODO: Cambiar

$action			= "LIST";

if(AML_BUSQUEDA_POR_SONIDO == false){
	$ByNombre		= ($nombre == "") ? "" :  " AND (nombrecompleto LIKE '%$nombre%') ";
	$ByAPaterno		= ($apaterno == "") ? "" : " AND (apellidopaterno LIKE '%$apaterno%') ";
	$ByAMaterno		= ($amaterno == "") ? "" : " AND (apellidomaterno LIKE '%$amaterno%') ";
} else {
	$ByNombre		= ($nombre == "") ? "" :  " AND (nombrecompleto SOUNDS LIKE '%$nombre%' OR nombrecompleto LIKE '%$nombre%') ";
	$ByAPaterno		= ($apaterno == "") ? "" : " AND (apellidopaterno SOUNDS LIKE '%$apaterno%' OR apellidopaterno LIKE '%$apaterno%') ";
	$ByAMaterno		= ($amaterno == "") ? "" : " AND (apellidomaterno SOUNDS LIKE '%$amaterno%' OR apellidomaterno LIKE '%$amaterno%') ";
}
//Metaphone
$ByMNombre		= ($nombre == "") ? "" :  " AND func_DoubleMetaphone(nombrecompleto) = func_DoubleMetaphone('$nombre') ";
$ByMAPaterno	= ($apaterno == "") ? "" : " AND func_DoubleMetaphone(apellidopaterno) = func_DoubleMetaphone('$apaterno') ";
$ByMAMaterno	= ($amaterno == "") ? "" : " AND func_DoubleMetaphone(apellidomaterno) = func_DoubleMetaphone('$amaterno') ";
//Jaro Winkler
$ByJWNombre		= ($nombre == "") ? "" :  " AND jaro_winkler_similarity(nombrecompleto, '$nombre') >= $umbral  ";
$ByJWAPaterno	= ($apaterno == "") ? "" : " AND jaro_winkler_similarity(apellidopaterno, '$apaterno' ) >= $umbral ";
$ByJWAMaterno	= ($amaterno == "") ? "" : " AND jaro_winkler_similarity(apellidomaterno, '$amaterno' ) >= $umbral ";

$OrderBy		= ($UseMeta == true OR $UseJW) ? "" : "ORDER BY	`socios_general`.`apellidopaterno`,	`socios_general`.`apellidomaterno`,	`socios_general`.`nombrecompleto` ";


if($ByNombre !== "" AND ($ByAPaterno !== "" OR $ByAMaterno !== "")){
	$ByTipoPersona	= " AND `personalidad_juridica`= " . PERSONAS_FIGURA_FISICA;
} else {
	$ByTipoPersona	= " AND `personalidad_juridica`= " . PERSONAS_FIGURA_MORAL;
}

if($ByAMaterno != "" AND $ByAPaterno != "" ){
	//$ByAPaterno	= "AND ( (apellidopaterno SOUNDS LIKE '%$apaterno%' OR apellidopaterno LIKE '%$apaterno%') OR (apellidomaterno SOUNDS LIKE '%$amaterno%' OR apellidomaterno LIKE '%$amaterno%') ) ";
	//$ByAMaterno	= "";
}


//$nombreR	= parametro("nombre", "", MQL_RAW); $nombreR	= parametro("n", $nombreR, MQL_RAW);
//$apaternoR	= parametro("apaterno", "", MQL_RAW); $apaternoR	= parametro("p", $apaternoR, MQL_RAW);
//$amaternoR	= parametro("amaterno", "", MQL_RAW); $amaternoR	= parametro("m", $amaternoR, MQL_RAW);

//exit;
$sql 	= "SELECT
		`socios_general`.`codigo`          AS `codigo`,
		`socios_general`.`apellidopaterno` AS `primerapellido`,
		`socios_general`.`apellidomaterno` AS `segundoapellido`,
		`socios_general`.`nombrecompleto`  AS `nombres`,
		`socios_general`.`curp`,
		'soundlike' AS 'tipo'
	FROM
		`socios_general`
	WHERE
		codigo != " . DEFAULT_SOCIO . " AND (`tipoingreso`= " . TIPO_INGRESO_PEP . " /*OR `nivel_de_riesgo_aml` = 100*/)
		$ByNombre $ByAPaterno $ByAMaterno $ByTipoPersona $OrderBy LIMIT 0,10";
if($UseJW == true){
	$sql 	.= " UNION SELECT
		`socios_general`.`codigo`          AS `codigo`,
		`socios_general`.`apellidopaterno` AS `primerapellido`,
		`socios_general`.`apellidomaterno` AS `segundoapellido`,
		`socios_general`.`nombrecompleto`  AS `nombres`,
		`socios_general`.`curp`,
		'jarowinkler' AS 'tipo'
	FROM
		`socios_general`
	WHERE
		codigo != " . DEFAULT_SOCIO . " AND (`tipoingreso`= " . TIPO_INGRESO_PEP . " /*OR `nivel_de_riesgo_aml` = 100*/)
			$ByJWNombre  $ByJWAPaterno $ByJWAMaterno $OrderBy
			LIMIT 0,10";	
}
if($UseMeta == true){
	$sql 	.= " UNION SELECT
		`socios_general`.`codigo`          AS `codigo`,
		`socios_general`.`apellidopaterno` AS `primerapellido`,
		`socios_general`.`apellidomaterno` AS `segundoapellido`,
		`socios_general`.`nombrecompleto`  AS `nombres`,
		`socios_general`.`curp`,
		'metaphone' AS 'tipo'
	FROM
		`socios_general`
	WHERE
		codigo != " . DEFAULT_SOCIO . "	AND (`tipoingreso`= " . TIPO_INGRESO_PEP . " /*OR `nivel_de_riesgo_aml` = 100*/)
			$ByMNombre $ByMAPaterno $ByMAMaterno $ByTipoPersona $OrderBy LIMIT 0,10
			";	
}

//exit($sql);
//header('Content-type: application/json');

$json			= array();
$mql			= new MQL();
if( strlen(trim(str_replace(" ", "", "$nombre$amaterno$apaterno"))) <= 3){
	$rs			= false;
	$xLog->add("ERROR\tNo hay parametros de Busqueda($nombre-$amaterno-$apaterno)");
} else {
	$rs			= $mql->getRecordset($sql);
}
$idx			= 0;
$xLng			= new cLang();
if($rs){
	while ($row = $rs->fetch_assoc()) {
		$id			= $row["codigo"];
		foreach($row as $campo => $valor){
			if ( is_string($valor) ){
				$valor		= htmlentities($valor); //htmlentities( (string) $valor, ENT_QUOTES, 'utf-8', FALSE);
			}
			
			$json["record_$idx"][$campo]	= $valor; //base64_encode($valor);//utf8_encode($valor);
			
		}
		
		$xSoc		= new cSocio($id);
		$xSoc->init();
		$OAE		= $xSoc->getOActividadEconomica();
//================= Consultar en wikipedia
		$mNombre	= $row["nombres"];
		$mAPaterno	= $row["primerapellido"];
		$mAMaterno	= $row["segundoapellido"];
		//Consultar el Wikipedia
		if($NoWiki == true){
			$arrWiki			= array();
		} else {
			$xWiki				= new cWikipedia();
			$arrWiki			= $xWiki->buscar(trim("$mNombre $mAPaterno $mAMaterno"));
			if($xWiki->esBusqueda($arrWiki) == false){
				$arrWiki		= $xWiki->buscar(trim("$mNombre $mAPaterno"));
				if($xWiki->esBusqueda($arrWiki) == false){
					$arrWiki	= $xWiki->buscar(trim("$mAPaterno $mAMaterno"));
				}
			}
		}
//================ End consulta wikipedia
		//setLog($arrWiki);
		//Generar el Acuse
		if($getPDF == false){
			
			//Agrega Actividad económica
			if($OAE == null ){
				$json["record_$idx"]["puesto"]		= "";
				$json["record_$idx"]["dependencia"]	= "";
			} else {
				$json["record_$idx"]["puesto"]		= $OAE->getPuesto();
				$json["record_$idx"]["dependencia"]	= $OAE->getNombreEmpresa();
			}
			
//====================== Wiki values
			if(is_array($arrWiki)){
				$QD	= $arrWiki;
				if(isset($QD["search"])){
					$dataWiki	= $QD["search"];
					$cntWiki	= 0;
					foreach ($dataWiki as  $items ){
						$title		= $items["title"];
						$snip		= $items["snippet"];
						
						if(strpos($snip, "#REDIR") !== false){
							$snip	= str_replace("#REDIRECCIÓN", "", $snip);
							$DSnip	= explode("[[", $snip);
							$snip	= $DSnip[1];
							$snip	= str_replace("]]", "", $snip);
							$title	= $snip;
						}						
						$json["record_$idx"]["info_$cntWiki"]		= $xWiki->getPage($title);
						$cntWiki++;
					}
				}
			}
			//setLog("FCKM");
//====================
		} else {
			$id			= $row["codigo"];
			$tipo		= $row["tipo"];
			$app1		= $row["primerapellido"];
			$app2		= $row["segundoapellido"];
			$noms		= $row["nombres"];
			$flTitle	= "";
			//generar aka
			//$xSoc		= new cSocio($id);
			//$xSoc->init();
			$xTbl		= new cHTabla();
			$xTbl->initRow();
			//score
			$xTbl->addTH($xLng->getT("TR.Numero"));
			switch($tipo){
				case "soundlike":
					$flTitle	= $xLng->getT("TR.Algoritmo") . " : SQL-SOUNDLIKE";
					//$xTbl->addTH("Algoritmo");
					//$xTbl->addTD("SOUNDLIKE");
					$xTbl->addRaw("<td /><td />");
					break;
				case "jarowinkler":
					$flTitle	= $xLng->getT("TR.Algoritmo") . " : JARO-WINKLER";
					$xTbl->addTH($xLng->getT("TR.Coincidencia"));
					
					$score1			= 0;
					$score2			= 0;
					$score3			= 0;
					
					if($nombre != ""){ $score1		= JaroWinkler($nombre, $noms);	}
					if($amaterno != ""){ $score3	= JaroWinkler($amaterno, $app2); }
					if($apaterno != ""){ $score2	= JaroWinkler($apaterno, $app1); }
					//JaroWinkler($string1, $string2)
					$xTbl->addTD("$score1 / $score2 / $score3");
					break;
				case "metaphone":
					$flTitle		= $xLng->getT("TR.Algoritmo") . " : METAPHONE";
					$xTbl->addTH($xLng->getT("TR.Coincidencia"));
					$score1			= "";
					$score2			= "";
					$score3			= "";
						
					if($nombre != ""){ $score1		= metaphone($nombre) . "/" . metaphone($noms);	}
					if($amaterno != ""){ $score3	= metaphone($amaterno) . "/" . metaphone($app2);; }
					if($apaterno != ""){ $score2	= metaphone($apaterno) . "/" . metaphone($app1);; }
					//JaroWinkler($string1, $string2)
					$xTbl->addTD("$score1 & $score2 & $score3");
										
					break;
			}
			//$xTbl->endRow();

			//sdn id
			//
			
			
			$xTbl->endRow();
			
			//
			
			
			//nombres
			$xTbl->initRow();
			$xTbl->addTD($id);
			$xTbl->addTH($xLng->getT("TR.Nombre"));
			$xTbl->addTD($xSoc->getNombreCompleto());
			//alias
			$xTbl->addTH($xLng->getT("TR.Alias"));
			$xTbl->addTD($xSoc->getAlias());
			$xTbl->endRow();
			//direcciones
			//marcadores
			$xTbl->addRaw("<tr><td colspan='3'>" . $xSoc->getObservaciones() . "</td></tr>");
			//$xTbl->initRow();
			//$OAC 		= $xSoc->getOActividadEconomica();
			$adr		= $OAE->getFicha();
			$xTbl->addRaw("<tr><td colspan='3'>$adr</td></tr>");			
			//$xTbl->endRow()
			
//======= Wiki data
			if(is_array($arrWiki)){
				$QD	= $arrWiki;
				if(isset($QD["search"])){
					$dataWiki	= $QD["search"];
					$cntWiki	= 0;
					foreach ($dataWiki as  $items ){
						$title		= $items["title"];
						$snip		= $items["snippet"];
			
						if(strpos($snip, "#REDIR") !== false){
							$snip	= str_replace("#REDIRECCIÓN", "", $snip);
							$DSnip	= explode("[[", $snip);
							$snip	= $DSnip[1];
							$snip	= str_replace("]]", "", $snip);
							$title	= $snip;
						}
						$xTbl->addRaw("<tr><td colspan='3'><h1>Informaci&oacute;n Extendida</h1></td></tr>");
						$xTbl->addRaw("<tr><td colspan='3'>" . $xWiki->getPage($title) . "</td></tr>");
						$cntWiki++;
					}
				}
			}
//======== End Wiki data		
			$xFld		= new cHFieldset($flTitle);
			$xFld->addHElem($xTbl->get());
			$txtLst 	.= $xFld->get();
			
		}
		$idx++;
	} //End While
	$xLog->add("INFO\tItems encontrados $idx");
	
	/*if($idx <= 0){
		//Consultar el Wikipedia
		$xWiki		= new cWikipedia();
		//setLog("$nombreR $apaternoR $amaternoR");
		$arrWiki	= $xWiki->buscar(trim("$nombreR $apaternoR $amaternoR"));
		if($xWiki->esBusqueda($arrWiki) === false){
			$arrWiki	= $xWiki->buscar(trim("$nombreR $apaternoR"));
			if($xWiki->esBusqueda($arrWiki) === false){
				$arrWiki	= $xWiki->buscar(trim("$apaternoR $nombreR"));
			}
		}
		
		if($getPDF == false){
		//====================== Wiki values
			if(is_array($arrWiki)){
				$QD				= $arrWiki;
				
				if(isset($QD["search"])){
					$dataWiki	= $QD["search"];
					$cntWiki	= 0;
					
					$json["record_$idx"]["codigo"]			= 0;
					$json["record_$idx"]["tipo"]			= "external";
					$json["record_$idx"]["primerapellido"]	= $apaternoR;
					$json["record_$idx"]["segundoapellido"]	= $amaternoR;
					$json["record_$idx"]["nombres"]			= $nombreR;
					$json["record_$idx"]["curp"]			= "";
					$enable									= false;					
					foreach ($dataWiki as  $items ){ //#REDIRECCI\u00d3N
						$title		= $items["title"];
						$snip		= $items["snippet"];
						
						if(strpos($snip, "#REDIR") !== false){
							$snip	= str_replace("#REDIRECCIÓN", "", $snip);
							$DSnip	= explode("[[", $snip);
							$snip	= $DSnip[1];
							$snip	= str_replace("]]", "", $snip);
							$title	= $snip;
						}						
						$json["record_$idx"]["info_$cntWiki"]		= $xWiki->getPage($title);
						$enable								= (trim($json["record_$idx"]["info_$cntWiki"]) == "") ? $enable : true;
						$cntWiki++;
					}
					if($enable == false){ unset($json["record_$idx"]); }
				}
			}
		} else {
			//$txtLst
			if(is_array($arrWiki)){
				$QD				= $arrWiki;
				if(isset($QD["search"])){
					$dataWiki	= $QD["search"];
					$cntWiki	= 0;
					foreach ($dataWiki as  $items ){
						$title		= $items["title"];
						$snip		= $items["snippet"];
						
						if(strpos($snip, "#REDIR") !== false){
							$snip	= str_replace("#REDIRECCIÓN", "", $snip);
							$DSnip	= explode("[[", $snip);
							$snip	= $DSnip[1];
							$snip	= str_replace("]]", "", $snip);
							$title	= $snip;
						}						
							
							$cnt	= $xWiki->getPage($title);
							
							$txtLst	.= "<fieldset><legend>INFORME EXTERNO</legend><table><tbody><tr><td>" . $cnt . "</td></tr></tbody></table></fieldset>";
						//}
						$cntWiki++;
					}
				}
			}			
		}
	}*/
	if($getPDF == true){
		//base64_encode($sql)
		$xFMT		= new cFormato(8802);
		$xFMT->setProcesarVars(array(
				"variable_listado_de_cedulas" => $txtLst,
				"variable_item_buscado" => "$nombre / $apaterno / $amaterno",
				"variable_cadena_consulta" => ""
		));
		$xRPT		= new cReportes($xHP->getTitle());
		$xRPT->getEncabezado($xHP->getTitle());

		if($ret == true){
			$xRPT->setOut(OUT_HTML);
			$xRPT->setFile("peps_list_");
			$xRPT->addContent($xFMT->get());			
		
			$xRPT->render(true);
			

			$dompdf 	= new Dompdf\Dompdf();
			$dompdf->loadHtml($xRPT->render(true));
			$dompdf->setPaper("letter", "portrait" );
			

			
			$dompdf->render();
			$json["pdf"] 	= base64_encode($dompdf->output());
		} else {
			$xRPT->setSenders($mails);
			$xRPT->setOut(OUT_PDF);
			$xRPT->setFile("pep_list_");
			$xRPT->addContent($xFMT->get());
			$xRPT->render(true);
		}	
	}
} else {
	$xLog->add($mql->getMessages(OUT_TXT), $xLog->DEVELOPER);
}
$json["error"] = $xLog->getMessages();

header('Content-type: application/json');
echo json_encode($json);
?>