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
*/

//set the include path
        $conf = glob("{/usr/local/etc,/etc}/fusionpbx/config.conf", GLOB_BRACE);
        set_include_path(parse_ini_file($conf[0])['document.root']);

//includes
	include_once "bria_conf.php";
//	include_once "root.php";
	require_once "resources/require.php";
	require_once "resources/check_auth.php";
	require_once "resources/paging.php";

	include_once "resources/phpmailer/class.phpmailer.php";
	include_once "resources/phpmailer/class.smtp.php";

	include 'bria_group_content.php';
	$group_array = $group_content['group_array'];

	$smtp =array();
	$smtp['secure'] 	= $_SESSION['email']['smtp_secure']['text'];
	$smtp['auth'] 		= $_SESSION['email']['smtp_auth']['text'];
	$smtp['username'] 	= $_SESSION['email']['smtp_username']['text'];
	$smtp['password'] 	= $_SESSION['email']['smtp_password']['text'];
	$smtp['from'] 		= $_SESSION['email']['smtp_from']['text'];
	$smtp['from_name'] 	= $_SESSION['email']['smtp_from_name']['text'];
	$smtp['host']           = $_SESSION['email']['smtp_host']['text'];
	$smtp['port']           = $_SESSION['email']['smtp_port']['text'];



//HP:START
	$domain_permission_list =array();
	if(!if_group("superadmin")){
		$group_uuid_str = "";
		foreach($_SESSION['groups'] as $vals){
			$group_uuid_str .= "'".$vals['group_uuid']."',";
		}
		$group_uuid_str .= ",";
		$final_group_uuid_str = str_replace(",,","",$group_uuid_str);
		$sql_new = "select domain_uuids ";
		$sql_new .= "from domain_permissions where group_uuid IN (".$final_group_uuid_str.")";
		$database = new database;
		$permission_result = $database->select($sql_new, $parameters, 'all');
		if(!empty($permission_result)){
			$domain_uuid_str = "";
			foreach($permission_result as $vals){
				if($vals['domain_uuids'] != NULL){
					$permission_arr = explode(",",$vals['domain_uuids']);
					foreach($permission_arr as $sub_vals){
						if($sub_vals != ""){
							$v_domain_sql_new = "select domain_name ";
							$v_domain_sql_new .= "from v_domains where domain_uuid = '".$sub_vals."'";
							$database = new database;
							$v_domain_sql_new_result = $database->select($v_domain_sql_new, $parameters);
							if(!empty($v_domain_sql_new_result)){
								$domain_permission_list[] = $v_domain_sql_new_result[0]['domain_name'];
							}

						}
						unset($v_domain_sql_new, $parameters);
					}
				}
			}

		}
		unset($sql_new, $parameters);
	}
	if(isset($_GET['delete'])){
		if($_GET['delete'] == 'yes'){
			message::add("Delete record successfully",'negative');
		}
		else{
			message::add("Something went wrong,Delete not successfully",'negative');
		}
		header('Location: briaprofile.php');
		exit;
	}
	if(isset($_GET['reload'])){
		if($_GET['reload'] == 'yes'){
			message::add("Group data reload in Sometime.");
		}
		header('Location: briauser.php');
		exit;
	}
	if($_POST > 0 && isset($_POST['profile_ajax'])){
		$url = $api_url."/stretto/prov/profile?profileName=".$_POST['profile_ajax']."&groupName=".$_POST['domain_ajax']."&force=true"; 
		$ch = curl_init();     
		curl_setopt($ch, CURLOPT_URL, $url);  
		curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_DIGEST); 
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
		curl_setopt($ch, CURLOPT_USERPWD, $api_username.":".$api_password);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
		curl_setopt($ch, CURLOPT_HEADER, false); 
		$result = curl_exec($ch); 
		$xml_array = json_decode(json_encode((array)simplexml_load_string($result)),true);
		if(!empty($xml_array)){
			if($xml_array['0'] ==""){
				echo true; exit;
			}else{
				echo false; exit;
			}
		}else{
				echo false; exit;
		}
		curl_close($ch); 
	}
	$v_bria_setting_new = "select bria_group ";
	$v_bria_setting_new .= "from bria_setting where domain_uuid = '".$domain_uuid."'";
	$database = new database;
	$v_bria_setting_result = $database->select($v_bria_setting_new, $parameters);
	$bria_group = $v_bria_setting_result[0]['bria_group'];

	$grid_array = array();
//	foreach($group_array as $key => $value){
//		if(in_array($value,$domain_permission_list) || if_group("superadmin")){
		if($bria_group != ""){
			$url = $api_url."/stretto/prov/profile?groupName=".$bria_group; 
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
				$grid_array[] = array("profile_name"=>$profile_name,'domain_name'=>$bria_group);
			}
		}
