
<?php
	include_once "bria_conf.php";

//includes

	if($_POST > 0 && isset($_POST['group_ajax'])){
		$sip_profile_array = array();
		$url = $api_url."/stretto/prov/profile?groupName=".$_POST['group_ajax'];
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);  
		curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_DIGEST); 
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
		curl_setopt($ch, CURLOPT_USERPWD, $api_username.":".$api_password);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
		curl_setopt($ch, CURLOPT_HEADER, false); 
		$result = curl_exec($ch);  
		$sip_xml_array = json_decode(json_encode((array)simplexml_load_string($result)),true);
		if(isset($sip_xml_array['CcsProfile'])){
			foreach($sip_xml_array['CcsProfile'] as $sip_value){
				$sip_profile_array[$sip_value['@attributes']['profileName']] = $sip_value['@attributes']['profileName'];
			}
		}
		$return_profile = "";
		$return_profile .= "    <select class='formfld' name='sipprofile' id='sipprofile'>\n";
		if(!empty($sip_profile_array)){
			foreach($sip_profile_array as $sip_key => $sip_value){
				$return_profile .= "    <option value=\"".$sip_key."\" >".$sip_value."</option>\n";
			}
		}
		$return_profile .= "    </select>\n";

		curl_close($ch);
		echo $return_profile; exit;
	}

//set the include path
        $conf = glob("{/usr/local/etc,/etc}/fusionpbx/config.conf", GLOB_BRACE);
        set_include_path(parse_ini_file($conf[0])['document.root']);


	include 'bria_group_content.php';
//	require_once "root.php";
	require_once "resources/require.php";
	require_once "resources/check_auth.php";
	include_once "resources/phpmailer/class.phpmailer.php";
	include_once "resources/phpmailer/class.smtp.php";
//if((strtotime(gmdate('Y-m-d')) > $group_content['date']) || (isset($_GET) && $_GET['manual'] == 1)){
if((isset($_GET) && $_GET['manual'] == 1)){
	shell_exec('cd /var/www/fusionpbx/app/extensions/ && php bria_group_generate.php >/dev/null 2>/dev/null &');
	if(isset($_GET) && $_GET['manual'] == 1){
		message::add("Bria Group Updated Successfully, it will take 5 to 10 Minutes to reflact in GUI.");
		header("Location: briaprofile.php");
		exit;
	}
}else{
	$group_array = $group_content['group_array'];
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
<?php
/*
	FusionPBX
	Version: MPL 1.1

	The contents of this file are subject to the Mozilla Public License Version
	1.1 (the "License"); you may not use this file except in compliance with
	the License. You may obtain a copy of the License at
	http://www.mozilla.org/MPL/

	Software distributed under the License is distributed on an "AS IS" basis,
	WITHOUT WARRANTY OF ANY KIND, either express or implied. See the License
	for the specific language governing rights and limitations under the
	License.

	The Original Code is FusionPBX

	The Initial Developer of the Original Code is
	Mark J Crane <markjcrane@fusionpbx.com>
	Portions created by the Initial Developer are Copyright (C) 2008-2020
	the Initial Developer. All Rights Reserved.

	Contributor(s):
	Mark J Crane <markjcrane@fusionpbx.com>
	Luis Daniel Lucio Quiroz <dlucio@okay.com.mx>
*/

//includes

	if (permission_exists('bria_profile_add')) {
		//access granted
	}
	else {
		echo "access denied";
		exit;
	}

	$smtp =array();
	$smtp['secure'] 	= $_SESSION['email']['smtp_secure']['text'];
	$smtp['auth'] 		= $_SESSION['email']['smtp_auth']['text'];
	$smtp['username'] 	= $_SESSION['email']['smtp_username']['text'];
	$smtp['password'] 	= $_SESSION['email']['smtp_password']['text'];
	$smtp['from'] 		= $_SESSION['email']['smtp_from']['text'];
	$smtp['from_name'] 	= $_SESSION['email']['smtp_from_name']['text'];
	$smtp['host']           = $_SESSION['email']['smtp_host']['text'];
	$smtp['port']           = $_SESSION['email']['smtp_port']['text'];



	$password_strength = $_SESSION["extension"]["password_strength"]["numeric"];
	$ext_password = generate_password("20", $password_strength);
	if (count($_POST) > 0) {
		if($_POST['account1--accountName'] != "" &&  $_POST['group'] != "0"){
			$url = $api_url."/stretto/prov/profile?method=POST&profileName=".$_POST['account1--accountName']."&groupName=".$_POST['group'];
			$ch = curl_init();     
			curl_setopt($ch, CURLOPT_URL, $url);  
			curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_DIGEST); 
			curl_setopt($ch, CURLOPT_USERPWD, $api_username.":".$api_password);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_POST, true);
			$result = curl_exec($ch); 
			$xml_array = json_decode(json_encode((array)simplexml_load_string($result)),true);
			curl_close($ch);
			if(!empty($xml_array)){
				if($xml_array['0'] ==""){
					foreach($_POST as $key => $value){
					   if($key != "group"){
						$url = $api_url."/stretto/prov/profile/attribute?method=POST&profileName=".$_POST['account1--accountName']."&groupName=".$_POST['group']."&attributeName=".str_replace("--",".",$key)."&attributeValue=".$value."";
						$ch = curl_init();     
						curl_setopt($ch, CURLOPT_URL, $url);  
						curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_DIGEST); 
						curl_setopt($ch, CURLOPT_USERPWD, $api_username.":".$api_password);
						curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
						curl_setopt($ch, CURLOPT_POST, true);
						$result = curl_exec($ch); 
						$xml_sub_array = json_decode(json_encode((array)simplexml_load_string($result)),true);
					   }
					}
					message::add("Bria Profile Added Successfully");
					header("Location: briaprofile.php");
					exit;
				}else{
					message::add(ltrim($xml_array['0']).", Please try again.",'negative');
					header('Location: bria_profile_add.php');
					exit;

				}
			}else{
				message::add("Something wrong in API",'negative');
				header('Location: bria_profile_add.php');
				exit;
			}
		}else{
			message::add("All fields are requires please fillup all fields",'negative');
			header('Location: bria_profile_add.php');
			exit;
		}

	}


