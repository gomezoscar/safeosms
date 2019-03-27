<?php
if ( !file_exists(dirname(__FILE__) . "/core/core.config.os." . strtolower(substr(PHP_OS, 0, 3)) .  ".inc.php") ){ header("location: install/install.php"); } else {  }
//=====>	INICIO_H
	include_once("./core/go.login.inc.php");
	include_once("./core/core.error.inc.php");
	include_once("./core/core.html.inc.php");
	include_once("./core/core.init.inc.php");
	
	@include_once ("./libs/aes.php");
	
	$theFile					= __FILE__;
	$permiso					= getSIPAKALPermissions($theFile);
	if($permiso === false){		header ("location:../404.php?i=999");	}
	$_SESSION["current_file"]	= addslashes( $theFile );
//<=====	FIN_H

//=====================================================================================================
$mTit						= ENTIDAD_NOMBRE_SIC;

$xHP						= new cHPage($mTit, HP_FORM, "", ".");
$xRuls						= new cReglaDeNegocio();
$xMenu						= new cHMenu();
$xCache						= new cCache();
$xUser						= new cSystemUser(getUsuarioActual());
$xPatch						= new cSystemPatch();

$fecha_de_sesion			= parametro("f", fechasys());
$MenuParent					= parametro("m", 0, MQL_INT);

$usarMenuAlt				= $xRuls->getValorPorRegla($xRuls->reglas()->RN_USAR_MENU_ALT);

$isMobile					= parametro("mobile", $xHP->isMobile($usarMenuAlt), MQL_BOOL);

$_SESSION[SYS_CLIENT_MOB]	= $isMobile;

$xUser->init();
$xUser->getUserRules();
$TasksPage					= $xUser->getTasksPage();



/**
 * Procedimientos AJAX
 */
$jxc 				= new TinyAjax();
function jsaGetMoneyChanges(){
	if(!isset($_SESSION["money"])){ $_SESSION["money"] = 0; }
	$_SESSION["money"] += 100;
	return  $_SESSION["money"];
}
function jsaGetMenu($subitems){

	$xMen	= new cHMenu();
	$xMen->setID("navigator");
	$menu	= "";
	
	$xMen->setIsMobile($_SESSION[SYS_CLIENT_MOB]);
	
	if($subitems > 0){
		$btn			= "";
		if($_SESSION[SYS_CLIENT_MOB] == true){
			$menu 		.= "<li><a onclick='var xG = new Gen(); xG.home();'><i class='fa fa-home fa-lg'></i>Inicio</a></li>";
		}
		
		$menu			.= $xMen->getItems($subitems);
	}
	return  $menu;

}
$jxc->exportFunction('jsaGetMenu', array('id-KeyEditable'), "#jMenu");
$jxc->exportFunction('jsaGetMoneyChanges', array("idMoneyExist"), "#idMoneyExist");

$jxc->process();




/* ******************************************************************************************************************
//
//--------------------------------------Verifica las tareas Comunes.------------------------------------------------
//
****************************************************************************************************************** */

if(MODO_DEBUG == true){
	
} else {
	//checar cierre del dia
	$xCierre			= new cCierreDelDia();
	$aCierres			= $xCierre->check5Cierres($fecha_de_sesion);
	if($aCierres[SYS_ESTADO] == false){
		setLog($xCierre->getMessages(), 300);
		header("location:utils/frmcierredeldia.php"); exit();
	}
	//if(CREDITO_CONTROLAR_POR_PERIODOS == true){
	$xPerCred		= new cPeriodoDeCredito();
		if($xPerCred->checkPeriodoVigente($fecha_de_sesion) == false ){
			setLog($xPerCred->getMessages(), 300);
			header("location:frmcreditos/cambiarperiodo.frm.php?a=1");
		}
	//}
}

		
$PATHIMG 	= "images/common/";

$xHP->setNoCache();
//$xHP->setNoDefaultCSS();

	//$xHP->addCSS("css/general.css");
	
	//$xHP->addJsFile("js/jquery/jquery.ui.js");
	//if($isMobile == false){
		//$xHP->addCSS("css/jmenu.css");
		//$xHP->addJsFile("js/jmenu/jMenu.jquery.js");
		
$xHP->addCSS("css/jquery.mmenu.all.css");
$xHP->addJsFile("js/jquery.mmenu.all.js");
		
	//}
	//$xHP->addJsFile("js/tinybox.js");

echo $xHP->getHeader();