//		}
//	}
	$user_count =count($grid_array);
//check permissions
	if (permission_exists('bria_profile_view')) {
		//access granted
	}
	else {
		echo "access denied";
		exit;
	}

//add multi-lingual support
	$language = new text;
	$text = $language->get();

//get posted data
	if (is_array($_POST['extensions'])) {
		$action = $_POST['action'];
		$search = $_POST['search'];
		$extensions = $_POST['extensions'];
	}

//get order and order by
	$order_by = $_GET["order_by"];
	$order = $_GET["order"];

//add the search term
	$search = strtolower($_GET["search"]);
	if (strlen($search) > 0) {
		$sql_search = " and ( ";
		$sql_search .= "lower(extension) like :search ";
		$sql_search .= "or lower(number_alias) like :search ";
		$sql_search .= "or lower(effective_caller_id_name) like :search ";
		$sql_search .= "or lower(effective_caller_id_number) like :search ";
		$sql_search .= "or lower(outbound_caller_id_name) like :search ";
		$sql_search .= "or lower(outbound_caller_id_number) like :search ";
		$sql_search .= "or lower(emergency_caller_id_name) like :search ";
		$sql_search .= "or lower(emergency_caller_id_number) like :search ";
		$sql_search .= "or lower(directory_first_name) like :search ";
		$sql_search .= "or lower(directory_last_name) like :search ";
		$sql_search .= "or lower(call_group) like :search ";
		$sql_search .= "or lower(user_context) like :search ";
		$sql_search .= "or lower(enabled) like :search ";
		$sql_search .= "or lower(description) like :search ";
		$sql_search .= ") ";
		$parameters['search'] = '%'.$search.'%';
	}

//get total extension count for domain
	if (is_numeric($_SESSION['limit']['extensions']['numeric'])) {
		$sql = "select count(*) from v_extensions ";
		$sql .= "where domain_uuid = :domain_uuid ";
		$parameters['domain_uuid'] = $_SESSION['domain_uuid'];
		$database = new database;
		$total_extensions = $database->select($sql, $parameters, 'column');
		unset($sql, $parameters);
	}

//get total extension count
	$sql = "select count(*) from v_extensions where true ";
	if (!($_GET['show'] == "all" && permission_exists('extension_all'))) {
		$sql .= "and domain_uuid = :domain_uuid ";
		$parameters['domain_uuid'] = $_SESSION['domain_uuid'];
	}
	$sql .= $sql_search;
	$database = new database;
	$num_rows = $database->select($sql, $parameters, 'column');

//prepare to page the results
	$rows_per_page = ($_SESSION['domain']['paging']['numeric'] != '') ? $_SESSION['domain']['paging']['numeric'] : 50;
	$param = "&search=".$search;
	if ($_GET['show'] == "all" && permission_exists('extension_all')) {
		$param .= "&show=all";
	}
	$page = is_numeric($_GET['page']) ? $_GET['page'] : 0;
//echo $rows_per_page;exit;
	list($paging_controls, $rows_per_page) = paging($user_count, $param, $rows_per_page); //bottom
	list($paging_controls_mini, $rows_per_page) = paging($user_count, $param, $rows_per_page, true); //top
	$offset = $rows_per_page * $page;
$current_page_first_record_count = $page*$rows_per_page;
$current_page_last_record_count = $current_page_first_record_count +$rows_per_page;


//get the extensions
	$sql = str_replace('count(*)', '*', $sql);
	if ($order_by == '' || $order_by == 'extension') {
		if ($db_type == 'pgsql') {
			$sql .= 'order by natural_sort(extension) '.$order; //function in app_defaults.php
		}
		else {
			$sql .= 'order by extension '.$order;
		}
	}
	else {
		$sql .= order_by($order_by, $order);
	}
	$sql .= limit_offset($rows_per_page, $offset);
	$database = new database;
	$profiles = $grid_array;
	unset($sql, $parameters);


//create token
	$object = new token;
	$token = $object->create($_SERVER['PHP_SELF']);
	$v_domain_sql_new = "select domain_name ";
	$v_domain_sql_new .= "from v_domains where domain_uuid = '".$domain_uuid."'";
	$database = new database;
	$v_domain_sql_new_result = $database->select($v_domain_sql_new, $parameters);
	$domain_name = $v_domain_sql_new_result[0]['domain_name'];

//include the header
	$document['title'] = "Bria Profile";//$text['title-extensions'];
	require_once "resources/header.php";