//get the device lines
	$sql = "select d.device_mac_address, d.device_template, d.device_description, l.device_line_uuid, l.device_uuid, l.line_number ";
	$sql .= "from v_device_lines as l, v_devices as d ";
	$sql .= "where (l.user_id = :user_id_1 or l.user_id = :user_id_2)";
	$sql .= "and l.domain_uuid = :domain_uuid ";
	$sql .= "and l.device_uuid = d.device_uuid ";
	$sql .= "order by l.line_number, d.device_mac_address asc ";
	$parameters['user_id_1'] = $extension;
	$parameters['user_id_2'] = $number_alias;
	$parameters['domain_uuid'] = $domain_uuid;
	$database = new database;
	$device_lines = $database->select($sql, $parameters, 'all');
	unset($sql, $parameters);

//get the devices
	$sql = "select * from v_devices ";
	$sql .= "where domain_uuid = :domain_uuid ";
	$sql .= "order by device_mac_address asc ";
	$parameters['domain_uuid'] = $domain_uuid;
	$database = new database;
	$devices = $database->select($sql, $parameters, 'all');
	unset($sql, $parameters);

//get the device vendors
	$sql = "select name ";
	$sql .= "from v_device_vendors ";
	$sql .= "where enabled = 'true' ";
	$sql .= "order by name asc ";
	$database = new database;
	$device_vendors = $database->select($sql, null, 'all');
	unset($sql);

//get assigned users
	if (is_uuid($extension_uuid)) {
		$sql = "select u.username, e.user_uuid ";
		$sql .= "from v_extension_users as e, v_users as u ";
		$sql .= "where e.user_uuid = u.user_uuid  ";
		$sql .= "and u.user_enabled = 'true' ";
		$sql .= "and e.domain_uuid = :domain_uuid ";
		$sql .= "and e.extension_uuid = :extension_uuid ";
		$sql .= "order by u.username asc ";
		$parameters['domain_uuid'] = $domain_uuid;
		$parameters['extension_uuid'] = $extension_uuid;
		$database = new database;
		$assigned_users = $database->select($sql, $parameters, 'all');
		if (is_array($assigned_users) && @sizeof($assigned_users) != 0) {
			foreach($assigned_users as $row) {
				$assigned_user_uuids[] = $row['user_uuid'];
			}
		}
		unset($sql, $parameters, $row);
	}

//get the users
	$sql = "select * from v_users ";
	$sql .= "where domain_uuid = :domain_uuid ";
	if (is_array($assigned_user_uuids) && @sizeof($assigned_user_uuids) != 0) {
		foreach ($assigned_user_uuids as $index => $assigned_user_uuid) {
			$sql .= "and user_uuid <> :user_uuid_".$index." ";
			$parameters['user_uuid_'.$index] = $assigned_user_uuid;
		}
	}
	$sql .= "and user_enabled = 'true' ";
	$sql .= "order by username asc ";
	$parameters['domain_uuid'] = $domain_uuid;
	$database = new database;
	$users = $database->select($sql, $parameters, 'all');
	unset($sql, $parameters, $assigned_user_uuids, $assigned_user_uuid);

//get the destinations
	$sql = "select * from v_destinations ";
	$sql .= "where domain_uuid = :domain_uuid ";
	$sql .= "and destination_type = 'inbound' ";
	$sql .= "order by destination_number asc ";
	$parameters['domain_uuid'] = $domain_uuid;
	$database = new database;
	$destinations = $database->select($sql, $parameters, 'all');
	unset($sql, $parameters);

//change toll allow delimiter
	$toll_allow = str_replace(':',',', $toll_allow);