?>
<style>
	html,body,object{padding:0 !important;	margin:0 !important;}
	#header{text-align: center !important;}
	#banner{right: 0;bottom: 0;position:fixed !important;}
	#wprincipal{width: 100%; max-width: 100%;}
	#idbtninfouser {
	right:5px;top:8px;position:fixed !important;text-shadow:0px 1px 1px #4d4d4d;color:#FFF;font-size:1.2em;background:#AB3D6F; padding-left:6px;padding-top:2px;padding-bottom:2px;padding-right:6px;border-radius:3px;
	}
	
<?php
if ($isMobile == true){ 
?>

#jPanelMenu-menu { font-size: 1em;overflow-y: hidden !important; background: #65a9cc; }
#jPanelMenu-menu li a { min-height: 1.8em;}
#jPanelMenu-menu {background:#3b3b3b;max-height: 99% !important;}
#jPanelMenu-menu ul{border-bottom:1px solid #484848;padding:0}
#jPanelMenu-menu li a{background:#3b3b3b;background:-o-linear-gradient(top, #3e3e3e, #383838);background:-ms-linear-gradient(top, #3e3e3e, #383838);background:-moz-linear-gradient(top, #3e3e3e, #383838);background:-webkit-gradient(linear, left top, left bottom, color-stop(0, #3e3e3e), color-stop(1, #383838));background:-webkit-linear-gradient(#3e3e3e, #383838);background:linear-gradient(top, #3e3e3e, #383838);font-family:"museo-sans","Museo Sans","Helvetica Neue",Helvetica,Arial,sans-serif;
font-weight:300;display:block;padding:0.5em 5%;border-top:1px solid #484848;border-bottom:1px solid #2e2e2e;text-decoration:none;text-shadow:0 -1px 2px #222; color: white}
#jPanelMenu-menu ul li a:hover, #jPanelMenu-menu ul li a:focus{background:#404040;background:-o-linear-gradient(top, #484848, #383838);background:-ms-linear-gradient(top, #484848, #383838);background:-moz-linear-gradient(top, #484848, #383838);background:-webkit-gradient(linear, left top, left bottom, color-stop(0, #484848), color-stop(1, #383838));background:-webkit-linear-gradient(#484848, #383838);background:linear-gradient(top, #484848, #383838); color: #F7F7F7}
#jPanelMenu-menu li a:active{background:#363636;background:-o-linear-gradient(top, #3e3e3e, #2e2e2e);background:-ms-linear-gradient(top, #3e3e3e, #2e2e2e);background:-moz-linear-gradient(top, #3e3e3e, #2e2e2e);background:-webkit-gradient(linear, left top, left bottom, color-stop(0, #3e3e3e), color-stop(1, #2e2e2e));background:-webkit-linear-gradient(#3e3e3e, #2e2e2e);background:linear-gradient(top, #3e3e3e, #2e2e2e);-moz-box-shadow:0 2px 7px #222 inset;-webkit-box-shadow:0 2px 7px #222 inset;box-shadow:0 2px 7px #222 inset;border-top-color:#222;padding-top:0.55em;padding-bottom:0.45em}

#jPanelMenu-menu ul, #jPanelMenu-menu ol{list-style:none;list-style-image:none;}
#jPanelMenu-menu ul li a i {  padding-right: 5px; }
<?php 
} 
?>

