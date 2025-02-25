<?php
$click_retries = 5;
$url_expire_day = 7;
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require "/etc/fusionpbx/config.php";
require_once "/var/www/freeswitchpbx/public/resources/classes/database.php";
$db = pg_connect( "host = ".$db_host." port = ".$db_port." dbname = ".$db_name." user = ".$db_username." password=".$db_password.""  );
if(!$db) {
	echo "Error : Unable to open database\n";
} 
$v_password_result = pg_query($db,"SELECT * FROM bria_password_manage where uuid='".$_GET['uuid']."'");
$v_password_row=pg_fetch_all($v_password_result);
$error_msg = "";
$date_compare = strtotime(date('Y-m-d H:i:s', strtotime('-'.$url_expire_day.' days')));
if(!empty($v_password_row)){
	$count = $v_password_row[0]['count'];
	$encrypted = $v_password_row[0]['password'];
	if($count >= $click_retries){
		$error_msg = "Maximum Attempts Reached Please Try Again.";
	}
	if($date_compare >= $v_password_row[0]['time']){
		$error_msg = "Link Is Expired.";
	}
	$update_count = $count+1;
	$sql = "select user_uuid from v_users where username=:username ";
	$parameters['username'] = 'admin';
	$database = new database;
	$row = $database->select($sql, $parameters, 'row');
	$update_uuid = $row['user_uuid'];

//	$update_uuid = $_SESSION['user_uuid'];
	pg_query($db,"update bria_password_manage set count = '".$update_count."',update_date= now(),update_user='".$update_uuid."' where uuid='".$_GET['uuid']."'");
}else{
	$error_msg = "Data Not Found Please Generate Password Again.";
}
$delete_query = "DELETE from bria_password_manage where time <='".$date_compare."' OR count > '".$click_retries."' ";
pg_query($db,$delete_query);

if(isset($_SERVER['HTTPS'])){
	$protocol = ($_SERVER['HTTPS'] && $_SERVER['HTTPS'] != "off") ? "https" : "http";
}
else{
	$protocol = 'http';
}
$host_name = $protocol . "://" . $_SERVER['HTTP_HOST']."/";

if(!isset($_GET) || !isset($_GET['uuid']) || $_GET['uuid'] == ""){
		echo "access denied";
		exit;
}
if($error_msg == '' && $encrypted != ''){
//JA_START
	$ciphering = "AES-128-CTR";
	$decryption_iv = '1234567891011121';
	$options = 0;
	$decryption_key = "0262993263";
	$decrypted=openssl_decrypt ($encrypted, $ciphering,$decryption_key, $options, $decryption_iv);
/*
	$string_key = 'AXT5p92wcdjWb6jiupHWqv5NUP83aCYG'; // note the spaces
	function decode_params($string) {
		$data = str_replace ( array (
				'-',
				'$'
		), array (
				'+',
				'/'
		), $string );
		$mod4 = strlen ( $data ) % 4;
		if ($mod4) {
			$data .= substr ( '====', $mod4 );
		}
		return base64_decode ( $data );
	}
	$crypttext = decode_params($encrypted);
	$ivSize = openssl_cipher_iv_length('BF-ECB');
	$iv = substr($crypttext, 0, $ivSize);
	$decrypted = openssl_decrypt(substr($crypttext, $ivSize), 'BF-ECB', $string_key, OPENSSL_RAW_DATA, $iv);
*/
//JA_END
}else{
	$error_msg = 'This password link has expired';
}
?>
<style>
.subject-info-box-1,
.subject-info-box-2 {
    float: left;
    width: 20%;
}
.subject-info-box-1{
	margin-left: 350px
}
.subject-info-box-1,
.subject-info-box-2 select {
        height: 200px;
        padding: 0;
}.subject-info-box-1,
.subject-info-box-2 select option {
            padding: 4px 10px 4px 10px;
        }
.subject-info-box-1,
.subject-info-box-2 select option:hover {
        }
.subject-info-arrows {
    float: left;
    width: 10%;
}
.subject-info-arrows{
        margin-top: 180px;
    }
.subject-info-arrows input{
        width: 70%;
        margin-top: 5px;
    }

.lstBox1,.lstBox2{
height:500px !important;
}
</style>

			<!DOCTYPE html>
	
