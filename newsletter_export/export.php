<?php
/*************************************
	Newsletter Export Script f�r Magento
	Digineo GmbH 2009 | www.digineo.de
	Author: Tim Kretschmer
	Version 1.0
	Lizenz: GNU 3
*************************************/
//The Authentication. Please Update!
$auth_user = "IMPORTANT TO";
$auth_password = "CHANGE";
//true = only members who want to receive newsletter
//false = export all members of database
$no_spam = true;





if($_SERVER['PHP_AUTH_PW'] != $auth_password || $auth_password == "" || $_SERVER['PHP_AUTH_USER'] != $auth_user) {
	header('WWW-Authenticate: Basic realm="Export"');
	header('HTTP/1.0 401 Unauthorized');
	die("not authorized");
}


$config = '../configuration.php';

if(!file_exists($config)) {
	die("Konfigurationsdatei nicht gefunden");
}
include($config);

function encode_field($field) {
	return htmlspecialchars(utf8_encode($field));
}

$cfg = new JConfig();


@set_time_limit(0);
mysql_connect ($cfg->host, $cfg->user, $cfg->password);
mysql_select_db($cfg->db);

function unsubscribe() {
	global $cfg;
	$sql = "UPDATE ".$cfg->dbprefix ."users SET sendEmail=0 WHERE id=".mysql_real_escape_string($_POST['recipient']['key']);
	mysql_query($sql);	
	header("HTTP/1.0 200 OK");	
}

function export() {
	global $no_spam;
	global $cfg;
	
	$sql ="SELECT * FROM ".$cfg->dbprefix ."users";
	if( $no_spam ) {	
		$sql .= " WHERE sendEmail=1";
	}		

	$export_query = mysql_unbuffered_query($sql);

	header ("content-type: text/xml");
	echo "<?xml version='1.0' encoding='utf-8' ?>\n";
	echo "<recipients>\n";
	while($user = mysql_fetch_assoc($export_query)) {


		echo "
			<recipient>
				<key>".encode_field($user['id'])."</key>
				<email>".encode_field($user['email'])."</email>
				<name>".encode_field($user['name'])."</name>
				<only_text>0</only_text>
				<approved>1</approved>
			</recipient>				
		";	
	}
	echo "</recipients>";	
}

switch($_SERVER['REQUEST_METHOD']) {
	case "GET":
		export();
		break;
	case "POST":
		unsubscribe();
		break;		
	default:
		header("HTTP/1.0 405 Method Not Allowed");
		die("Method not allowed");
}

?>
