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
 Portions created by the Initial Developer are Copyright (C) 2008-2023
 the Initial Developer. All Rights Reserved.

 Contributor(s):
 Mark J Crane <markjcrane@fusionpbx.com>
*/

//set the include path
	$conf = glob("{/usr/local/etc,/etc}/fusionpbx/config.conf", GLOB_BRACE);
	set_include_path(parse_ini_file($conf[0])['document.root']);

//includes files
	require_once "resources/require.php";
	require_once "resources/check_auth.php";

//check permissions
	if (permission_exists('default_setting_clone')) {
		//access granted
	}
	else {
		echo "access denied";
		exit;
	}

//add multi-lingual support
	$language = new text;
	$text = $language->get();

//get submitted variables
	$search = $_REQUEST['search'];
	$default_setting_uuid = $_REQUEST["id"];

//clone the setting
	if (is_uuid($default_setting_uuid)) {
		//get current setting
			$sql = "select * from v_default_settings where default_setting_uuid = :default_setting_uuid ";
			$parameters['default_setting_uuid'] = $default_setting_uuid;
			$database = new database;
			$row = $database->select($sql, $parameters, 'row');
			unset($sql, $parameters);

		//override old values
			$default_setting_uuid = uuid();
			$row['default_setting_uuid'] = $default_setting_uuid;
			$row['insert_date'] = 'now()';
			$row['insert_user'] = 'user_uuid()';
			$row['update_date'] = null;
			$row['update_user'] = null;

		//set new status
			$array['default_settings'][0] = $row;
			$database = new database;
			$database->app_name = 'default_settings';
			$database->app_uuid = '2c2453c0-1bea-4475-9f44-4d969650de09';
			$database->save($array);
			$message = $database->message;
			unset($array);

	}

	$_SESSION["message"] = $text['message-cloned'];

//redirect the user
	$search = preg_replace('#[^a-zA-Z0-9_\-\.]# ', '', $search);
	header("Location: default_setting_edit.php?id=".$default_setting_uuid."".($search != '' ? '&search='.$search : null));

?>