//set the defaults
	if (strlen($user_context) == 0) { $user_context = $_SESSION['domain_name']; }
	if (strlen($limit_max) == 0) { $limit_max = '5'; }
	if (strlen($limit_destination) == 0) { $limit_destination = 'error/user_busy'; }
	if (strlen($call_timeout) == 0) { $call_timeout = '30'; }
	if (strlen($call_screen_enabled) == 0) { $call_screen_enabled = 'false'; }
	if (strlen($user_record) == 0) { $user_record = $_SESSION['extension']['user_record_default']['text']; }
	if (strlen($voicemail_enabled) == 0) { $voicemail_enabled = $_SESSION['voicemail']['enabled_default']['boolean']; }

//create token
	$v_domain_sql_new = "select domain_name ";
	$v_domain_sql_new .= "from v_domains where domain_uuid = '".$domain_uuid."'";
	$database = new database;
	$v_domain_sql_new_result = $database->select($v_domain_sql_new, $parameters);
	$domain_name = $v_domain_sql_new_result[0]['domain_name'];
	$v_bria_setting_new = "select bria_group ";
	$v_bria_setting_new .= "from bria_setting where domain_uuid = '".$domain_uuid."'";
	$database = new database;
	$v_bria_setting_result = $database->select($v_bria_setting_new, $parameters);
	$bria_group = $v_bria_setting_result[0]['bria_group'];

	$object = new token;
	$token = $object->create($_SERVER['PHP_SELF']);

//begin the page content
	$document['title'] = "Bria Profile Add";//$text['title-extensions'];
	require_once "resources/header.php";
	echo "<form method='post' name='frm' id='frm'>\n";

	echo "<div class='action_bar' id='action_bar'>\n";
	echo "	<div class='heading'>";
	echo "<b>Bria Profile Add(Domain:".$domain_name.")</b>";
	echo 	"</div>\n";
	echo "	<div class='actions'>\n";
	echo button::create(['type'=>'button','label'=>$text['button-back'],'icon'=>$_SESSION['theme']['button_icon_back'],'id'=>'btn_back','link'=>'briaprofile.php'.(is_numeric($page) ? '?page='.$page : null)]);
	echo button::create(['type'=>'button','label'=>$text['button-save'],'icon'=>$_SESSION['theme']['button_icon_save'],'id'=>'btn_save','style'=>'margin-left: 15px;','onclick'=>'submit_form();']);
	echo "	</div>\n";
	echo "	<div style='clear: both;'></div>\n";
	echo "</div>\n";
	echo "<br><br>";
	echo "<table width='100%' border='0' cellpadding='0' cellspacing='0'>";
	echo "<tbody>";
	echo "<tr>";
	echo "<td width='30%' class='vncellreq' valign='top' align='left' nowrap='nowrap'>";
	echo "Group";
	echo"</td>";
	echo "<td  width='70%' class='vtable' align='left'>";
	echo "<input class='formfld' type='text' name='group' autocomplete='new-password' maxlength='255' value='".$bria_group."' required='required' onchange='profile_bria(this.value)'>";
