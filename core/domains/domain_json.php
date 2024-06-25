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
	Portions created by the Initial Developer are Copyright (C) 2023
	the Initial Developer. All Rights Reserved.

	Contributor(s):
	Mark J Crane <markjcrane@fusionpbx.com>
*/

//includes files
	require_once dirname(__DIR__, 2) . "/resources/require.php";
	require_once "resources/check_auth.php";
	
//check permissions
	if (permission_exists('domain_view')) {
		//access granted
	}
	else {
		echo "access denied";
		exit;
	}

//get posted data
	if (!empty($_POST['search'])) {
		$search = $_POST['search'];
	}

//add the search term
	if (!empty($_GET["search"])) {
		$search = strtolower($_GET["search"]);
	}

//validate the token	
	//$token = new token;
	//if (!$token->validate($_SERVER['PHP_SELF'])) {
	//	message::add($text['message-invalid_token'],'negative');
	//	header('Location: /');
	//	exit;
	//}

//include css
	//echo "<link rel='stylesheet' type='text/css' href='/resources/fontawesome/css/all.min.css.php'>\n";

//get the list of domains
	if (permission_exists('domain_all') || permission_exists('domain_select')) {
		$sql = "select * ";
		$sql .= "from v_domains ";
		$sql .= "where true ";
		$sql .= "and domain_enabled = 'true' \n";
		if (isset($search)) {
			$sql .= "	and ( ";
			$sql .= "		lower(domain_name) like :search ";
			$sql .= "		or lower(domain_description) like :search ";
			$sql .= "	) ";
			$parameters['search'] = '%'.$search.'%';
		}
		$sql .= "order by domain_name asc ";
		$database = new database;
		$domains = $database->select($sql, $parameters ?? null, 'all');
		unset($sql, $parameters);
	}
	
//HP:START
//        unset($domains);
//        $domains= array();
//        unset($_SESSION['domains']);
//        $domain_sql = "select domain_uuid, domain_name, cast(domain_enabled as text), domain_description ";
//        $domain_sql .= "from v_domains ";
//        $domain_sql .= "where 1=1 ";
//        $key = 1;// array_search('superadmin', array_column($_SESSION['groups'], 'group_name'));
//        if(!empty($_SESSION['groups']) && !if_group("superadmin")){
//                $group_uuid_str = "";
//                foreach($_SESSION['groups'] as $vals){
//                        $group_uuid_str .= "'".$vals['group_uuid']."',";
//                }
//                $group_uuid_str .= ",";
//                $final_group_uuid_str = str_replace(",,","",$group_uuid_str);
//                $sql_new = "select domain_uuids ";
//                $sql_new .= "from domain_permissions where group_uuid IN (".$final_group_uuid_str.")";
//                $database = new database;
//                $permission_result = $database->select($sql_new, $parameters, 'all');
//                if(!empty($permission_result)){
//                        $domain_uuid_str = "";
//                        foreach($permission_result as $vals){
//                                if($vals['domain_uuids'] != NULL){
//                                        $permission_arr = explode(",",$vals['domain_uuids']);
//                                        foreach($permission_arr as $sub_vals){
//                                                if($sub_vals != ""){
//                                                        $domain_uuid_str .= "'".$sub_vals."',";
//                                                }
//                                        }
//                                }
//                        }
//                        $domain_uuid_str .= ",";
//                        $final_domain_uuid_str = str_replace(",,","",$domain_uuid_str);
//                        $domain_sql .= " and domain_uuid IN (".$final_domain_uuid_str.") ";
//                }
//                unset($sql_new, $parameters);
//        }
//                        if($search != ''){
//                                $domain_sql .= " and ( domain_name like '%".$search."%'";
//                                $domain_sql .= " or lower(domain_description) like '%".$search."%' ) ";
//
//                        }
//
//        $database = new database;
//        $parameters = array();
//        $domains = $database->select($domain_sql, $parameters, 'all');
//        if(!empty($domains)){
//                foreach($domains as $key=> $value)
//                $_SESSION['domains'][$value['domain_uuid']] = $value;
//        }else{
//                $_SESSION['domains'] = array();
//        }
//        unset($domain_sql, $parameters);
//
//HP:END


//get the domains
	if (file_exists($_SERVER["PROJECT_ROOT"]."/app/domains/app_config.php") && !is_cli()){
		require_once "app/domains/resources/domains.php";
	}

//debug information
	//print_r($domains);

//show the domains as json
	echo json_encode($domains, true);

?>