#jpanel {
	min-height: 2em;
 	text-align: center; border-bottom: 1px solid #1b5572; box-shadow: 0 0 25px #222;  /* box-shadow:0 1px 5px rgba(34,34,34,0.5);*/
	transform: translate3d(0px, 0px, 0px);
  	background: #317ca2;
	background: -o-linear-gradient(top, #3f94bf, #246485);
	background: -ms-linear-gradient(top, #3f94bf, #246485);
	background: -moz-linear-gradient(top, #3f94bf, #246485);
	background: -webkit-gradient(linear, left top, left bottom, color-stop(0, #3f94bf),
		color-stop(1, #246485));
	background: -webkit-linear-gradient(#3f94bf, #246485);
	background: linear-gradient(top, #3f94bf, #246485);
	-moz-box-shadow: 0 1px 5px rgba(34, 34, 34, 0.5);
	-webkit-box-shadow: 0 1px 5px rgba(34, 34, 34, 0.5);
	box-shadow: 0 1px 5px rgba(34, 34, 34, 0.5);
	width: 100%; /*95%*/
	max-width: none;
	height: 2.5em;
	margin: 0;
	/*padding: 0 7.5%;*/
	border-bottom: 1px solid #1b5572;
	z-index: 10

}
#jpanel #menugo {
	float: left; position: relative; color: #FFF; top: 0.25em; left: 0.25em;
}
#jpanel h1 { background-color: transparent !important; background-image: none;  height:1.7em; }


</style>
<body  onload="jsInitComponents();">
<?php
$adsense	= (MODO_DEBUG == true) ? "" : getAdsense();
//$xFRM->addToolbar("");

$xMenu->setIsMobile($isMobile);

$iduserniv	= $xUser->getNivel();
$iduserniv	= ($isMobile == true) ? "xmenu-mob-$iduserniv" : "xmenu-desk-$iduserniv";
$menu		= $xCache->get($iduserniv);
if($menu === null){
	$menu		= $xMenu->getAll();
	$xCache->set($iduserniv, $menu, $xCache->EXPIRA_MEDHORA);
}




	$menu	=
	"<div id=\"jpanel\">
	<a href=\"#navmenu\" class=\"menu-trigger\" id=\"menugo\">
	<i class=\"fa fa-reorder fa-2x\"></i></a> <h1 id='htitle'>$mTit</h1>
	<span id=\"idbtninfouser\" style='cursor:pointer' onclick=\"setInFrame('../frmsocios/socios.usuario.frm.php')\">" . $xUser->getNombreDeUsuario() . " / " . getSucursal() . "</span>
	</div>
 
	<nav id=\"navmenu\">" . $menu . "</nav> ";
	
	
	echo $menu;
	echo "<div id=\"wprincipal\">
	<iframe id=\"idFPrincipal\" src=\"./$TasksPage\" width=\"100%\" height=\"100%\" ></iframe>
	</div>
	<div id=\"banner\">$adsense</div>";
	
	echo "<input type=\"hidden\" id=\"id-KeyEditable\" />";


$jxc ->drawJavaScript(false, true);

?>
<script>
var xG 		= new Gen();
var smenu	= 42;
var mmob	= <?php echo ($isMobile == false) ? 'false': 'true'; ?>;
var reqUpt	= <?php echo ($xPatch->isRequiredPatch() == true) ? "true" : "false"; ?>;
var dbLv	= <?php echo $xPatch->getDBLocalVersion(); ?>;
var dbCv	= <?php echo $xPatch->getDBCodeVersion(); ?>;

$(document).ready(function(){

	$(function() {
		$('nav#navmenu').mmenu({
			navbar : {
			title : 'Menu'
		},
		navbars	: [
			{
				position	: 'top',
				content		: [ 'breadcrumbs', 'close' ]
			}
		],
		onClick : {
			close : true
		}
		});
	});

	//$("#wprincipal").css("height", xG.alto());
	
	if(mmob === false){
		session(Configuracion.variables.sistema.isMobile, "0");
	} else {
		smenu	= 8;
		session(Configuracion.variables.sistema.isMobile, "1");
	}
	if(reqUpt === true){
		xG.aviso({msg:"El Sistema requiere actualizacion, si no actualiza puede general un mal funcionamiento.<br />La ID local es : " + dbLv + ".<br />La ID en codigo es : " + dbCv + ".", tipo : "warn"});
	} 
});

function setInFrame(sURI, evt){

	setCloseM1();
	
	xG.QFrame({ url : sURI, id : 'idFPrincipal' });
}
function jsGetParent(parentID){	window.location = "./index.xul.php?m=" + parentID;}
function getNewTiny(mFile){
	
	if(mFile){
		setCloseM1();
		var xG	= new Gen();
		mFile	= mFile + "?";
		xG.w({url: mFile, tiny : true});
	}
}

function getNewWindow(mFile){
	if(mFile){
		setCloseM1();
		var xG	= new Gen();
		xG.w({url: mFile});
	}
	<?php
		if(MODO_DEBUG == false){
			echo "if( window.console ) { window.console.log( '' ); }";
		}
	?>


}
function setCloseM1(){ 
	var API = $("#navmenu").data( "mmenu" );
	API.close();
}
function jsGetMoneyChanges(){
	jsaGetMoneyChanges();
	//Mostrar el Popup
	setTimeout("jsGetMoneyInBox()", 15000);
}
function jsGetMoneyInBox(){
	//setTimeout("jsGetMoneyChanges()", 15000);
}
function jsEndSession(){}
function jsInitComponents(){
	jsGetMoneyChanges();
	var mAlto	= xG.alto()-(smenu + 5);
	<?php
		if($isMobile == true){ 
	?>
	var smenu2	= entero($("#htitle").css("height"));
	
	var mAlto2	= xG.alto()-(smenu+smenu2);
	var mAlto	= xG.alto()-(smenu + 8 + smenu2);
	$("#wprincipal").css("height", mAlto2);
	
	<?php 
		}
	?>
	$("#idFPrincipal").attr("height", mAlto);
	if($('#id-KeyEditable').length >0){
		//setTimeout("jsGetMenu()", 500);
	}
}

</script>
<?php 
$xHP->fin();
?>