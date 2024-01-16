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
		header('Location: briauser.php');
		exit;
	}
	if(isset($_GET['reload'])){
		if($_GET['reload'] == 'yes'){
			message::add("Group data reload in Sometime.");
		}
		header('Location: briauser.php');
		exit;
	}
	if($_POST > 0 && isset($_POST['extension_ajax'])){
		$explode_array = explode("@",$_POST['extension_ajax']);
		$url = $api_url."/stretto/prov/user?userName=".$_POST['extension_ajax']."&groupName=".$explode_array[1]; 
		$ch = curl_init();     
		curl_setopt($ch, CURLOPT_URL, $url);  
		curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_DIGEST); 
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
		curl_setopt($ch, CURLOPT_USERPWD, $api_username.":".$api_password);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
		curl_setopt($ch, CURLOPT_HEADER, false); 
		$result = curl_exec($ch);  
		$xml_array = json_decode(json_encode((array)simplexml_load_string($result)),true);
		$edit_array = $xml_array['CcsUser']['@attributes'];
		$url = $api_url."/stretto/prov/user?userName=".$_POST['extension_ajax']."&groupName=".$explode_array[1]; 
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

					$mail = new PHPMailer;
					$mail->isSMTP();
					$mail->Host = $smtp['host'];
					$mail->SMTPAuth = $smtp['auth'];
					$mail->Username = $smtp['username'];
					$mail->Password = $smtp['password'];
					$mail->SMTPSecure = $smtp['secure'];
					$mail->Port = $smtp['port'];
					$mail->From = $smtp['from'];
					$mail->FromName = $smtp['from_name'];
//					$mail->addAddress('patelharsh371@gmail.com', '');
					$mail->addAddress($edit_array['emailAddress'], '');
					$mail->addReplyTo($smtp['from'],  $smtp['from_name']);
					$mail->WordWrap = 50;
					$mail->isHTML(true); 
					$mail->Subject = $delete_email_subject;
					$body = str_replace("#USERNAME#",$_POST['extension_ajax'],$delete_email_body);
					$mail->Body    = $body;
					$mail->send();


				echo true; exit;
			}else{
				echo false; exit;
			}
		}else{
				echo false; exit;
		}
		curl_close($ch); 
	}
	include 'bria_group_content.php';
	$group_array = $group_content['group_array'];
	$grid_array = array();
	$v_bria_setting_new = "select bria_group ";
	$v_bria_setting_new .= "from bria_setting where domain_uuid = '".$domain_uuid."'";
	$database = new database;
	$v_bria_setting_result = $database->select($v_bria_setting_new, $parameters);
	$bria_group = $v_bria_setting_result[0]['bria_group'];
//	foreach($group_array as $group_value){
//		if(in_array($group_value,$domain_permission_list) || if_group("superadmin")){
		if($bria_group != ""){
			$url = $api_url."/stretto/prov/user?includeDevices=true&groupName=".$bria_group; 
			$ch = curl_init();     
			curl_setopt($ch, CURLOPT_URL, $url);  
			curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_DIGEST); 
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
			curl_setopt($ch, CURLOPT_USERPWD, $api_username.":".$api_password);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
			curl_setopt($ch, CURLOPT_HEADER, false); 
			$result = curl_exec($ch);  
			$xml_array = json_decode(json_encode((array)simplexml_load_string($result)),true);
			curl_close($ch);
			if(isset($xml_array['CcsUser'])){
				if(isset($xml_array['CcsUser'][0])){
					foreach($xml_array['CcsUser'] as $key => $value){
						if(isset($value['@attributes'])){
							$grid_array[] = $value['@attributes'];
						}
					}
				}else{
					foreach($xml_array['CcsUser'] as $key => $value){
						if($key == '@attributes'){
							$grid_array[] = $value;
						}
					}
				}
			}
		}
//		}
//	}
	$user_count =count($grid_array);