<html xmlns='http://www.w3.org/1999/xhtml' xml:lang='en' lang='en'>
<head>
<meta charset='utf-8'>
<meta http-equiv='Content-Type' content='text/html; charset=UTF-8'>
<meta http-equiv='X-UA-Compatible' content='IE=edge'>
<meta name='viewport' content='width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no' />

	<link rel='stylesheet' type='text/css' href='../resources/bootstrap/css/bootstrap.min.css.php'>
	<link rel='stylesheet' type='text/css' href='/resources/bootstrap/css/bootstrap-tempusdominus.min.css.php'>
	<link rel='stylesheet' type='text/css' href='/resources/bootstrap/css/bootstrap-colorpicker.min.css.php'>
	<link rel='stylesheet' type='text/css' href='/resources/fontawesome/css/all.min.css.php'>
	<link rel='stylesheet' type='text/css' href='/themes/default/css.php'>

	
	<link rel='icon' href='/themes/default/favicon.ico'>

	<title>Junction Cloud Connections</title>

</head>
<script>
function myFunction() {
  /* Get the text field */
  var copyText = document.getElementById("myInput");

  /* Select the text field */
  copyText.select();
  copyText.setSelectionRange(0, 99999); /*For mobile devices*/

  /* Copy the text inside the text field */
  document.execCommand("copy");

  /* Alert the copied text */
  alert("Copied the text: " + copyText.value);
}
</script>

