<?php
/*include_once ("core/global.inc.php");
include_once ("core/global.static.inc.php");
include_once ("core/lang.inc.php");
include_once ("core/html.inc.php");*/
// Get HTTP/HTTPS (the possible values for this vary from server to server)
$lurl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] && !in_array(strtolower($_SERVER['HTTPS']),array('off','no'))) ? 'https' : 'http';
// Get domain portion
$lurl .= '://'. $_SERVER['HTTP_HOST'] . "/";

$dir			= $_SERVER["DOCUMENT_ROOT"];

$ndb			= $_SERVER['HTTP_HOST'];
$ondebug		= isset($_REQUEST["debug"]) ? $_REQUEST["debug"] : false;
//$ndb			= "demo.server.lan";

if(strpos($ndb, ".") !== false){
	$ddb		= explode(".",$ndb);
	$ndb		= $ddb[0];
}

$arrEsp			= array(0=>"_", 1 => ".", 2=>"%");
$sABC			= "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
$sABC			= str_shuffle($sABC);
$largo			= strlen($sABC);
$rnd			= rand(0,($largo-2));
$rnd2			= rand(0,($largo-4));

$userPos		= "User". rand(0,10) . $ndb;

$tt				= microtime();
$userPwd		= substr($sABC, $rnd, 2) . $arrEsp[rand(0,2)] . crc32("$tt") . strtolower(substr($sABC, $rnd, 2));


$privateconfig	= "$dir/core/core.config.os." . strtolower(substr(PHP_OS, 0, 3)) .  ".inc.php";
if ( file_exists($privateconfig) ){ header("location: ../index.php"); } else {  }

include_once ("./libs/importer.php");
ini_set("max_execution_time", 900);

if($ondebug == true){
		error_reporting(E_ALL);
		ini_set('display_errors', 1);
		ini_set('display_startup_errors', 1);
}

$msg			= "";
//======================== Checar
//si guardar iniciar al index
$action			= (isset($_REQUEST["action"])) ?  $_REQUEST["action"] : "";

$usrmysql		= (isset($_REQUEST["idusuario"])) ?  $_REQUEST["idusuario"] : "";
$pwdmysql		= (isset($_REQUEST["idpassword"])) ?  $_REQUEST["idpassword"] : "";
$srvmysql		= (isset($_REQUEST["idservidor"])) ?  $_REQUEST["idservidor"] : "localhost";
$dbmysql		= (isset($_REQUEST["iddb"])) ?  $_REQUEST["iddb"] : "";

$pwdroot		= (isset($_REQUEST["idpwdroot"])) ?  $_REQUEST["idpwdroot"] : "";

//$sucursal		= (isset($_REQUEST["idsucursal"])) ?  $_REQUEST["idsucursal"] : "";
$urlsys			= (isset($_REQUEST["idurl"])) ?  $_REQUEST["idurl"] : $_SERVER['SERVER_NAME'];
$urlpath		= (isset($_REQUEST["idpath"])) ?  $_REQUEST["idpath"] : $dir;
$nocache		= (isset($_REQUEST["idnocache"])) ?  $_REQUEST["idnocache"] : 0;
$useeng			= (isset($_REQUEST["iduseeng"])) ?  $_REQUEST["iduseeng"] : false;

if($nocache == 1){
	$nocache	= true;
} else {
	$nocache	= false;
}
if($useeng == 1){
	$useeng		= true;
} else {
	$useeng		= false;
}

$srvmysql		= ($srvmysql == "") ? "127.0.0.1" : $srvmysql;