//check permissions
	if (permission_exists('bria_view')) {
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

//process the http post data by action
	if ($action != '' && is_array($extensions) && @sizeof($extensions) != 0) {
		switch ($action) {
			case 'toggle':
				if (permission_exists('extension_enabled')) {
					$obj = new extension;
					$obj->toggle($extensions);
				}
				break;
			case 'delete_extension':
			case 'delete_extension_voicemail':
				if (permission_exists('extension_delete')) {
					$obj = new extension;
					if ($action == 'delete_extension_voicemail' && permission_exists('voicemail_delete')) {
						$obj->delete_voicemail = true;
					}
					$obj->delete($extensions);
				}
				break;
		}

		header('Location: extensions.php'.($search != '' ? '?search='.urlencode($search) : null));
		exit;
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
	$extensions = $grid_array;
	unset($sql, $parameters);

//get the registrations
	if (permission_exists('extension_registered')) {
		$obj = new registrations;
		if ($_GET['show'] == 'all') {
			$obj->show = 'all';
		}
		$registrations = $obj->get('all');
	}

//create token
	$object = new token;
	$token = $object->create($_SERVER['PHP_SELF']);
	$v_domain_sql_new = "select domain_name ";
	$v_domain_sql_new .= "from v_domains where domain_uuid = '".$domain_uuid."'";
	$database = new database;
	$v_domain_sql_new_result = $database->select($v_domain_sql_new, $parameters);
	$domain_name = $v_domain_sql_new_result[0]['domain_name'];

//include the header
	$document['title'] = "Bria User";//$text['title-extensions'];
	require_once "resources/header.php";

//show the content
	echo "<div class='action_bar' id='action_bar'>\n";
	echo "	<div class='heading'><b>Bria User (".$user_count.")(Domain:".$domain_name.")</b></div>\n";
	echo "	<div class='actions'>\n";
	if (permission_exists('bria_reload_group') ) {
//		echo "Group Reload take 5 to 10 minutes to reflacte new entry in GUI after reload*.";
//		echo button::create(['type'=>'button','label'=>'Group Reload','icon'=>$_SESSION['theme']['button_icon_refresh'],'id'=>'btn_add','style'=>$margin_left,'link'=>'bria_add.php?manual=1']);
//		echo button::create(['type'=>'button','label'=>'Group Reload','icon'=>$_SESSION['theme']['button_icon_refresh'],'id'=>'btn_add','style'=>$margin_left,'onclick'=>'group_reload();']);
	}
	if (permission_exists('bria_add')) {
		echo button::create(['type'=>'button','label'=>$text['button-add'],'icon'=>$_SESSION['theme']['button_icon_add'],'id'=>'btn_add','style'=>$margin_left,'link'=>'bria_add.php']);
		unset($margin_left);
	}
	echo "	</div>\n";
	echo "	<div style='clear: both;'></div>\n";
	echo "</div>\n";

	if (permission_exists('extension_enabled') && $extensions) {
		echo modal::create(['id'=>'modal-toggle','type'=>'toggle','actions'=>button::create(['type'=>'button','label'=>$text['button-continue'],'icon'=>'check','id'=>'btn_toggle','style'=>'float: right; margin-left: 15px;','collapse'=>'never','onclick'=>"modal_close(); list_action_set('toggle'); list_form_submit('form_list');"])]);
	}
	if (permission_exists('extension_delete') && $extensions) {
		if (permission_exists('voicemail_delete')) {
			echo modal::create([
				'id'=>'modal-delete-options',
				'title'=>$text['modal_title-confirmation'],
				'message'=>$text['message-delete_selection'],
				'actions'=>
					button::create(['type'=>'button','label'=>$text['button-cancel'],'icon'=>$_SESSION['theme']['button_icon_cancel'],'collapse'=>'hide-xs','onclick'=>'modal_close();']).
					button::create(['type'=>'button','label'=>$text['label-extension_and_voicemail'],'icon'=>'voicemail','style'=>'float: right; margin-left: 15px;','collapse'=>'never','onclick'=>"modal_close(); list_action_set('delete_extension_voicemail'); list_form_submit('form_list');"]).
					button::create(['type'=>'button','label'=>$text['label-extension_only'],'icon'=>'phone-alt','collapse'=>'never','style'=>'float: right;','onclick'=>"modal_close(); list_action_set('delete_extension'); list_form_submit('form_list');"])
				]);
		}
		else {
			echo modal::create(['id'=>'modal-delete','type'=>'delete','actions'=>button::create(['type'=>'button','label'=>$text['button-continue'],'id'=>'btn_delete','icon'=>'check','style'=>'float: right; margin-left: 15px;','collapse'=>'never','onclick'=>"modal_close(); list_action_set('delete_extension'); list_form_submit('form_list');"])]);
		}
	}

	echo "\n";
	echo "<br /><br />\n";

	echo "<form id='form_list' method='post'>\n";
	echo "<input type='hidden' id='action' name='action' value=''>\n";
	echo "<input type='hidden' name='search' value=\"".escape($search)."\">\n";

	echo "<table class='list'>\n";
	echo "<tr class='list-header'>\n";
	echo th_order_by('userName', 'User Name', $order_by, $order);
	echo th_order_by('password', 'Domain', $order_by, $order);
	echo th_order_by('password', 'Password', $order_by, $order);
	echo th_order_by('profile', 'profile Name', $order_by, $order);
	echo th_order_by('emailAddress', 'Email Address', $order_by, $order);
	if (permission_exists('bria_delete')) {
		echo th_order_by('delete', 'Delete', $order_by, $order);
	}
	if (permission_exists('extension_edit') && $_SESSION['theme']['list_row_edit_button']['boolean'] == 'true') {
		echo "	<td class='action-button'>&nbsp;</td>\n";
	}
	echo "</tr>\n";
	if (is_array($extensions) && @sizeof($extensions) != 0) {
		$x = 0;
		foreach($extensions as $ext_key=> $row) {
			if($ext_key >= $current_page_first_record_count && $ext_key < $current_page_last_record_count){
					$list_row_url = "bria_edit.php?username=".urlencode($row['userName']).(is_numeric($page) ? '&page='.urlencode($page) : null);
				echo "<tr class='list-row' >\n";
				echo "	<td>";
					$user_name_explode= explode("@",$row['userName']);

				if (permission_exists('bria_edit')) {
					echo "<a href='".$list_row_url."' title=\"".$text['button-edit']."\">".escape($user_name_explode[0])."</a>";
				}
				else {
					echo escape($user_name_explode[0]);
				}
				echo "	</td>\n";
				echo "	<td>";
					echo escape($user_name_explode[1]);
				echo "	</td>\n";
				echo "	<td>";
					echo escape($row['password']);
				echo "	</td>\n";
				echo "	<td>";
					echo escape($row['profileName']);
				echo "	</td>\n";
				echo "	<td>";
					echo escape($row['emailAddress']);
				echo "	</td>\n";
				if (permission_exists('bria_delete')) {
					echo "	<td>";
					echo '<button type="button" name="btn_delete" alt="Delete" title="Delete" onclick="delete_bria(\''.$row["userName"].'\');" class="btn btn-default "><span class="fas fa-trash fa-fw"></span><span class="button-label hide-md-dn pad">Delete</span></button>';
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
		async:false,
		url: "bria_add.php?manual=1",  
		data: {'manual': 1},  
		success: function(res) {  
		}
	});
	document.location.href='briauser.php?reload=yes';

}
function delete_bria(extension) {
var result = confirm('Are You Sure want to delete?');
	if (result) {
		$.ajax({  
			type: "POST",  
			url: "briauser.php",  
			data: {'extension_ajax': extension},  
			success: function(res) {  
				if(res == true){
					document.location.href='briauser.php?delete=yes';
				}else{
					document.location.href='briauser.php?delete=no';
				}
			}  
		});
	}
}
</script>