//show the content
	echo "<div class='action_bar' id='action_bar'>\n";
	echo "	<div class='heading'><b>Bria Profile (".$user_count.")(Domain:".$domain_name.")</b></div>\n";
	echo "	<div class='actions'>\n";
	if (permission_exists('bria_profile_reload_group') ) {
//		echo "Group Reload take 5 to 10 minutes to reflacte new entry in GUI after reload*.";
//		echo button::create(['type'=>'button','label'=>'Group Reload','icon'=>$_SESSION['theme']['button_icon_refresh'],'id'=>'btn_add','style'=>$margin_left,'link'=>'bria_profile_add.php?manual=1']);

//		echo button::create(['type'=>'button','label'=>'Group Reload','icon'=>$_SESSION['theme']['button_icon_refresh'],'id'=>'btn_add','style'=>$margin_left,'onclick'=>'group_reload();']);
	}
	if (permission_exists('bria_profile_massupdate') ) {
		echo button::create(['type'=>'button','label'=>'Mass Update','icon'=>$_SESSION['theme']['button_icon_add'],'id'=>'btn_massupdate','style'=>$margin_left,'link'=>'bria_profile_massupdate.php']);
	}
	if (permission_exists('bria_profile_add') ) {
		echo button::create(['type'=>'button','label'=>$text['button-add'],'icon'=>$_SESSION['theme']['button_icon_add'],'id'=>'btn_add','style'=>$margin_left,'link'=>'bria_profile_add.php']);
		unset($margin_left);
	}
	echo "	</div>\n";
	echo "	<div style='clear: both;'></div>\n";
	echo "</div>\n";


	echo "\n";
	echo "<br /><br />\n";

	echo "<form id='form_list' method='post'>\n";
	echo "<input type='hidden' id='action' name='action' value=''>\n";
	echo "<input type='hidden' name='search' value=\"".escape($search)."\">\n";

	echo "<table class='list'>\n";
	echo "<tr class='list-header'>\n";
	echo th_order_by('userName', 'Sip Profile', $order_by, $order);
	echo th_order_by('password', 'Domain', $order_by, $order);
	if (permission_exists('bria_profile_delete')) {
		echo th_order_by('delete', 'Delete', $order_by, $order);
	}
	echo "</tr>\n";

	if (is_array($profiles) && @sizeof($profiles) != 0) {
		$x = 0;
		foreach($profiles as $ext_key=> $row) {
			if($ext_key >= $current_page_first_record_count && $ext_key < $current_page_last_record_count){
					$list_row_url = "bria_profile_edit.php?domain=".urlencode($row['domain_name'])."&username=".urlencode($row['profile_name']).(is_numeric($page) ? '&page='.urlencode($page) : null);
				echo "<tr class='list-row' >\n";
				echo "	<td>";
				if (permission_exists('bria_profile_edit')) {
					echo "<a href='".$list_row_url."' title=\"".$text['button-edit']."\">".escape($row['profile_name'])."</a>";
				}
				else {
					echo escape($row['profile_name']);
				}
				echo "	</td>\n";
				echo "	<td>";
					echo escape($row['domain_name']);
				echo "	</td>\n";
			if (permission_exists('bria_profile_delete')) {
				echo "	<td>";
				echo '<button type="button" name="btn_delete" alt="Delete" title="Delete" onclick="delete_bria(\''.$row["profile_name"].'\',\''.$row["domain_name"].'\');" class="btn btn-default "><span class="fas fa-trash fa-fw"></span><span class="button-label hide-md-dn pad">Delete</span></button>';
				echo "\n </td>\n";

			}
				echo "</tr>\n";
				$x++;
			}
		}
	}

	echo "</table>\n";
	echo "<br />\n";
	echo "<div align='center'>".$paging_controls."</div>\n";

	echo "<input type='hidden' name='".$token['name']."' value='".$token['hash']."'>\n";

	echo "</form>\n";

	unset($extensions);

//show the footer
	require_once "resources/footer.php";

?>
<script type="text/javascript" language="JavaScript">
function group_reload(){
	$.ajax({  
		type: "GET",  
		url: "bria_add.php?manual=1",  
		data: {'manual': 1},  
		success: function(res) {  
		}
	});
	document.location.href='briaprofile.php?reload=yes';

}
function delete_bria(extension,domain) {
var result = confirm('Are You Sure want to delete?');
	if (result) {
		$.ajax({  
			type: "POST",  
			url: "briaprofile.php",  
			data: {'profile_ajax': extension,'domain_ajax':domain},  
			success: function(res) {  
				if(res == true){
					document.location.href='briaprofile.php?delete=yes';
				}else{
					document.location.href='briaprofile.php?delete=no';
				}
			}  
		});
	}
}
</script>
