
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
if((strtotime(gmdate('Y-m-d')) > $group_content['date']) || (isset($_GET) && $_GET['manual'] == 1)){
	$group_array = array();
	$url = $api_url."/stretto/prov/usergroup?groupName=all"; 
	$ch = curl_init();     
	curl_setopt($ch, CURLOPT_URL, $url);  
	curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_DIGEST); 
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
	curl_setopt($ch, CURLOPT_USERPWD, $api_username.":".$api_password);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
	curl_setopt($ch, CURLOPT_HEADER, false); 
	$result = curl_exec($ch);  
	$xml_group_array = json_decode(json_encode((array)simplexml_load_string($result)),true);
	curl_close($ch);
	foreach($xml_group_array['CcsUserGroup'] as $key => $value){
		if($key == '@attributes'){
			$group_name = $value['groupName'];
		}else{
			$group_name = $value['@attributes']['groupName'];
		}
		$group_array[$group_name] = $group_name;
	}
	$text = array("date"=>strtotime(gmdate('Y-m-d')),"group_array"=>$group_array);
	$var_str = var_export($text, true);
	$var = "<?php\n\n\$group_content = $var_str;\n\n?>";
	file_put_contents('bria_group_content.php', $var);
	if(isset($_GET) && $_GET['manual'] == 1){
		message::add("Bria Group Updated Successfully");
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

	if (permission_exists('bria_profile_edit')) {
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
			foreach($_POST as $key => $value){
			   if($key != "group"){
				$url = $api_url."/stretto/prov/profile/attribute?method=POST&profileName=".$_POST['account1--accountName']."&groupName=".$_POST['group']."&attributeName=".str_replace("--",".",$key)."&attributeValue=".$value."";
//echo $url."::::\n";
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
//echo ":::::::";exit;
				message::add("Bria Profile Updated Successfully");
				header("Location: briaprofile.php");
				exit;
			}else{
				message::add("Something wrong in API",'negative');
				header("Location: bria_profile_edit.php?domain=".$_POST['group']."&username=".$_POST['account1--accountName']);
				exit;
			}

	}
	$sub_cat = array();
	if(isset($_GET)){
		$url = $api_url."/stretto/prov/profile?profileName=".$_GET['username']."&groupName=".$_GET['domain']; 
		$ch = curl_init();     
		curl_setopt($ch, CURLOPT_URL, $url);  
		curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_DIGEST); 
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
		curl_setopt($ch, CURLOPT_USERPWD, $api_username.":".$api_password);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
		curl_setopt($ch, CURLOPT_HEADER, false); 
		$result = curl_exec($ch);  
		$xml_profile_array = json_decode(json_encode((array)simplexml_load_string($result)),true);
		curl_close($ch);
		foreach($xml_profile_array['CcsProfile'] as $sub_key => $sub_value){
		if($key == '@attributes'){
			$profile_name = $sub_value['profileName'];
		}else{
			$profile_name = $sub_value['@attributes']['profileName'];
		}
		}
		foreach($xml_profile_array['CcsProfile']['CcsProfileAttributes']['CcsProfileAttribute'] as $sub_key => $sub_value){
			$sub_cat[$sub_value['@attributes']['attributeName']] = $sub_value['@attributes']['attributeValue'];
		}	
	}

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

	$object = new token;
	$token = $object->create($_SERVER['PHP_SELF']);

//begin the page content
	$document['title'] = "Bria Profile Update";//$text['title-extensions'];
	require_once "resources/header.php";
	echo "<form method='post' name='frm' id='frm'>\n";

	echo "<div class='action_bar' id='action_bar'>\n";
	echo "	<div class='heading'>";
	echo "<b>Bria Profile Update(Domain:".$domain_name.")</b>";
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
	echo "Group(Read-Only)";
	echo"</td>";
	echo "<td  width='70%' class='vtable' align='left'>";
	echo "<input class='formfld' type='text' name='group' autocomplete='new-password' maxlength='255' value='".$_GET['domain']."' required='required' readonly>";
	
	echo "    <br />\n";
	echo "</td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td width='30%' class='vncellreq' valign='top' align='left' nowrap='nowrap'>";
	echo "account1.accountName(Read-Only)";
	echo"</td>";
	echo "<td  width='70%' class='vtable' align='left'>";
	echo "<input class='formfld' type='text' name='account1--accountName' autocomplete='new-password' maxlength='255' value='".$sub_cat['account1.accountName']."' required='required' readonly>";
	echo "    <br />\n";
	echo "</td>";
	echo "</tr>";
	echo "</tbody>";
	echo "</table>";
	echo "<br><button id='advance_button' class='advance_button btn btn-default' style='margin-left:10%;'>Advance</button><br>";
	echo "<div id='advance_div' class='advance_div ' style='display:none;'>";
	echo "<table width='100%' border='0' cellpadding='0' cellspacing='0'>";
	echo "<tbody>";
	echo "<tr>";
	echo "<td width='30%' class='vncellreq' valign='top' align='left' nowrap='nowrap'>";
	echo "account1.briaPush.enabled";
	echo"</td>";
	echo "<td  width='70%' class='vtable' align='left'>";
	echo "<input class='formfld' type='text' name='account1--briaPush--enabled' autocomplete='new-password' maxlength='255' value='".$sub_cat['account1.briaPush.enabled']."' required='required'>";
	echo "    <br />\n";
	echo "</td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td width='30%' class='vncellreq' valign='top' align='left' nowrap='nowrap'>";
	echo "account1.ignoreTlsCertVerify";
	echo"</td>";
	echo "<td  width='70%' class='vtable' align='left'>";
	echo "<input class='formfld' type='text' name='account1--ignoreTlsCertVerify' autocomplete='new-password' maxlength='255' value='".$sub_cat['account1.ignoreTlsCertVerify']."' required='required'>";
	echo "    <br />\n";
	echo "</td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td width='30%' class='vncellreq' valign='top' align='left' nowrap='nowrap'>";
	echo "mob.codecs.mobileNetwork.g711a.enabled";
	echo"</td>";
	echo "<td  width='70%' class='vtable' align='left'>";
	echo "<input class='formfld' type='text' name='mob--codecs--mobileNetwork--g711a--enabled' autocomplete='new-password' maxlength='255' value='".$sub_cat['mob.codecs.mobileNetwork.g711a.enabled']."' required='required'>";
	echo "    <br />\n";
	echo "</td>";
	echo "</tr>";
	echo "</tr>";
	echo "<tr>";
	echo "<td width='30%' class='vncellreq' valign='top' align='left' nowrap='nowrap'>";
	echo "mob.codecs.mobileNetwork.g711a.priority";
	echo"</td>";
	echo "<td  width='70%' class='vtable' align='left'>";
	echo "<input class='formfld' type='text' name='mob--codecs--mobileNetwork--g711a--priority' autocomplete='new-password' maxlength='255' value='".$sub_cat['mob.codecs.mobileNetwork.g711a.priority']."' required='required'>";
	echo "    <br />\n";
	echo "</td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td width='30%' class='vncellreq' valign='top' align='left' nowrap='nowrap'>";
	echo "mob.codecs.mobileNetwork.g711u.enabled";
	echo"</td>";
	echo "<td  width='70%' class='vtable' align='left'>";
	echo "<input class='formfld' type='text' name='mob--codecs--mobileNetwork--g711u--enabled' autocomplete='new-password' maxlength='255' value='".$sub_cat['mob.codecs.mobileNetwork.g711u.enabled']."' required='required'>";
	echo "    <br />\n";
	echo "</td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td width='30%' class='vncellreq' valign='top' align='left' nowrap='nowrap'>";
	echo "mob.codecs.mobileNetwork.g711u.priority";
	echo"</td>";
	echo "<td  width='70%' class='vtable' align='left'>";
	echo "<input class='formfld' type='text' name='mob--codecs--mobileNetwork--g711u--priority' autocomplete='new-password' maxlength='255' value='".$sub_cat['mob.codecs.mobileNetwork.g711u.priority']."' required='required'>";
	echo "    <br />\n";
	echo "</td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td width='30%' class='vncellreq' valign='top' align='left' nowrap='nowrap'>";
	echo "mob.codecs.mobileNetwork.g722.enabled";
	echo"</td>";
	echo "<td  width='70%' class='vtable' align='left'>";
	echo "<input class='formfld' type='text' name='mob--codecs--mobileNetwork--g722--enabled' autocomplete='new-password' maxlength='255' value='".$sub_cat['mob.codecs.mobileNetwork.g722.enabled']."' required='required'>";
	echo "    <br />\n";
	echo "</td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td width='30%' class='vncellreq' valign='top' align='left' nowrap='nowrap'>";
	echo "mob.codecs.mobileNetwork.g722.priority";
	echo"</td>";
	echo "<td  width='70%' class='vtable' align='left'>";
	echo "<input class='formfld' type='text' name='mob--codecs--mobileNetwork--g722--priority' autocomplete='new-password' maxlength='255' value='".$sub_cat['mob.codecs.mobileNetwork.g722.priority']."' required='required'>";
	echo "    <br />\n";
	echo "</td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td width='30%' class='vncellreq' valign='top' align='left' nowrap='nowrap'>";
	echo "mob.codecs.mobileNetwork.g729.enabled";
	echo"</td>";
	echo "<td  width='70%' class='vtable' align='left'>";
	echo "<input class='formfld' type='text' name='mob--codecs--mobileNetwork--g729--enabled' autocomplete='new-password' maxlength='255' value='".$sub_cat['mob.codecs.mobileNetwork.g729.enabled']."' required='required'>";
	echo "    <br />\n";
	echo "</td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td width='30%' class='vncellreq' valign='top' align='left' nowrap='nowrap'>";
	echo "mob.codecs.mobileNetwork.g729.priority";
	echo"</td>";
	echo "<td  width='70%' class='vtable' align='left'>";
	echo "<input class='formfld' type='text' name='mob--codecs--mobileNetwork--g729--priority' autocomplete='new-password' maxlength='255' value='".$sub_cat['mob.codecs.mobileNetwork.g729.priority']."' required='required'>";
	echo "    <br />\n";
	echo "</td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td width='30%' class='vncellreq' valign='top' align='left' nowrap='nowrap'>";
	echo "mob.codecs.mobileNetwork.gsm.enabled";
	echo"</td>";
	echo "<td  width='70%' class='vtable' align='left'>";
	echo "<input class='formfld' type='text' name='mob--codecs--mobileNetwork--gsm--enabled' autocomplete='new-password' maxlength='255' value='".$sub_cat['mob.codecs.mobileNetwork.gsm.enabled']."' required='required'>";
	echo "    <br />\n";
	echo "</td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td width='30%' class='vncellreq' valign='top' align='left' nowrap='nowrap'>";
	echo "mob.codecs.mobileNetwork.gsm.priority";
	echo"</td>";
	echo "<td  width='70%' class='vtable' align='left'>";
	echo "<input class='formfld' type='text' name='mob--codecs--mobileNetwork--gsm--priority' autocomplete='new-password' maxlength='255' value='".$sub_cat['mob.codecs.mobileNetwork.gsm.priority']."' required='required'>";
	echo "    <br />\n";
	echo "</td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td width='30%' class='vncellreq' valign='top' align='left' nowrap='nowrap'>";
	echo "mob.codecs.mobileNetwork.ilbc.enabled";
	echo"</td>";
	echo "<td  width='70%' class='vtable' align='left'>";
	echo "<input class='formfld' type='text' name='mob--codecs--mobileNetwork--ilbc--enabled' autocomplete='new-password' maxlength='255' value='".$sub_cat['mob.codecs.mobileNetwork.ilbc.enabled']."' required='required'>";
	echo "    <br />\n";
	echo "</td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td width='30%' class='vncellreq' valign='top' align='left' nowrap='nowrap'>";
	echo "mob.codecs.mobileNetwork.ilbc.priority";
	echo"</td>";
	echo "<td  width='70%' class='vtable' align='left'>";
	echo "<input class='formfld' type='text' name='mob--codecs--mobileNetwork--ilbc--priority' autocomplete='new-password' maxlength='255' value='".$sub_cat['mob.codecs.mobileNetwork.ilbc.priority']."' required='required'>";
	echo "    <br />\n";
	echo "</td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td width='30%' class='vncellreq' valign='top' align='left' nowrap='nowrap'>";
	echo "mob.codecs.mobileNetwork.opus.enabled";
	echo"</td>";
	echo "<td  width='70%' class='vtable' align='left'>";
	echo "<input class='formfld' type='text' name='mob--codecs--mobileNetwork--opus--enabled' autocomplete='new-password' maxlength='255' value='".$sub_cat['mob.codecs.mobileNetwork.opus.enabled']."' required='required'>";
	echo "    <br />\n";
	echo "</td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td width='30%' class='vncellreq' valign='top' align='left' nowrap='nowrap'>";
	echo "mob.codecs.mobileNetwork.opus.priority";
	echo"</td>";
	echo "<td  width='70%' class='vtable' align='left'>";
	echo "<input class='formfld' type='text' name='mob--codecs--mobileNetwork--opus--priority' autocomplete='new-password' maxlength='255' value='".$sub_cat['mob.codecs.mobileNetwork.opus.priority']."' required='required'>";
	echo "    <br />\n";
	echo "</td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td width='30%' class='vncellreq' valign='top' align='left' nowrap='nowrap'>";
	echo "mob.codecs.mobileNetwork.silk.enabled";
	echo"</td>";
	echo "<td  width='70%' class='vtable' align='left'>";
	echo "<input class='formfld' type='text' name='mob--codecs--mobileNetwork--silk--enabled' autocomplete='new-password' maxlength='255' value='".$sub_cat['mob.codecs.mobileNetwork.silk.enabled']."' required='required'>";
	echo "    <br />\n";
	echo "</td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td width='30%' class='vncellreq' valign='top' align='left' nowrap='nowrap'>";
	echo "mob.codecs.mobileNetwork.silk.priority";
	echo"</td>";
	echo "<td  width='70%' class='vtable' align='left'>";
	echo "<input class='formfld' type='text' name='mob--codecs--mobileNetwork--silk--priority' autocomplete='new-password' maxlength='255' value='".$sub_cat['mob.codecs.mobileNetwork.silk.priority']."' required='required'>";
	echo "    <br />\n";
	echo "</td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td width='30%' class='vncellreq' valign='top' align='left' nowrap='nowrap'>";
	echo "mob.codecs.mobileNetwork.silkhd.enabled";
	echo"</td>";
	echo "<td  width='70%' class='vtable' align='left'>";
	echo "<input class='formfld' type='text' name='mob--codecs--mobileNetwork--silkhd--enabled' autocomplete='new-password' maxlength='255' value='".$sub_cat['mob.codecs.mobileNetwork.silkhd.enabled']."' required='required'>";
	echo "    <br />\n";
	echo "</td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td width='30%' class='vncellreq' valign='top' align='left' nowrap='nowrap'>";
	echo "mob.codecs.mobileNetwork.silkhd.priority";
	echo"</td>";
	echo "<td  width='70%' class='vtable' align='left'>";
	echo "<input class='formfld' type='text' name='mob--codecs--mobileNetwork--silkhd--priority' autocomplete='new-password' maxlength='255' value='".$sub_cat['mob.codecs.mobileNetwork.silkhd.priority']."' required='required'>";
	echo "    <br />\n";
	echo "</td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td width='30%' class='vncellreq' valign='top' align='left' nowrap='nowrap'>";
	echo "mob.codecs.mobileNetwork.silkswb.enabled";
	echo"</td>";
	echo "<td  width='70%' class='vtable' align='left'>";
	echo "<input class='formfld' type='text' name='mob--codecs--mobileNetwork--silkswb--enabled' autocomplete='new-password' maxlength='255' value='".$sub_cat['mob.codecs.mobileNetwork.silkswb.enabled']."' required='required'>";
	echo "    <br />\n";
	echo "</td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td width='30%' class='vncellreq' valign='top' align='left' nowrap='nowrap'>";
	echo "mob.codecs.mobileNetwork.silkswb.priority";
	echo"</td>";
	echo "<td  width='70%' class='vtable' align='left'>";
	echo "<input class='formfld' type='text' name='mob--codecs--mobileNetwork--silkswb--priority' autocomplete='new-password' maxlength='255' value='".$sub_cat['mob.codecs.mobileNetwork.silkswb.priority']."' required='required'>";
	echo "    <br />\n";
	echo "</td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td width='30%' class='vncellreq' valign='top' align='left' nowrap='nowrap'>";
	echo "mob.codecs.video.h264.enabled";
	echo"</td>";
	echo "<td  width='70%' class='vtable' align='left'>";
	echo "<input class='formfld' type='text' name='mob--codecs--video--h264--enabled' autocomplete='new-password' maxlength='255' value='".$sub_cat['mob.codecs.video.h264.enabled']."' required='required'>";
	echo "    <br />\n";
	echo "</td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td width='30%' class='vncellreq' valign='top' align='left' nowrap='nowrap'>";
	echo "mob.codecs.video.h264.priority";
	echo"</td>";
	echo "<td  width='70%' class='vtable' align='left'>";
	echo "<input class='formfld' type='text' name='mob--codecs--video--h264--priority' autocomplete='new-password' maxlength='255' value='".$sub_cat['mob.codecs.video.h264.priority']."' required='required'>";
	echo "    <br />\n";
	echo "</td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td width='30%' class='vncellreq' valign='top' align='left' nowrap='nowrap'>";
	echo "mob.codecs.video.vp8.enabled";
	echo"</td>";
	echo "<td  width='70%' class='vtable' align='left'>";
	echo "<input class='formfld' type='text' name='mob--codecs--video--vp8--enabled' autocomplete='new-password' maxlength='255' value='".$sub_cat['mob.codecs.video.vp8.enabled']."' required='required'>";
	echo "    <br />\n";
	echo "</td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td width='30%' class='vncellreq' valign='top' align='left' nowrap='nowrap'>";
	echo "mob.codecs.video.vp8.priority";
	echo"</td>";
	echo "<td  width='70%' class='vtable' align='left'>";
	echo "<input class='formfld' type='text' name='mob--codecs.video--vp8.priority' autocomplete='new-password' maxlength='255' value='".$sub_cat['mob.codecs.video.vp8.priority']."' required='required'>";
	echo "    <br />\n";
	echo "</td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td width='30%' class='vncellreq' valign='top' align='left' nowrap='nowrap'>";
	echo "mob.codecs.wifiNetwork.g711a.enabled";
	echo"</td>";
	echo "<td  width='70%' class='vtable' align='left'>";
	echo "<input class='formfld' type='text' name='mob--codecs--wifiNetwork--g711a--enabled' autocomplete='new-password' maxlength='255' value='".$sub_cat['mob.codecs.wifiNetwork.g711a.enabled']."' required='required'>";
	echo "    <br />\n";
	echo "</td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td width='30%' class='vncellreq' valign='top' align='left' nowrap='nowrap'>";
	echo "mob.codecs.wifiNetwork.g711a.priority";
	echo"</td>";
	echo "<td  width='70%' class='vtable' align='left'>";
	echo "<input class='formfld' type='text' name='mob--codecs--wifiNetwork--g711a--priority' autocomplete='new-password' maxlength='255' value='".$sub_cat['mob.codecs.wifiNetwork.g711a.priority']."' required='required'>";
	echo "    <br />\n";
	echo "</td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td width='30%' class='vncellreq' valign='top' align='left' nowrap='nowrap'>";
	echo "mob.codecs.wifiNetwork.g711u.enabled";
	echo"</td>";
	echo "<td  width='70%' class='vtable' align='left'>";
	echo "<input class='formfld' type='text' name='mob--codecs--wifiNetwork--g711u--enabled' autocomplete='new-password' maxlength='255' value='".$sub_cat['mob.codecs.wifiNetwork.g711u.enabled']."' required='required'>";
	echo "    <br />\n";
	echo "</td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td width='30%' class='vncellreq' valign='top' align='left' nowrap='nowrap'>";
	echo "mob.codecs.wifiNetwork.g711u.priority";
	echo"</td>";
	echo "<td  width='70%' class='vtable' align='left'>";
	echo "<input class='formfld' type='text' name='mob--codecs--wifiNetwork--g711u--priority' autocomplete='new-password' maxlength='255' value='".$sub_cat['mob.codecs.wifiNetwork.g711u.priority']."' required='required'>";
	echo "    <br />\n";
	echo "</td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td width='30%' class='vncellreq' valign='top' align='left' nowrap='nowrap'>";
	echo "mob.codecs.wifiNetwork.g722.enabled";
	echo"</td>";
	echo "<td  width='70%' class='vtable' align='left'>";
	echo "<input class='formfld' type='text' name='mob--codecs--wifiNetwork--g722--enabled' autocomplete='new-password' maxlength='255' value='".$sub_cat['mob.codecs.wifiNetwork.g722.enabled']."' required='required'>";
	echo "    <br />\n";
	echo "</td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td width='30%' class='vncellreq' valign='top' align='left' nowrap='nowrap'>";
	echo "mob.codecs.wifiNetwork.g722.priority";
	echo"</td>";
	echo "<td  width='70%' class='vtable' align='left'>";
	echo "<input class='formfld' type='text' name='mob--codecs--wifiNetwork--g722--priority' autocomplete='new-password' maxlength='255' value='".$sub_cat['mob.codecs.wifiNetwork.g722.priority']."' required='required'>";
	echo "    <br />\n";
	echo "</td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td width='30%' class='vncellreq' valign='top' align='left' nowrap='nowrap'>";
	echo "mob.codecs.wifiNetwork.g729.enabled";
	echo"</td>";
	echo "<td  width='70%' class='vtable' align='left'>";
	echo "<input class='formfld' type='text' name='mob--codecs--wifiNetwork--g729--enabled' autocomplete='new-password' maxlength='255' value='".$sub_cat['mob.codecs.wifiNetwork.g729.enabled']."' required='required'>";
	echo "    <br />\n";
	echo "</td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td width='30%' class='vncellreq' valign='top' align='left' nowrap='nowrap'>";
	echo "mob.codecs.wifiNetwork.g729.priority";
	echo"</td>";
	echo "<td  width='70%' class='vtable' align='left'>";
	echo "<input class='formfld' type='text' name='mob--codecs--wifiNetwork--g729--priority' autocomplete='new-password' maxlength='255' value='".$sub_cat['mob.codecs.wifiNetwork.g729.priority']."' required='required'>";
	echo "    <br />\n";
	echo "</td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td width='30%' class='vncellreq' valign='top' align='left' nowrap='nowrap'>";
	echo "mob.codecs.wifiNetwork.gsm.enabled";
	echo"</td>";
	echo "<td  width='70%' class='vtable' align='left'>";
	echo "<input class='formfld' type='text' name='mob--codecs--wifiNetwork--gsm--enabled' autocomplete='new-password' maxlength='255' value='".$sub_cat['mob.codecs.wifiNetwork.gsm.enabled']."' required='required'>";
	echo "    <br />\n";
	echo "</td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td width='30%' class='vncellreq' valign='top' align='left' nowrap='nowrap'>";
	echo "mob.codecs.wifiNetwork.ilbc.enabled";
	echo"</td>";
	echo "<td  width='70%' class='vtable' align='left'>";
	echo "<input class='formfld' type='text' name='mob--codecs--wifiNetwork--ilbc--enabled' autocomplete='new-password' maxlength='255' value='".$sub_cat['mob.codecs.wifiNetwork.ilbc.enabled']."' required='required'>";
	echo "    <br />\n";
	echo "</td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td width='30%' class='vncellreq' valign='top' align='left' nowrap='nowrap'>";
	echo "mob.codecs.wifiNetwork.ilbc.priority";
	echo"</td>";
	echo "<td  width='70%' class='vtable' align='left'>";
	echo "<input class='formfld' type='text' name='mob--codecs--wifiNetwork--ilbc--priority' autocomplete='new-password' maxlength='255' value='".$sub_cat['mob.codecs.wifiNetwork.ilbc.priority']."' required='required'>";
	echo "    <br />\n";
	echo "</td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td width='30%' class='vncellreq' valign='top' align='left' nowrap='nowrap'>";
	echo "mob.codecs.wifiNetwork.opus.enabled";
	echo"</td>";
	echo "<td  width='70%' class='vtable' align='left'>";
	echo "<input class='formfld' type='text' name='mob--codecs--wifiNetwork--opus--enabled' autocomplete='new-password' maxlength='255' value='".$sub_cat['mob.codecs.wifiNetwork.opus.enabled']."' required='required'>";
	echo "    <br />\n";
	echo "</td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td width='30%' class='vncellreq' valign='top' align='left' nowrap='nowrap'>";
	echo "mob.codecs.wifiNetwork.opus.priority";
	echo"</td>";
	echo "<td  width='70%' class='vtable' align='left'>";
	echo "<input class='formfld' type='text' name='mob--codecs--wifiNetwork--opus--priority' autocomplete='new-password' maxlength='255' value='".$sub_cat['mob.codecs.wifiNetwork.opus.priority']."' required='required'>";
	echo "    <br />\n";
	echo "</td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td width='30%' class='vncellreq' valign='top' align='left' nowrap='nowrap'>";
	echo "mob.codecs.wifiNetwork.silk.enabled";
	echo"</td>";
	echo "<td  width='70%' class='vtable' align='left'>";
	echo "<input class='formfld' type='text' name='mob.codecs--wifiNetwork--silk--enabled' autocomplete='new-password' maxlength='255' value='".$sub_cat['mob.codecs.wifiNetwork.silk.enabled']."' required='required'>";
	echo "    <br />\n";
	echo "</td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td width='30%' class='vncellreq' valign='top' align='left' nowrap='nowrap'>";
	echo "mob.codecs.wifiNetwork.silk.priority";
	echo"</td>";
	echo "<td  width='70%' class='vtable' align='left'>";
	echo "<input class='formfld' type='text' name='mob--codecs--wifiNetwork--silk--priority' autocomplete='new-password' maxlength='255' value='".$sub_cat['mob.codecs.wifiNetwork.silk.priority']."' required='required'>";
	echo "    <br />\n";
	echo "</td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td width='30%' class='vncellreq' valign='top' align='left' nowrap='nowrap'>";
	echo "mob.codecs.wifiNetwork.silkhd.enabled";
	echo"</td>";
	echo "<td  width='70%' class='vtable' align='left'>";
	echo "<input class='formfld' type='text' name='mob--codecs--wifiNetwork--silkhd--enabled' autocomplete='new-password' maxlength='255' value='".$sub_cat['mob.codecs.wifiNetwork.silkhd.enabled']."' required='required'>";
	echo "    <br />\n";
	echo "</td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td width='30%' class='vncellreq' valign='top' align='left' nowrap='nowrap'>";
	echo "mob.codecs.wifiNetwork.silkhd.priority";
	echo"</td>";
	echo "<td  width='70%' class='vtable' align='left'>";
	echo "<input class='formfld' type='text' name='mob--codecs--wifiNetwork--silkhd--priority' autocomplete='new-password' maxlength='255' value='".$sub_cat['mob.codecs.wifiNetwork.silkhd.priority']."' required='required'>";
	echo "    <br />\n";
	echo "</td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td width='30%' class='vncellreq' valign='top' align='left' nowrap='nowrap'>";
	echo "mob.codecs.wifiNetwork.silkswb.enabled";
	echo"</td>";
	echo "<td  width='70%' class='vtable' align='left'>";
	echo "<input class='formfld' type='text' name='mob--codecs--wifiNetwork--silkswb--enabled' autocomplete='new-password' maxlength='255' value='".$sub_cat['mob.codecs.wifiNetwork.silkswb.enabled']."' required='required'>";
	echo "    <br />\n";
	echo "</td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td width='30%' class='vncellreq' valign='top' align='left' nowrap='nowrap'>";
	echo "mob.codecs.wifiNetwork.silkswb.priority";
	echo"</td>";
	echo "<td  width='70%' class='vtable' align='left'>";
	echo "<input class='formfld' type='text' name='mob--codecs--wifiNetwork--silkswb--priority' autocomplete='new-password' maxlength='255' value='".$sub_cat['mob.codecs.wifiNetwork.silkswb.priority']."' required='required'>";
	echo "    <br />\n";
	echo "</td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td width='30%' class='vncellreq' valign='top' align='left' nowrap='nowrap'>";
	echo "mob.feature.sms.enabled";
	echo"</td>";
	echo "<td  width='70%' class='vtable' align='left'>";
	echo "<input class='formfld' type='text' name='mob--feature--sms--enabled' autocomplete='new-password' maxlength='255' value='".$sub_cat['mob.feature.sms.enabled']."' required='required'>";
	echo "    <br />\n";
	echo "</td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td width='30%' class='vncellreq' valign='top' align='left' nowrap='nowrap'>";
	echo "mob.network.keepAlive.mobiledata.interval";
	echo"</td>";
	echo "<td  width='70%' class='vtable' align='left'>";
	echo "<input class='formfld' type='text' name='mob--network--keepAlive--mobiledata--interval' autocomplete='new-password' maxlength='255' value='".$sub_cat['mob.network.keepAlive.mobiledata.interval']."' required='required'>";
	echo "    <br />\n";
	echo "</td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td width='30%' class='vncellreq' valign='top' align='left' nowrap='nowrap'>";
	echo "mob.network.keepAlive.wifi.interval";
	echo"</td>";
	echo "<td  width='70%' class='vtable' align='left'>";
	echo "<input class='formfld' type='text' name='mob--network--keepAlive--wifi--interval' autocomplete='new-password' maxlength='255' value='".$sub_cat['mob.network.keepAlive.wifi.interval']."' required='required'>";
	echo "    <br />\n";
	echo "</td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td width='30%' class='vncellreq' valign='top' align='left' nowrap='nowrap'>";
	echo "mob.network.siptransport.encryptAudio";
	echo"</td>";
	echo "<td  width='70%' class='vtable' align='left'>";
	echo "<input class='formfld' type='text' name='mob--network--siptransport--encryptAudio' autocomplete='new-password' maxlength='255' value='".$sub_cat['mob.network.siptransport.encryptAudio']."' required='required'>";
	echo "    <br />\n";
	echo "</td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td width='30%' class='vncellreq' valign='top' align='left' nowrap='nowrap'>";
	echo "mob.network.siptransport.option";
	echo"</td>";
	echo "<td  width='70%' class='vtable' align='left'>";
	echo "<input class='formfld' type='text' name='mob--network--siptransport--option' autocomplete='new-password' maxlength='255' value='".$sub_cat['account1.accountName']."' required='required'>";
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