<body>
			<div id='domains_container'>
				<div id='domains_block'>
					<div id='domains_header'>
						<input id='domains_hide' type='button' class='btn' style='float: right' value="Close">
						<a id='domains_title' href='/core/domains/domains.php'>Domains <span style='font-size: 80%;'>(11)</span></a>
						<br><br>
						<input type='text' id='domains_filter' class='formfld' style='margin-left: 0; min-width: 100%; width: 100%;' placeholder="Search..." onkeyup='domain_search(this.value)'>
					</div>
					<div id='domains_list'>
																																																																																														<div id='demo.pbx02.jcnt.net' class='domains_list_item' style='background-color: #eaedf2' onclick="document.location.href='/core/domains/domains.php?domain_uuid=68834df9-5314-4e3a-b761-ffe836fd0bc9&domain_change=true';">
																							<a href='/core/domains/domains.php?domain_uuid=68834df9-5314-4e3a-b761-ffe836fd0bc9&domain_change=true' >demo.pbx02.jcnt.net</a>
																																													<span class='domain_list_item_description' title="Demo Units"> - Demo Units</span>
																									</div>
																																																																																																												<div id='test3' class='domains_list_item' style='background-color: #ffffff' onclick="document.location.href='/core/domains/domains.php?domain_uuid=c48e062b-38e5-4dfc-936c-1cc671f01ffe&domain_change=true';">
																							<a href='/core/domains/domains.php?domain_uuid=c48e062b-38e5-4dfc-936c-1cc671f01ffe&domain_change=true' >test3</a>
																						</div>
																																																																																																												<div id='45.76.15.214' class='domains_list_item' style='background-color: #eeffee' onclick="document.location.href='/core/domains/domains.php?domain_uuid=bde18220-3779-4e83-9bc5-341ea15553d7&domain_change=true';">
																							<a href='/core/domains/domains.php?domain_uuid=bde18220-3779-4e83-9bc5-341ea15553d7&domain_change=true' style='font-weight: bold;'>45.76.15.214</a>
																						</div>
																																																																																																												<div id='test1.co.in' class='domains_list_item' style='background-color: #eaedf2' onclick="document.location.href='/core/domains/domains.php?domain_uuid=9ffdd4be-4c71-40ce-99d5-4daf7e0d6541&domain_change=true';">
																							<a href='/core/domains/domains.php?domain_uuid=9ffdd4be-4c71-40ce-99d5-4daf7e0d6541&domain_change=true' >test1.co.in</a>
																																													<span class='domain_list_item_description' title="Test1"> - Test1</span>
																									</div>
																																																																																																												<div id='test2.co.in' class='domains_list_item' style='background-color: #ffffff' onclick="document.location.href='/core/domains/domains.php?domain_uuid=5e47033b-9e40-4902-b02b-2cb1d2fbf3b4&domain_change=true';">
																							<a href='/core/domains/domains.php?domain_uuid=5e47033b-9e40-4902-b02b-2cb1d2fbf3b4&domain_change=true' >test2.co.in</a>
																																													<span class='domain_list_item_description' title="test2"> - test2</span>
																									</div>
																																																																																																												<div id='test3.co.in' class='domains_list_item' style='background-color: #eaedf2' onclick="document.location.href='/core/domains/domains.php?domain_uuid=0491b6d4-5e50-4219-9790-743dac4eae6d&domain_change=true';">
																							<a href='/core/domains/domains.php?domain_uuid=0491b6d4-5e50-4219-9790-743dac4eae6d&domain_change=true' >test3.co.in</a>
																																													<span class='domain_list_item_description' title="test3"> - test3</span>
																									</div>
																																																																																																												<div id='test4.co.in' class='domains_list_item' style='background-color: #ffffff' onclick="document.location.href='/core/domains/domains.php?domain_uuid=64813c9e-e1cd-4507-a1e5-b47c1f99fca9&domain_change=true';">
																							<a href='/core/domains/domains.php?domain_uuid=64813c9e-e1cd-4507-a1e5-b47c1f99fca9&domain_change=true' >test4.co.in</a>
																																													<span class='domain_list_item_description' title="test"> - test</span>
																									</div>
																																																																																																												<div id='test123123' class='domains_list_item' style='background-color: #eaedf2' onclick="document.location.href='/core/domains/domains.php?domain_uuid=5ec6c680-4304-4226-b9eb-203033bb58c7&domain_change=true';">
																							<a href='/core/domains/domains.php?domain_uuid=5ec6c680-4304-4226-b9eb-203033bb58c7&domain_change=true' >test123123</a>
																																													<span class='domain_list_item_description' title="TESTT"> - TESTT</span>
																									</div>
																																																																																																												<div id='sdgfdgfdg' class='domains_list_item' style='background-color: #ffffff' onclick="document.location.href='/core/domains/domains.php?domain_uuid=06bb6ede-3243-4111-ae50-66faf6975979&domain_change=true';">
																							<a href='/core/domains/domains.php?domain_uuid=06bb6ede-3243-4111-ae50-66faf6975979&domain_change=true' >sdgfdgfdg</a>
																																													<span class='domain_list_item_description' title="fgfgfg"> - fgfgfg</span>
																									</div>
																																																																																																												<div id='final_test' class='domains_list_item' style='background-color: #eaedf2' onclick="document.location.href='/core/domains/domains.php?domain_uuid=6467afdd-5c35-4d94-8d61-bc9697890dc8&domain_change=true';">
																							<a href='/core/domains/domains.php?domain_uuid=6467afdd-5c35-4d94-8d61-bc9697890dc8&domain_change=true' >final_test</a>
																																													<span class='domain_list_item_description' title="final_test"> - final_test</span>
																									</div>
																																																																																																												<div id='testpbx.jcnt.net' class='domains_list_item' style='background-color: #ffffff' onclick="document.location.href='/core/domains/domains.php?domain_uuid=0072365b-fdd8-4695-a51f-65cc12766724&domain_change=true';">
																							<a href='/core/domains/domains.php?domain_uuid=0072365b-fdd8-4695-a51f-65cc12766724&domain_change=true' >testpbx.jcnt.net</a>
																						</div>
																									</div>


				</div>
			</div>

		
			<div id='qr_code_container' style='display: none;' onclick='$(this).fadeOut(400);'>
			<table cellpadding='0' cellspacing='0' border='0' width='100%' height='100%'><tr><td align='center' valign='middle'>
				<span id='qr_code' onclick="$('#qr_code_container').fadeOut(400);"></span>
			</td></tr></table>
		</div>

						 				<nav class='navbar' style='background-color: transparent;' >
	<div class='container-fluid' style='width: calc(90% - 20px); padding: 0;'>
		<div class='navbar-brand'>
			<a href='/'>				<img id='menu_brand_image' class='navbar-logo' src='/themes/default/images/logo_login.png' title="Junction Cloud Connections"></a>
			<a style='margin: 0;'></a>
		</div>
		<button type='button' class='navbar-toggler' data-toggle='collapse' data-target='#main_navbar' aria-expanded='false' aria-controls='main_navbar' aria-label='Toggle Menu'>
			<span class='fas fa-bars'></span>
		</button>
	</div>
</nav>

				<div class='container-fluid' style='padding: 0;' align='center'>

						<div id='main_content'>
<div class='action_bar' id='action_bar'>
	<div class='heading'><b>Your password is:</b></div>
	<br/><br/>
	<div style='clear: both;'></div>
</div>
	<div>
<?php if($error_msg == ""){ ?>
<input class='formfld' type='text' name='extension' autocomplete='new-password' maxlength='255' value='<?php echo $decrypted; ?>' required='required' id="myInput" style="height: 100px;width: 400px;" readonly> <button onclick="myFunction()">Copy text</button>
<?php }else{ ?>
 <b><?php echo $error_msg; ?></b>

<?php } ?>
	</div>

			</div>
			<div id='footer'>
				<span class='footer'>&copy; Copyright 2021 - <?= date('Y'); ?> <a href='http://www.junctionconnections.com' class='footer' target='_blank'>junctionconnections.com</a> All rights reserved.</span>
			</div>
			</div>
		
</body>
</html>