/*	echo "    <select class='formfld' name='group' id='group' onchange='profile_bria(this.value)'>\n";
	if(!empty($group_array)){
		foreach($group_array as $group_key => $group_value){
			if(in_array($group_key,$domain_permission_list) || if_group("superadmin")){
				echo "    <option value=\"".$group_key."\" >".$group_value."</option>\n";
			}
		}
	}*/
	echo "    </select>\n";
	echo "    <br />\n";
	echo "</td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td width='30%' class='vncellreq' valign='top' align='left' nowrap='nowrap'>";
	echo "account1.accountName";
	echo"</td>";
	echo "<td  width='70%' class='vtable' align='left'>";
	echo "<input class='formfld' type='text' name='account1--accountName' autocomplete='new-password' maxlength='255' value='Junction PBX' required='required'>";
	echo "    <br />\n";
	echo "</td>";
	echo "</tr>";
	echo "</tbody>";
	echo "</table>";
	echo "<br><button id='advance_button' class='advance_button btn btn-default' style='margin-left:10%;'>Advance</button><br>";
	echo "<div id='advance_div' class='advance_div' style='display:none;'>";
	echo "<table width='100%' border='0' cellpadding='0' cellspacing='0'>";
	echo "<tbody>";

	echo "<tr>";
	echo "<td width='30%' class='vncellreq' valign='top' align='left' nowrap='nowrap'>";
	echo "account1.briaPush.enabled";
	echo"</td>";
	echo "<td  width='70%' class='vtable' align='left'>";
	echo "<input class='formfld' type='text' name='account1--briaPush--enabled' autocomplete='new-password' maxlength='255' value='1' required='required'>";
	echo "    <br />\n";
	echo "</td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td width='30%' class='vncellreq' valign='top' align='left' nowrap='nowrap'>";
	echo "account1.ignoreTlsCertVerify";
	echo"</td>";
	echo "<td  width='70%' class='vtable' align='left'>";
	echo "<input class='formfld' type='text' name='account1--ignoreTlsCertVerify' autocomplete='new-password' maxlength='255' value='true' required='required'>";
	echo "    <br />\n";
	echo "</td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td width='30%' class='vncellreq' valign='top' align='left' nowrap='nowrap'>";
	echo "mob.codecs.mobileNetwork.g711a.enabled";
	echo"</td>";
	echo "<td  width='70%' class='vtable' align='left'>";
	echo "<input class='formfld' type='text' name='mob--codecs--mobileNetwork--g711a--enabled' autocomplete='new-password' maxlength='255' value='true' required='required'>";
	echo "    <br />\n";
	echo "</td>";
	echo "</tr>";
	echo "</tr>";
	echo "<tr>";
	echo "<td width='30%' class='vncellreq' valign='top' align='left' nowrap='nowrap'>";
	echo "mob.codecs.mobileNetwork.g711a.priority";
	echo"</td>";
	echo "<td  width='70%' class='vtable' align='left'>";
	echo "<input class='formfld' type='text' name='mob--codecs--mobileNetwork--g711a--priority' autocomplete='new-password' maxlength='255' value='80' required='required'>";
	echo "    <br />\n";
	echo "</td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td width='30%' class='vncellreq' valign='top' align='left' nowrap='nowrap'>";
	echo "mob.codecs.mobileNetwork.g711u.enabled";
	echo"</td>";
	echo "<td  width='70%' class='vtable' align='left'>";
	echo "<input class='formfld' type='text' name='mob--codecs--mobileNetwork--g711u--enabled' autocomplete='new-password' maxlength='255' value='true' required='required'>";
	echo "    <br />\n";
	echo "</td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td width='30%' class='vncellreq' valign='top' align='left' nowrap='nowrap'>";
	echo "mob.codecs.mobileNetwork.g711u.priority";
	echo"</td>";
	echo "<td  width='70%' class='vtable' align='left'>";
	echo "<input class='formfld' type='text' name='mob.codecs--mobileNetwork--g711u--priority' autocomplete='new-password' maxlength='255' value='30' required='required'>";
	echo "    <br />\n";
	echo "</td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td width='30%' class='vncellreq' valign='top' align='left' nowrap='nowrap'>";
	echo "mob.codecs.mobileNetwork.g722.enabled";
	echo"</td>";
	echo "<td  width='70%' class='vtable' align='left'>";
	echo "<input class='formfld' type='text' name='mob--codecs--mobileNetwork--g722--enabled' autocomplete='new-password' maxlength='255' value='true' required='required'>";
	echo "    <br />\n";
	echo "</td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td width='30%' class='vncellreq' valign='top' align='left' nowrap='nowrap'>";
	echo "mob.codecs.mobileNetwork.g722.priority";
	echo"</td>";
	echo "<td  width='70%' class='vtable' align='left'>";
	echo "<input class='formfld' type='text' name='mob--codecs--mobileNetwork--g722--priority' autocomplete='new-password' maxlength='255' value='40' required='required'>";
	echo "    <br />\n";
	echo "</td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td width='30%' class='vncellreq' valign='top' align='left' nowrap='nowrap'>";
	echo "mob.codecs.mobileNetwork.g729.enabled";
	echo"</td>";
	echo "<td  width='70%' class='vtable' align='left'>";
	echo "<input class='formfld' type='text' name='mob--codecs--mobileNetwork--g729--enabled' autocomplete='new-password' maxlength='255' value='true' required='required'>";
	echo "    <br />\n";
	echo "</td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td width='30%' class='vncellreq' valign='top' align='left' nowrap='nowrap'>";
	echo "mob.codecs.mobileNetwork.g729.priority";
	echo"</td>";
	echo "<td  width='70%' class='vtable' align='left'>";
	echo "<input class='formfld' type='text' name='mob--codecs--mobileNetwork--g729--priority' autocomplete='new-password' maxlength='255' value='50' required='required'>";
	echo "    <br />\n";
	echo "</td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td width='30%' class='vncellreq' valign='top' align='left' nowrap='nowrap'>";
	echo "mob.codecs.mobileNetwork.gsm.enabled";
	echo"</td>";
	echo "<td  width='70%' class='vtable' align='left'>";
	echo "<input class='formfld' type='text' name='mob--codecs--mobileNetwork--gsm--enabled' autocomplete='new-password' maxlength='255' value='false' required='required'>";
	echo "    <br />\n";
	echo "</td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td width='30%' class='vncellreq' valign='top' align='left' nowrap='nowrap'>";
	echo "mob.codecs.mobileNetwork.gsm.priority";
	echo"</td>";
	echo "<td  width='70%' class='vtable' align='left'>";
	echo "<input class='formfld' type='text' name='mob--codecs--mobileNetwork--gsm--priority' autocomplete='new-password' maxlength='255' value='' required='required'>";
	echo "    <br />\n";
	echo "</td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td width='30%' class='vncellreq' valign='top' align='left' nowrap='nowrap'>";
	echo "mob.codecs.mobileNetwork.ilbc.enabled";
	echo"</td>";
	echo "<td  width='70%' class='vtable' align='left'>";
	echo "<input class='formfld' type='text' name='mob--codecs--mobileNetwork--ilbc--enabled' autocomplete='new-password' maxlength='255' value='false' required='required'>";
	echo "    <br />\n";
	echo "</td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td width='30%' class='vncellreq' valign='top' align='left' nowrap='nowrap'>";
	echo "mob.codecs.mobileNetwork.ilbc.priority";
	echo"</td>";
	echo "<td  width='70%' class='vtable' align='left'>";
	echo "<input class='formfld' type='text' name='mob--codecs--mobileNetwork--ilbc--priority' autocomplete='new-password' maxlength='255' value='' required='required'>";
	echo "    <br />\n";
	echo "</td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td width='30%' class='vncellreq' valign='top' align='left' nowrap='nowrap'>";
	echo "mob.codecs.mobileNetwork.opus.enabled";
	echo"</td>";
	echo "<td  width='70%' class='vtable' align='left'>";
	echo "<input class='formfld' type='text' name='mob--codecs--mobileNetwork--opus--enabled' autocomplete='new-password' maxlength='255' value='true' required='required'>";
	echo "    <br />\n";
	echo "</td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td width='30%' class='vncellreq' valign='top' align='left' nowrap='nowrap'>";
	echo "mob.codecs.mobileNetwork.opus.priority";
	echo"</td>";
	echo "<td  width='70%' class='vtable' align='left'>";
	echo "<input class='formfld' type='text' name='mob--codecs--mobileNetwork--opus--priority' autocomplete='new-password' maxlength='255' value='10' required='required'>";
	echo "    <br />\n";
	echo "</td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td width='30%' class='vncellreq' valign='top' align='left' nowrap='nowrap'>";
	echo "mob.codecs.mobileNetwork.silk.enabled";
	echo"</td>";
	echo "<td  width='70%' class='vtable' align='left'>";
	echo "<input class='formfld' type='text' name='mob--codecs--mobileNetwork--silk--enabled' autocomplete='new-password' maxlength='255' value='false' required='required'>";
	echo "    <br />\n";
	echo "</td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td width='30%' class='vncellreq' valign='top' align='left' nowrap='nowrap'>";
	echo "mob.codecs.mobileNetwork.silk.priority";
	echo"</td>";
	echo "<td  width='70%' class='vtable' align='left'>";
	echo "<input class='formfld' type='text' name='mob--codecs--mobileNetwork--silk--priority' autocomplete='new-password' maxlength='255' value='' required='required'>";
	echo "    <br />\n";
	echo "</td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td width='30%' class='vncellreq' valign='top' align='left' nowrap='nowrap'>";
	echo "mob.codecs.mobileNetwork.silkhd.enabled";
	echo"</td>";
	echo "<td  width='70%' class='vtable' align='left'>";
	echo "<input class='formfld' type='text' name='mob--codecs--mobileNetwork--silkhd--enabled' autocomplete='new-password' maxlength='255' value='false' required='required'>";
	echo "    <br />\n";
	echo "</td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td width='30%' class='vncellreq' valign='top' align='left' nowrap='nowrap'>";
	echo "mob.codecs.mobileNetwork.silkhd.priority";
	echo"</td>";
	echo "<td  width='70%' class='vtable' align='left'>";
	echo "<input class='formfld' type='text' name='mob--codecs--mobileNetwork--silkhd--priority' autocomplete='new-password' maxlength='255' value='' required='required'>";
	echo "    <br />\n";
	echo "</td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td width='30%' class='vncellreq' valign='top' align='left' nowrap='nowrap'>";
	echo "mob.codecs.mobileNetwork.silkswb.enabled";
	echo"</td>";
	echo "<td  width='70%' class='vtable' align='left'>";
	echo "<input class='formfld' type='text' name='mob--codecs--mobileNetwork--silkswb--enabled' autocomplete='new-password' maxlength='255' value='false' required='required'>";
	echo "    <br />\n";
	echo "</td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td width='30%' class='vncellreq' valign='top' align='left' nowrap='nowrap'>";
	echo "mob.codecs.mobileNetwork.silkswb.priority";
	echo"</td>";
	echo "<td  width='70%' class='vtable' align='left'>";
	echo "<input class='formfld' type='text' name='mob--codecs--mobileNetwork--silkswb--priority' autocomplete='new-password' maxlength='255' value='' required='required'>";
	echo "    <br />\n";
	echo "</td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td width='30%' class='vncellreq' valign='top' align='left' nowrap='nowrap'>";
	echo "mob.codecs.video.h264.enabled";
	echo"</td>";
	echo "<td  width='70%' class='vtable' align='left'>";
	echo "<input class='formfld' type='text' name='mob--codecs--video--h264--enabled' autocomplete='new-password' maxlength='255' value='true' required='required'>";
	echo "    <br />\n";
	echo "</td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td width='30%' class='vncellreq' valign='top' align='left' nowrap='nowrap'>";
	echo "mob.codecs.video.h264.priority";
	echo"</td>";
	echo "<td  width='70%' class='vtable' align='left'>";
	echo "<input class='formfld' type='text' name='mob--codecs--video--h264--priority' autocomplete='new-password' maxlength='255' value='90' required='required'>";
	echo "    <br />\n";
	echo "</td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td width='30%' class='vncellreq' valign='top' align='left' nowrap='nowrap'>";
	echo "mob.codecs.video.vp8.enabled";
	echo"</td>";
	echo "<td  width='70%' class='vtable' align='left'>";
	echo "<input class='formfld' type='text' name='mob--codecs--video--vp8--enabled' autocomplete='new-password' maxlength='255' value='true' required='required'>";
	echo "    <br />\n";
	echo "</td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td width='30%' class='vncellreq' valign='top' align='left' nowrap='nowrap'>";
	echo "mob.codecs.video.vp8.priority";
	echo"</td>";
	echo "<td  width='70%' class='vtable' align='left'>";
	echo "<input class='formfld' type='text' name='mob--codecs.video--vp8.priority' autocomplete='new-password' maxlength='255' value='80' required='required'>";
	echo "    <br />\n";
	echo "</td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td width='30%' class='vncellreq' valign='top' align='left' nowrap='nowrap'>";
	echo "mob.codecs.wifiNetwork.g711a.enabled";
	echo"</td>";
	echo "<td  width='70%' class='vtable' align='left'>";
	echo "<input class='formfld' type='text' name='mob--codecs--wifiNetwork--g711a--enabled' autocomplete='new-password' maxlength='255' value='true' required='required'>";
	echo "    <br />\n";
	echo "</td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td width='30%' class='vncellreq' valign='top' align='left' nowrap='nowrap'>";
	echo "mob.codecs.wifiNetwork.g711a.priority";
	echo"</td>";
	echo "<td  width='70%' class='vtable' align='left'>";
	echo "<input class='formfld' type='text' name='mob--codecs--wifiNetwork--g711a--priority' autocomplete='new-password' maxlength='255' value='80' required='required'>";
	echo "    <br />\n";
	echo "</td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td width='30%' class='vncellreq' valign='top' align='left' nowrap='nowrap'>";
	echo "mob.codecs.wifiNetwork.g711u.enabled";
	echo"</td>";
	echo "<td  width='70%' class='vtable' align='left'>";
	echo "<input class='formfld' type='text' name='mob--codecs--wifiNetwork--g711u--enabled' autocomplete='new-password' maxlength='255' value='true' required='required'>";
	echo "    <br />\n";
	echo "</td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td width='30%' class='vncellreq' valign='top' align='left' nowrap='nowrap'>";
	echo "mob.codecs.wifiNetwork.g711u.priority";
	echo"</td>";
	echo "<td  width='70%' class='vtable' align='left'>";
	echo "<input class='formfld' type='text' name='mob--codecs--wifiNetwork--g711u--priority' autocomplete='new-password' maxlength='255' value='30' required='required'>";
	echo "    <br />\n";
	echo "</td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td width='30%' class='vncellreq' valign='top' align='left' nowrap='nowrap'>";
	echo "mob.codecs.wifiNetwork.g722.enabled";
	echo"</td>";
	echo "<td  width='70%' class='vtable' align='left'>";
	echo "<input class='formfld' type='text' name='mob--codecs--wifiNetwork--g722--enabled' autocomplete='new-password' maxlength='255' value='true' required='required'>";
	echo "    <br />\n";
	echo "</td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td width='30%' class='vncellreq' valign='top' align='left' nowrap='nowrap'>";
	echo "mob.codecs.wifiNetwork.g722.priority";
	echo"</td>";
	echo "<td  width='70%' class='vtable' align='left'>";
	echo "<input class='formfld' type='text' name='mob--codecs--wifiNetwork--g722--priority' autocomplete='new-password' maxlength='255' value='40' required='required'>";
	echo "    <br />\n";
	echo "</td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td width='30%' class='vncellreq' valign='top' align='left' nowrap='nowrap'>";
	echo "mob.codecs.wifiNetwork.g729.enabled";
	echo"</td>";
	echo "<td  width='70%' class='vtable' align='left'>";
	echo "<input class='formfld' type='text' name='mob--codecs--wifiNetwork--g729--enabled' autocomplete='new-password' maxlength='255' value='true' required='required'>";
	echo "    <br />\n";
	echo "</td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td width='30%' class='vncellreq' valign='top' align='left' nowrap='nowrap'>";
	echo "mob.codecs.wifiNetwork.g729.priority";
	echo"</td>";
	echo "<td  width='70%' class='vtable' align='left'>";
	echo "<input class='formfld' type='text' name='mob--codecs--wifiNetwork--g729--priority' autocomplete='new-password' maxlength='255' value='50' required='required'>";
	echo "    <br />\n";
	echo "</td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td width='30%' class='vncellreq' valign='top' align='left' nowrap='nowrap'>";
	echo "mob.codecs.wifiNetwork.gsm.enabled";
	echo"</td>";
	echo "<td  width='70%' class='vtable' align='left'>";
	echo "<input class='formfld' type='text' name='mob--codecs--wifiNetwork--gsm--enabled' autocomplete='new-password' maxlength='255' value='false' required='required'>";
	echo "    <br />\n";
	echo "</td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td width='30%' class='vncellreq' valign='top' align='left' nowrap='nowrap'>";
	echo "mob.codecs.wifiNetwork.ilbc.enabled";
	echo"</td>";
	echo "<td  width='70%' class='vtable' align='left'>";
	echo "<input class='formfld' type='text' name='mob--codecs--wifiNetwork--ilbc--enabled' autocomplete='new-password' maxlength='255' value='false' required='required'>";
	echo "    <br />\n";
	echo "</td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td width='30%' class='vncellreq' valign='top' align='left' nowrap='nowrap'>";
	echo "mob.codecs.wifiNetwork.ilbc.priority";
	echo"</td>";
	echo "<td  width='70%' class='vtable' align='left'>";
	echo "<input class='formfld' type='text' name='mob--codecs--wifiNetwork--ilbc--priority' autocomplete='new-password' maxlength='255' value='' required='required'>";
	echo "    <br />\n";
	echo "</td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td width='30%' class='vncellreq' valign='top' align='left' nowrap='nowrap'>";
	echo "mob.codecs.wifiNetwork.opus.enabled";
	echo"</td>";
	echo "<td  width='70%' class='vtable' align='left'>";
	echo "<input class='formfld' type='text' name='mob--codecs--wifiNetwork--opus--enabled' autocomplete='new-password' maxlength='255' value='true' required='required'>";
	echo "    <br />\n";
	echo "</td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td width='30%' class='vncellreq' valign='top' align='left' nowrap='nowrap'>";
	echo "mob.codecs.wifiNetwork.opus.priority";
	echo"</td>";
	echo "<td  width='70%' class='vtable' align='left'>";
	echo "<input class='formfld' type='text' name='mob--codecs--wifiNetwork--opus--priority' autocomplete='new-password' maxlength='255' value='10' required='required'>";
	echo "    <br />\n";
	echo "</td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td width='30%' class='vncellreq' valign='top' align='left' nowrap='nowrap'>";
	echo "mob.codecs.wifiNetwork.silk.enabled";
	echo"</td>";
	echo "<td  width='70%' class='vtable' align='left'>";
	echo "<input class='formfld' type='text' name='mob.codecs--wifiNetwork--silk--enabled' autocomplete='new-password' maxlength='255' value='false' required='required'>";
	echo "    <br />\n";
	echo "</td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td width='30%' class='vncellreq' valign='top' align='left' nowrap='nowrap'>";
	echo "mob.codecs.wifiNetwork.silk.priority";
	echo"</td>";
	echo "<td  width='70%' class='vtable' align='left'>";
	echo "<input class='formfld' type='text' name='mob--codecs--wifiNetwork--silk--priority' autocomplete='new-password' maxlength='255' value='' required='required'>";
	echo "    <br />\n";
	echo "</td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td width='30%' class='vncellreq' valign='top' align='left' nowrap='nowrap'>";
	echo "mob.codecs.wifiNetwork.silkhd.enabled";
	echo"</td>";
	echo "<td  width='70%' class='vtable' align='left'>";
	echo "<input class='formfld' type='text' name='mob--codecs--wifiNetwork--silkhd--enabled' autocomplete='new-password' maxlength='255' value='false' required='required'>";
	echo "    <br />\n";
	echo "</td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td width='30%' class='vncellreq' valign='top' align='left' nowrap='nowrap'>";
	echo "mob.codecs.wifiNetwork.silkhd.priority";
	echo"</td>";
	echo "<td  width='70%' class='vtable' align='left'>";
	echo "<input class='formfld' type='text' name='mob--codecs--wifiNetwork--silkhd--priority' autocomplete='new-password' maxlength='255' value='' required='required'>";
	echo "    <br />\n";
	echo "</td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td width='30%' class='vncellreq' valign='top' align='left' nowrap='nowrap'>";
	echo "mob.codecs.wifiNetwork.silkswb.enabled";
	echo"</td>";
	echo "<td  width='70%' class='vtable' align='left'>";
	echo "<input class='formfld' type='text' name='mob--codecs--wifiNetwork--silkswb--enabled' autocomplete='new-password' maxlength='255' value='false' required='required'>";
	echo "    <br />\n";
	echo "</td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td width='30%' class='vncellreq' valign='top' align='left' nowrap='nowrap'>";
	echo "mob.codecs.wifiNetwork.silkswb.priority";
	echo"</td>";
	echo "<td  width='70%' class='vtable' align='left'>";
	echo "<input class='formfld' type='text' name='mob--codecs--wifiNetwork--silkswb--priority' autocomplete='new-password' maxlength='255' value='' required='required'>";
	echo "    <br />\n";
	echo "</td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td width='30%' class='vncellreq' valign='top' align='left' nowrap='nowrap'>";
	echo "mob.feature.sms.enabled";
	echo"</td>";
	echo "<td  width='70%' class='vtable' align='left'>";
	echo "<input class='formfld' type='text' name='mob--feature--sms--enabled' autocomplete='new-password' maxlength='255' value='false' required='required'>";
	echo "    <br />\n";
	echo "</td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td width='30%' class='vncellreq' valign='top' align='left' nowrap='nowrap'>";
	echo "mob.network.keepAlive.mobiledata.interval";
	echo"</td>";
	echo "<td  width='70%' class='vtable' align='left'>";
	echo "<input class='formfld' type='text' name='mob--network--keepAlive--mobiledata--interval' autocomplete='new-password' maxlength='255' value='9' required='required'>";
	echo "    <br />\n";
	echo "</td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td width='30%' class='vncellreq' valign='top' align='left' nowrap='nowrap'>";
	echo "mob.network.keepAlive.wifi.interval";
	echo"</td>";
	echo "<td  width='70%' class='vtable' align='left'>";
	echo "<input class='formfld' type='text' name='mob--network--keepAlive--wifi--interval' autocomplete='new-password' maxlength='255' value='30' required='required'>";
	echo "    <br />\n";
	echo "</td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td width='30%' class='vncellreq' valign='top' align='left' nowrap='nowrap'>";
	echo "mob.network.siptransport.encryptAudio";
	echo"</td>";
	echo "<td  width='70%' class='vtable' align='left'>";
	echo "<input class='formfld' type='text' name='mob--network--siptransport--encryptAudio' autocomplete='new-password' maxlength='255' value='1' required='required'>";
	echo "    <br />\n";
	echo "</td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td width='30%' class='vncellreq' valign='top' align='left' nowrap='nowrap'>";
	echo "mob.network.siptransport.option";
	echo"</td>";
	echo "<td  width='70%' class='vtable' align='left'>";
	echo "<input class='formfld' type='text' name='mob--network--siptransport--option' autocomplete='new-password' maxlength='255' value='2' required='required'>";
	echo "    <br />\n";
	echo "</td>";
	echo "</tr>";

	echo "</tbody>";
	echo "</table>";
	echo "</div>";
	echo "</form>";
	echo "<script>\n";