if( trim("$usrmysql$pwdmysql") !== "" AND trim("$srvmysql$dbmysql") !== "" AND $action == "" ){
	
	$markL		= "";
	if($useeng == true){
		$markL	= "-en";
	}
	$FS_TMP		= "/tmp";
	$F1			= "$FS_TMP/safe-osms.sql";
	$F2			= "$FS_TMP/xx.vistas.sql";
	$F3			= "$FS_TMP/xx.functions.sql";
	
	if(file_exists($F1)){
		unlink($F1);
		$msg		.= "Eliminando el Archivo pre-existente\r\n";
	}
	//ejecutar si no existe el archivo
	system("wget https://raw.githubusercontent.com/patadejaguar/safeosmsdb/master/safe-osms" . $markL. ".sql -O $F1");
	system("wget https://raw.githubusercontent.com/patadejaguar/safeosmsdb/master/xx.vistas.sql -O $F2");
	system("wget https://raw.githubusercontent.com/patadejaguar/safeosmsdb/master/xx.functions.sql -O $F3");
	
	if(!file_exists($F1)){
		exit("No existen los archivos de importacion");
	}
	
	//Importar la base de datos
	$mysqlImport = new MySQLImporter($srvmysql, "root", $pwdroot);
	$mysqlImport->doImport($F1,$dbmysql,true);
	$errCrea	= false;
	foreach ($mysqlImport->errors as $iderr => $err){
		$msg		.= $err . "\r\n";
		$errCrea	= true;
	}
	if($errCrea == true){
		system("mysql --host=$srvmysql --user=root --password=$pwdroot --force --database=$dbmysql < $F1");
		$msg		.= "Intentando crear la DB en RAW\r\n";
	}
	system("mysql --host=$srvmysql --user=root --password=$pwdroot --force --database=$dbmysql < $F2");
	system("mysql --host=$srvmysql --user=root --password=$pwdroot --force --database=$dbmysql < $F3");
	
	
	system("mysql --host=$srvmysql --user=root --password=$pwdroot --force --database=$dbmysql < $F2");
	system("mysql --host=$srvmysql --user=root --password=$pwdroot --force --database=$dbmysql < $F3");
	
	//$CMDMYSQL --host=localhost --user=root --password=$ROOTPWD --force --database=$SUCURSAL < $PATH_HOME/$FILEFUNC
	
	//$mysqlImport->doImport($F2,$dbmysql);
	//$mysqlImport->doImport($F3,$dbmysql);
	
	//$mysqlImport->doImport($F2,$dbmysql);
	//$mysqlImport->doImport($F3,$dbmysql);
	
	//========================= Agregar Usuario y contraseña
	$cnn 		= new mysqli($srvmysql, "root", $pwdroot, $dbmysql);
	if ($cnn->connect_errno) {
		$msg	.= "ERROR EN LA CONEXION ROOT: ". $cnn->connect_error . " \n";
		exit;
	} else {
		$rs		= $cnn->query("CREATE USER '$usrmysql'@'localhost' IDENTIFIED BY '$pwdmysql'");
		$rs		= $cnn->query("GRANT ALL PRIVILEGES ON $dbmysql.* To '$usrmysql'@'localhost' IDENTIFIED BY '$pwdmysql'");
	}
	$cnn		= null;
	//=========================
	
	$cnn 		= new mysqli($srvmysql, $usrmysql, $pwdmysql, $dbmysql);
	
	if ($cnn->connect_errno) {
		$msg	.= "ERROR EN LA CONEXION : ". $cnn->connect_error . " \n";
		exit($msg);
	} else {
		$rs		= $cnn->query("SHOW TABLES IN $dbmysql");
		
		if($rs == false){
			$msg	.= "ERROR(". $cnn->error . ") \r\n";
		} else {
			
			
			$fileconfig		= "<?php\r\n";
			$fileconfig		.= "\$V_0a744893951e0d1706ff74	= \"$usrmysql\";\r\n";
			$fileconfig		.= "\$V_9003d1df22eb4d38200150	= \"$pwdmysql\";\r\n";
			$fileconfig		.= "\$sucursal 			= \"matriz\";\r\n";
			$fileconfig		.= "\$db_de_trabajo			= \"$dbmysql\";\r\n";
			$fileconfig		.= "\$SAFEPathRoot			= \"$urlpath\";\r\n";
			$fileconfig		.= "\$os_path_phpreports_engine	= \"\$SAFEPathRoot/reports\";\r\n";
			$fileconfig		.= "\$os_path_includes_str		= ini_get(\"include_path\").\":\$SAFEPathRoot/reports:\$SAFEPathRoot/libs:\$SAFEPathRoot/core\";\r\n";
			$fileconfig		.= "\$os_path_php_log		= \"/var/log/php.log\";\r\n";
			$fileconfig		.= "\$os_path_mysql_log		= \"/var/log/mysql/mysql-slow.log\";\r\n";
			$fileconfig		.= "\$os_path_apache_log		= \"/var/log/apache2/error.log\";\r\n";
			$fileconfig		.= "\$os_path_htdocs		= \"\$SAFEPathRoot\";\r\n";
			$fileconfig		.= "\$os_path_ctw			= \"\";\r\n";
			$fileconfig		.= "\$os_path_bks			= \"$dir/bks-$dbmysql\";\r\n";
			$fileconfig		.= "\$os_path_tmp			= \"$dir/tmp-$dbmysql\";\r\n";
			$fileconfig		.= "\$V_cf1e8c14e54505f60aa10c	= \"$urlsys\";\r\n";
			$fileconfig		.= "\$V_67e92c8765a9bc7fb2d335	= \"$srvmysql\";\r\n";
			//$fileconfig		.= "//\$fecha_de_inicio_operaciones	= \"\$VFecha\";\r\n";
			if($nocache == true){
				$fileconfig		.= "\$os_en_memcache\t\t\t\t= false;\r\n";
				
			} else {
				$fileconfig		.= "\$os_en_memcache\t\t\t\t= true;\r\n";
			}
			$fileconfig		.= "\r\n";
			$fileconfig		.= "\r\n?>";
			
			if(file_put_contents($privateconfig, $fileconfig, FILE_TEXT | LOCK_EX) == false){
				$msg	.= "ERROR AL GUARDAR EL ARCHIVO  \r\n";
			} else {
				
				header("location: ../../index.php"); 
				
				exit;
			}
		}	
	}
} else {
	?>
	
<!DOCTYPE HTML>
<!--
	Photon by HTML5 UP
	html5up.net | @ajlkn
	Free for personal and commercial use under the CCA 3.0 license (html5up.net/license)
-->
<html>
	<head>
		<title>Instalador SAFE-OSMS</title>
		<meta charset="utf-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1" />

	</head>
	<body>
	<style>
body {
  font: 16px Helvetica;
  background: #1abc9c;
}

.container {
  width: 600px;
  margin: 2em auto;
  overflow: hidden;
  background: white;
  border-radius: 5px;
}

.message,
.contact,
.name,
.footer,
header,
footer,
textarea {
  display: block;
  padding: 0;
  margin: 0;
  border: 0;
  clear: both;
  overflow: hidden;
}

header, footer {
  height: 75px;
  background: rgba(0, 0, 0, 0.05);
  line-height: 75px;
  padding-left: 20px;
  border-radius: 5px 5px 0 0;
}
header h1, footer h1 {
  font-size: 1.2em;
  text-transform: uppercase;
  color: rgba(51, 51, 51, 0.4);
}

.first,
.last {
  float: left;
  width: 278px;
  margin: 0;
  padding: 0 0 0 20px;
  border: 1px solid rgba(0, 0, 0, 0.1);
  height: 50px;
}

.last {
  width: 279px;
  border-left: 0;
}

.email, textarea {
  height: 50px;
  width: 578px;
  line-height: 50px;
  padding: 0 0 0 20px;
  border-top: 0;
  border-left: 1px solid rgba(0, 0, 0, 0.1);
  border-right: 1px solid rgba(0, 0, 0, 0.1);
  border-bottom: 1px solid rgba(0, 0, 0, 0.1);
}

textarea {
  height: 200px;
}

footer {
  height: 49px;
  border-top: 1px dashed rgba(0, 0, 0, 0.3);
  border-radius: 0 0 5px 5px;
  padding-left: 0;
  padding-right: 20px;
}
footer button {
  height: 32px;
  background: #e74c3c;
  border-radius: 5px;
  border: 0;
  margin: 7px 0;
  color: white;
  float: right;
  padding: 0 20px 0 20px;
  border-bottom: 3px solid #c0392b;
  transition: all linear .2s;
}
footer button:hover {
  background: #c0392b;
}
footer button:focus {
  outline: none;
}

.first:focus,
.last:focus,
.email:focus,
textarea:focus,
textarea:focus {
  outline: none;
  background: rgba(52, 152, 219, 0.1);
  color: rgba(51, 51, 51, 0.7);
}
	
</style>
	
	<!-- 
	

$sucursal		= (isset($_REQUEST["idsucursal"])) ?  $_REQUEST["idsucursal"] : "";

	 -->
	 
<form name="sl" action="./install.php?action=save">	 
<div class='container'>
  <header>
    <h1>Instalador SAFE-OSMS v 2.0</h1>
  </header>

  <div class='name'>
    <input class='first' placeholder='Usuario MySQL' type='text' name='idusuario' value='<?php echo $userPos; ?>'>
    <input class='last' placeholder='Contraseña MySQL' type='text' name='idpassword' value='<?php echo $userPwd; ?>'>
  </div>

  <div class='name'>
    <input class='last' placeholder='Contraseña ROOT MySQL' type='password' name='idpwdroot'>
  </div>
    

  <div class='name'>
    <input class='first' placeholder='Servidor MySQL como 127.0.0.1' type='text' name='idservidor' value='localhost'>
    <input class='last' placeholder='Nombre de la Base de Datos' type='text' name='iddb'  value='<?php echo $ndb; ?>'>
  </div>
<div class='contact'>
    <input class='email' placeholder='Direccion Web del Servidor como http://servidor.com/' type='text' name='idurl' value='<?php echo $lurl; ?>'>
</div>
      
<div class='contact'>
    <input type="hidden" name="idnocache" value="1" />
    <input type="checkbox" name="idnocache" value="0">Usar Memcache<br>
</div>      

<!-- <div class='contact'>
    <input type="hidden" name="iduseeng" value="1" />
    <input type="checkbox" name="iduseeng" value="0">English Version<br>
</div> -->
      <input type="hidden" name="debug" id="debug" value="<?php echo ($ondebug == true) ? "true" : "false"; ?>" />
  <!--  <div class='contact'>
    <input class='email' placeholder='E-mail Address' type='text'>
  </div>
  <div class='message'>
    <textarea placeholder='Your Suggestions Here!'></textarea>
  </div> -->
  <footer>
    <button>Guardar Configuracion</button>
  </footer>
</div>

</form>

	</body>
	
</html>	
<?php
}


//var_dump($_REQUEST);
?>