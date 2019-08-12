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
$xHP		= new cHPage("TR.AGREGAR PASSWORD", HP_FORM);
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
$pin		= parametro("idpin", "", MQL_RAW);

$xFRM		= new cHForm("frmnuevopin", "../frmarrendamiento/originadores-usuarios.pin.frm.php?action=" . MQL_MOD);
$xSel		= new cHSelect();
$xText		= new cHText();

$xFRM->setTitle($xHP->getTitle());
$xTabla		= new cLeasing_usuarios();
$xTabla->setData($xTabla->query()->initByID($clave));
$ready		= false;

$xUsOrg	= new cLeasingUsuarios($clave);
if($xUsOrg->init() == true){
	$xFRM->addSeccion("idinfo0", "TR.INFORMACION");
	$xFRM->addHElem( $xUsOrg->getFicha() );
	$xFRM->endSeccion();
}

$xFRM->addSeccion("idinfo1", "TR.DATOS");

if($action == SYS_NINGUNO){
	$xFRM->OHidden("clave", $clave);
	$xFRM->OHidden("idleasing_usuarios", $xTabla->idleasing_usuarios()->v());
	$xFRM->OHidden("idusuario", $xTabla->idusuario()->v());
	$xFRM->addHElem($xText->getPassword("idpin", $xFRM->l()->getT("TR.PASSWORD")  , ""));
	$xFRM->addEnviar();
} else {
	if($clave > 0){
		
		$xUsOrg	= new cLeasingUsuarios($clave);
		
		if($xUsOrg->init() == true){
			if($xUsOrg->setPin($pin) == true){
				$ready	= true;
			}
		}
	}
	$xFRM->setResultado($ready, $xUsOrg->getMessages(), "", true);
}

$xFRM->endSeccion();

echo $xFRM->get();



//$jxc ->drawJavaScript(false, true);
$xHP->fin();
?>