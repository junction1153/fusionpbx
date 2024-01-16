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
	Portions created by the Initial Developer are Copyright (C) 2018-2020
	the Initial Developer. All Rights Reserved.

	Contributor(s):
	Mark J Crane <markjcrane@fusionpbx.com>
*/

//set the include path
	$conf = glob("{/usr/local/etc,/etc}/fusionpbx/config.conf", GLOB_BRACE);
	set_include_path(parse_ini_file($conf[0])['document.root']);

//includes
//	require_once "root.php";
	require_once "resources/require.php";
	require_once "resources/check_auth.php";

//check permissions
	if (permission_exists('domain_all')) {
		//access granted
	}
	else {
		echo "access denied";
		exit;
	}

//action add or update
	if (is_uuid($_REQUEST["group_uuid"])) {
		$group_uuid = $_REQUEST["group_uuid"];
	}
	if (count($_POST) > 0 ) {
		//redirect
		$group_uuid = $_POST['group_uuid'];	
		if(!empty($_POST['domain_checkbox'])){
			$domain_checkbox_str = "";
			foreach($_POST['domain_checkbox'] as $key => $val){
				$domain_checkbox_str .= $key.",";
			}
				$domain_checkbox_str .= ",";
		}
		$domain_checkbox_str_final = str_replace(",,","",$domain_checkbox_str);
		$sql = "select count(group_uuid) from domain_permissions ";
		$sql .= "where group_uuid='".$group_uuid."'";
		$database = new database;
		$domain_permissions_num_rows = $database->select($sql, $parameters, 'column');
		unset($sql, $parameters);
		$sql = "select user_uuid from v_users where username=:username ";
		$parameters['username'] = 'admin';
		$database = new database;
		$row = $database->select($sql, $parameters, 'row');
		$user_uuid = $row['user_uuid'];
		unset($sql, $parameters, $rows, $row);
		if($domain_permissions_num_rows == 0){
			$domain_permission_uuid = uuid();
			$sql = "insert into domain_permissions(domain_permission_uuid,group_uuid,domain_uuids,insert_date,insert_user) VALUES('".$domain_permission_uuid."','".$group_uuid."','".$domain_checkbox_str_final."',now(),'".$user_uuid."')";
		}else{
			$sql = "update domain_permissions set domain_uuids= '".$domain_checkbox_str_final."',update_date= now(),update_user='".$user_uuid."'  where group_uuid ='".$group_uuid."' ";
		}
//echo $sql;exit;
                $database = new database;
                $database->execute($sql, $parameters);
                unset($sql, $parameters);
		message::add("Domain Permission Updated Successfully");
		header('Location: domain_permissions.php?group_uuid='.urlencode($group_uuid));
		exit;
	}

	$sql = "select count(group_uuid) from domain_permissions ";
	$sql .= "where group_uuid='".$group_uuid."'";
	$database = new database;
	$domain_permissions_num_rows = $database->select($sql, $parameters, 'column');
	$permission_arr = array();
	unset($sql, $parameters);
	if($domain_permissions_num_rows > 0){
		$sql = "select domain_uuids ";
		$sql .= "from domain_permissions where group_uuid='".$group_uuid."'";
		$database = new database;
		$permission_result = $database->select($sql, $parameters, 'all');
		$permission_arr = explode(",",$permission_result[0]['domain_uuids']);
		if($permission_result[0]['domain_uuids'] != NULL){
			$domain_permissions_num_rows = count(explode(",",$permission_result[0]['domain_uuids']));
		}else{
			$domain_permissions_num_rows = 0;
		}
		unset($sql, $parameters);
	}
//get the list
	$sql = "select domain_uuid, domain_name, cast(domain_enabled as text), domain_description ";
	$sql .= "from v_domains ";
	$sql .= order_by($order_by, $order, 'domain_name', 'asc');
	$sql .= limit_offset($rows_per_page, $offset);
	$database = new database;
	$domains = $database->select($sql, $parameters, 'all');
	unset($sql, $parameters);
//create token
	$object = new token;
	$token = $object->create($_SERVER['PHP_SELF']);

//include the header
	$document['title'] = "Domain Permissions";
	require_once "resources/header.php";

//show the content
	echo "<div class='action_bar' id='action_bar'>\n";
	echo "	<div class='heading'><b>Domain Permissions (".escape($domain_permissions_num_rows).")</b></div>\n";
	echo "	<div class='actions'>\n";
	echo button::create(['type'=>'button','label'=>$text['button-back'],'icon'=>$_SESSION['theme']['button_icon_back'],'id'=>'btn_back','style'=>'margin-right: 15px;','collapse'=>'hide-sm-dn','link'=>'groups.php']);
	echo button::create(['type'=>'button','label'=>$text['button-save'],'icon'=>$_SESSION['theme']['button_icon_save'],'id'=>'btn_save','collapse'=>'hide-sm-dn','style'=>'margin-left: 15px;','onclick'=>"document.getElementById('form_list').submit();"]);
	echo "	</div>\n";
	echo "	<div style='clear: both;'></div>\n";
	echo "</div>\n";

	echo $text['description-group_permissions']."\n";
	echo "<br /><br />\n";

	echo "<form id='form_list' method='post'>\n";
	echo "<input type='hidden' name='".$token['name']."' value='".$token['hash']."'>\n";
	echo "<input type='hidden' name='group_uuid' value='".escape($group_uuid)."'>\n";
	echo "<input type='hidden' name='search' value=\"".escape($search)."\">\n";
	echo "<table class='list' style='margin-bottom: 25px;'>\n";
	if (is_array($domains) && @sizeof($domains) != 0) {
		$x = 0;
		foreach ($domains as $key => $row) {
			if(is_int($key/5)){
				$vals = 0;
				echo "<tr class='list-header'>";
			}
			$vals++;
			$uuid_domain = $row['domain_uuid'];
			echo "	<th class='checkbox'>";
			if(in_array($uuid_domain,$permission_arr)){
				$checked = "checked";
			}else{
				$checked = "";
			}	
			echo "		<input type='checkbox' id='checkbox_all_".$application_name."_protected' name='domain_checkbox[".$uuid_domain."]' ".$checked."> ".$row['domain_name'];
			echo "	</th>";
			if($vals ==5){
				echo "</tr>\n";
			}
		}

		unset($domain_permissions);
	}

	echo "</table>\n";
	echo "</form>\n";

//include the footer
	require_once "resources/footer.php";

?>