//hide password fields before submit
	echo "	function submit_form() {\n";
	echo "		hide_password_fields();\n";
	echo "		$('form#frm').submit();\n";
	echo "	}\n";
	echo "</script>\n";

//include the footer
	require_once "resources/footer.php";

?>
<script>
$(document).ready(function(){
  $("#advance_button").click(function(){
    $("#advance_div").toggle();
  });
});
</script>
<script>
(function () {
  $("#btnRight").click(function (e) {
    var selectedOpts = $("#lstBox1 option:selected");
    if (selectedOpts.length == 0) {
      //alert("Nothing to move.");
      e.preventDefault();
    }
    $("#lstBox2").append($(selectedOpts).clone());
    $(selectedOpts).remove();
    e.preventDefault();
	$("#lstBox2 option").each(function()
	{
		var value = $(this).val();
		$('#lstBox2 [value='+value+']').prop('selected', true);
	});
  });
  $("#btnAllRight").click(function (e) {
    var selectedOpts = $("#lstBox1 option");
    if (selectedOpts.length == 0) {
      //alert("Nothing to move.");
      e.preventDefault();
    }
    $("#lstBox2").append($(selectedOpts).clone());
    $(selectedOpts).remove();
    e.preventDefault();
	$("#lstBox2 option").each(function()
	{
		var value = $(this).val();
		$('#lstBox2 [value='+value+']').prop('selected', true);
	});
  });
  $("#btnLeft").click(function (e) {
    var selectedOpts = $("#lstBox2 option:selected");
    if (selectedOpts.length == 0) {
      //alert("Nothing to move.");
      e.preventDefault();
    }
    $("#lstBox1").append($(selectedOpts).clone());
    $(selectedOpts).remove();
    e.preventDefault();
	$("#lstBox2 option").each(function()
	{
		var value = $(this).val();
		$('#lstBox2 [value='+value+']').prop('selected', true);
	});
  });
  $("#btnAllLeft").click(function (e) {
    var selectedOpts = $("#lstBox2 option");
    if (selectedOpts.length == 0) {
      //alert("Nothing to move.");
      e.preventDefault();
    }
    $("#lstBox1").append($(selectedOpts).clone());
    $(selectedOpts).remove();
    e.preventDefault();
	$("#lstBox2 option").each(function()
	{
		var value = $(this).val();
		$('#lstBox2 [value='+value+']').prop('selected', true);
	});
  });
})(jQuery);
</script>
<script type="text/javascript" language="JavaScript">
function profile_bria(group) {
		$.ajax({  
			type: "POST",  
			url: "bria_add.php",  
			data: {'group_ajax': group},  
			success: function(res) {  
 			   document.getElementById('sipprofile').innerHTML =res;
			}  
		});
}
</script>
