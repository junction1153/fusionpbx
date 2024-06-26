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
	Copyright (C) 2010 - 2019
	All Rights Reserved.

	Contributor(s):
	Mark J Crane <markjcrane@fusionpbx.com>
*/

//define the device class
	class device {
		public $db;
		public $domain_uuid;
		public $template_dir;
		public $device_uuid;
		public $device_vendor_uuid;
		public $device_profile_uuid;

		/**
		 * declare private variables
		 */
		private $app_name;
		private $app_uuid;
		private $permission_prefix;
		private $list_page;
		private $table;
		private $uuid_prefix;
		private $toggle_field;
		private $toggle_values;

		public function __construct() {

			//assign private variables
				$this->app_name = 'devices';
				$this->app_uuid = '4efa1a1a-32e7-bf83-534b-6c8299958a8e';

		}

		public function get_domain_uuid() {
			return $this->domain_uuid;
		}

		public static function get_vendor($mac) {
			//return if the mac address is empty
				if(empty($mac)) {
					return '';
				}

			//use the mac address to find the vendor
				$mac = preg_replace('#[^a-fA-F0-9./]#', '', $mac);
				$mac = strtolower($mac);
				switch (substr($mac, 0, 6)) {
				case "00085d":
					$device_vendor = "aastra";
					break;
				case "001873":
					$device_vendor = "cisco";
					break;
				case "a44c11":
					$device_vendor = "cisco";
					break;
				case "0021A0":
					$device_vendor = "cisco";
					break;
				case "30e4db":
					$device_vendor = "cisco";
					break;
				case "002155":
					$device_vendor = "cisco";
					break;
				case "68efbd":
					$device_vendor = "cisco";
					break;
				case "000b82":
					$device_vendor = "grandstream";
					break;
				case "00177d":
					$device_vendor = "konftel";
					break;
				case "00045a":
					$device_vendor = "linksys";
					break;
				case "000625":
					$device_vendor = "linksys";
					break;
				case "000e08":
					$device_vendor = "linksys";
					break;
				case "08000f":
					$device_vendor = "mitel";
					break;
				case "0080f0":
					$device_vendor = "panasonic";
					break;
				case "0004f2":
					$device_vendor = "polycom";
					break;
				case "00907a":
					$device_vendor = "polycom";
					break;
				case "64167f":
					$device_vendor = "polycom";
					break;
				case "482567":
					$device_vendor = "polycom";
					break;
				case "000413":
					$device_vendor = "snom";
					break;
				case "001565":
					$device_vendor = "yealink";
					break;
				case "805ec0":
					$device_vendor = "yealink";
					break;
				case "00268B":
					$device_vendor = "escene";
					break;
				case "001fc1":
					$device_vendor = "htek";
					break;
				case "0C383E":
					$device_vendor = "fanvil";
					break;
				case "7c2f80":
					$device_vendor = "gigaset";
					break;
				case "14b370":
					$device_vendor = "gigaset";
					break;
				case "002104":
					$device_vendor = "gigaset";
					break;
				case "bcc342":
					$device_vendor = "panasonic";
					break;
				case "080023":
					$device_vendor = "panasonic";
					break;
				case "0080f0":
					$device_vendor = "panasonic";
					break;
				case "0021f2":
					$device_vendor = "flyingvoice";
					break;					
				default:
					$device_vendor = "";
				}
				return $device_vendor;
		}

		public static function get_vendor_by_agent($agent){
			if ($agent) {
					$agent = strtolower($agent);
				//get the vendor
					if (preg_replace('/^.*?(aastra).*$/i', '$1', $agent) == "aastra") {
						return "aastra";
					}
					if (preg_replace('/^.*?(cisco\/spa).*$/i', '$1', $agent) == "cisco/spa") {
						return "cisco-spa";
					}
					if (preg_replace('/^.*?(cisco).*$/i', '$1', $agent) == "cisco") {
						return "cisco";
					}
					if (preg_replace('/^.*?(digium).*$/i', '$1', $agent) == "digium") {
                                                return "digium";
                                        }
					if (preg_replace('/^.*?(grandstream).*$/i', '$1', $agent) == "grandstream") {
						return "grandstream";
					}
					if (preg_replace('/^.*?(linksys).*$/i', '$1', $agent) == "linksys") {
						return "linksys";
					}
					if (preg_replace('/^.*?(polycom).*$/i', '$1', $agent) == "polycom") {
						return "polycom";
					}
					if (preg_replace('/^.*?(yealink).*$/i', '$1', $agent) == "yealink") {
						return "yealink";
					}
					if (preg_replace('/^.*?(vp530p).*$/i', '$1', $agent) == "vp530p") {
						return "yealink";
					}
					if (preg_replace('/^.*?(snom).*$/i', '$1', $agent) == "snom") {
						return "snom";
					}
					if (preg_match('/^.*?addpac.*$/i', $agent)) {
						return "addpac";
					}
					/*Escene use User-Agent string like `ES320VN2 v4.0 ...  or `ES206 v1.0 ...` */
					if (preg_match('/^es\d\d\d.*$/i', $agent)) {
						return "escene";
					}
					if (preg_match('/^.*?panasonic.*$/i', $agent)) {
						return "panasonic";
					}
					if (preg_replace('/^.*?(N510).*$/i', '$1', $agent) == "n510") {
						return "gigaset";
					}
					if (preg_match('/^.*?htek.*$/i', $agent)) {
						return "htek";
					}
					if (preg_replace('/^.*?(fanvil).*$/i', '$1', $agent) == "fanvil") {
						return "fanvil";
					}
					if (preg_replace('/^.*?(flyingvoice).*$/i', '$1', $agent) == "flyingvoice") {
						return "flyingvoice";
					}
					// unknown vendor
					return "";
				}
		}

		public function get_template_dir() {
			//set the default template directory
				if (PHP_OS == "Linux") {
					//set the default template dir
						if (empty($this->template_dir)) {
							if (file_exists('/usr/share/fusionpbx/templates/provision')) {
								$this->template_dir = '/usr/share/fusionpbx/templates/provision';
							}
							elseif (file_exists('/etc/fusionpbx/resources/templates/provision')) {
								$this->template_dir = '/etc/fusionpbx/resources/templates/provision';
							}
							else {
								$this->template_dir = $_SERVER["DOCUMENT_ROOT"].PROJECT_PATH.'/resources/templates/provision';
							}
						}
				}
				elseif (PHP_OS == "FreeBSD") {
					//if the FreeBSD port is installed use the following paths by default.
						if (empty($this->template_dir)) {
							if (file_exists('/usr/local/share/fusionpbx/templates/provision')) {
								$this->template_dir = '/usr/local/share/fusionpbx/templates/provision';
							}
							elseif (file_exists('/usr/local/etc/fusionpbx/resources/templates/provision')) {
								$this->template_dir = '/usr/local/etc/fusionpbx/resources/templates/provision';
							}
							else {
								$this->template_dir = $_SERVER["DOCUMENT_ROOT"].PROJECT_PATH.'/resources/templates/provision';
							}
						}
				}
				elseif (PHP_OS == "NetBSD") {
					//set the default template_dir
						if (empty($this->template_dir)) {
							$this->template_dir = $_SERVER["DOCUMENT_ROOT"].PROJECT_PATH.'/resources/templates/provision';
						}
				}
				elseif (PHP_OS == "OpenBSD") {
					//set the default template_dir
						if (empty($this->template_dir)) {
							$this->template_dir = $_SERVER["DOCUMENT_ROOT"].PROJECT_PATH.'/resources/templates/provision';
						}
				}
				else {
					//set the default template_dir
						if (empty($this->template_dir)) {
							$this->template_dir = $_SERVER["DOCUMENT_ROOT"].PROJECT_PATH.'/resources/templates/provision';
						}
				}

			//check to see if the domain name sub directory exists
				if (is_dir($this->template_dir."/".$_SESSION["domain_name"])) {
					$this->template_dir = $this->template_dir."/".$_SESSION["domain_name"];
				}

			//return the template directory
				return $this->template_dir;
		}

		/**
		 * delete records
		 */
		public function delete($records) {

			//assign private variables
				$this->permission_prefix = 'device_';
				$this->list_page = 'devices.php';
				$this->table = 'devices';
				$this->uuid_prefix = 'device_';

			if (permission_exists($this->permission_prefix.'delete')) {

				//add multi-lingual support
					$language = new text;
					$text = $language->get();

				//validate the token
					$token = new token;
					if (!$token->validate($_SERVER['PHP_SELF'])) {
						message::add($text['message-invalid_token'],'negative');
						header('Location: '.$this->list_page);
						exit;
					}

				//delete multiple records
					if (is_array($records) && @sizeof($records) != 0) {

						//build the delete array
							foreach ($records as $x => $record) {
								if (!empty($record['checked']) && $record['checked'] == 'true' && is_uuid($record['uuid'])) {
									$sql = "update v_devices set device_uuid_alternate = null where device_uuid_alternate = :device_uuid_alternate; ";
									$parameters['device_uuid_alternate'] = $record['uuid'];
									$database = new database;
									$database->execute($sql, $parameters);
									unset($sql, $parameters);
									//Pooja: Start code for Edit ZTP-profile for polycom
									$sql = "select * from v_devices ";
									$sql .= "where device_uuid = :device_uuid ";
									$parameters['device_uuid'] = $record['uuid'];
									$database = new database;
									$row = $database->select($sql, $parameters, 'row');
									if (is_array($row) && @sizeof($row) != 0) {
										if((isset($row["ztp_reference_id"]) && !empty($row["ztp_reference_id"])) && (isset($row["device_address"]) && !empty($row["device_address"]))){
											$delete_ztp_device = device::delete_ZTP_profile($row["ztp_reference_id"],$row["device_address"]);
											/*if($delete_ztp_device['status'] == "Error"){
												message::add($delete_ztp_device['message'],'negative');
												header('Location: ' . $this->list_page);
											exit;
											}*/ 
										}
										}
									//Pooja: Start code for Edit ZTP-profile for polycom

									$array[$this->table][$x][$this->uuid_prefix.'uuid'] = $record['uuid'];
									$array['device_settings'][$x]['device_uuid'] = $record['uuid'];
									$array['device_lines'][$x]['device_uuid'] = $record['uuid'];
									$array['device_keys'][$x]['device_uuid'] = $record['uuid'];
								}
							}

						//delete the checked rows
							if (is_array($array) && @sizeof($array) != 0) {

								//grant temporary permissions
									$p = new permissions;
									$p->add('device_setting_delete', 'temp');
									$p->add('device_line_delete', 'temp');
									$p->add('device_key_delete', 'temp');

								//execute delete
									$database = new database;
									$database->app_name = $this->app_name;
									$database->app_uuid = $this->app_uuid;
									$database->delete($array);
									unset($array);

								//revoke temporary permissions
									$p->delete('device_setting_delete', 'temp');
									$p->delete('device_line_delete', 'temp');
									$p->delete('device_key_delete', 'temp');

								//write the provision files
									if (!empty($_SESSION['provision']['path']['text'])) {
										$prov = new provision;
										$prov->domain_uuid = $_SESSION['domain_uuid'];
										$response = $prov->write();
									}

								//set message
									message::add($text['message-delete']);

							}
							unset($records);
					}
			}
		}

		public function delete_lines($records) {
			//assign private variables
				$this->permission_prefix = 'device_line_';
				$this->table = 'device_lines';
				$this->uuid_prefix = 'device_line_';

			if (permission_exists($this->permission_prefix.'delete')) {

				//add multi-lingual support
					$language = new text;
					$text = $language->get();

				//validate the token
					$token = new token;
					if (!$token->validate($_SERVER['PHP_SELF'])) {
						message::add($text['message-invalid_token'],'negative');
						header('Location: '.$this->list_page);
						exit;
					}

				//delete multiple records
					if (is_array($records) && @sizeof($records) != 0) {

						//filter out unchecked device lines, build delete array
							$x = 0;
							foreach ($records as $record) {
								if (!empty($record['checked']) && $record['checked'] == 'true' && is_uuid($record['uuid'])) {
									$array[$this->table][$x][$this->uuid_prefix.'uuid'] = $record['uuid'];
									$array[$this->table][$x]['device_uuid'] = $this->device_uuid;
									$x++;
								}
							}

						//delete the checked rows
							if (!empty($array) && is_array($array) && @sizeof($array) != 0) {
								//execute delete
									$database = new database;
									$database->app_name = $this->app_name;
									$database->app_uuid = $this->app_uuid;
									$database->delete($array);
									unset($array);
							}
							unset($records);
					}
			}
		}

		public function delete_keys($records) {
			//assign private variables
				$this->permission_prefix = 'device_key_';
				$this->table = 'device_keys';
				$this->uuid_prefix = 'device_key_';

			if (permission_exists($this->permission_prefix.'delete')) {

				//add multi-lingual support
					$language = new text;
					$text = $language->get();

				//validate the token
					$token = new token;
					if (!$token->validate($_SERVER['PHP_SELF'])) {
						message::add($text['message-invalid_token'],'negative');
						header('Location: '.$this->list_page);
						exit;
					}

				//delete multiple records
					if (is_array($records) && @sizeof($records) != 0) {

						//filter out unchecked device keys, build delete array
							$x = 0;
							foreach ($records as $record) {
								if (!empty($record['checked']) && $record['checked'] == 'true' && is_uuid($record['uuid'])) {
									$array[$this->table][$x][$this->uuid_prefix.'uuid'] = $record['uuid'];
									$array[$this->table][$x]['device_uuid'] = $this->device_uuid;
									$x++;
								}
							}

						//delete the checked rows
							if (!empty($array) && is_array($array) && @sizeof($array) != 0) {
								//execute delete
									$database = new database;
									$database->app_name = $this->app_name;
									$database->app_uuid = $this->app_uuid;
									$database->delete($array);
									unset($array);
							}
							unset($records);
					}
			}
		}

		public function delete_settings($records) {
			//assign private variables
				$this->permission_prefix = 'device_setting_';
				$this->table = 'device_settings';
				$this->uuid_prefix = 'device_setting_';

			if (permission_exists($this->permission_prefix.'delete')) {

				//add multi-lingual support
					$language = new text;
					$text = $language->get();

				//validate the token
					$token = new token;
					if (!$token->validate($_SERVER['PHP_SELF'])) {
						message::add($text['message-invalid_token'],'negative');
						header('Location: '.$this->list_page);
						exit;
					}

				//delete multiple records
					if (is_array($records) && @sizeof($records) != 0) {

						//filter out unchecked device settings, build delete array
							$x = 0;
							foreach ($records as $record) {
								if (!empty($record['checked']) && $record['checked'] == 'true' && is_uuid($record['uuid'])) {
									$array[$this->table][$x][$this->uuid_prefix.'uuid'] = $record['uuid'];
									$array[$this->table][$x]['device_uuid'] = $this->device_uuid;
									$x++;
								}
							}

						//delete the checked rows
							if (!empty($array) && is_array($array) && @sizeof($array) != 0) {
								//execute delete
									$database = new database;
									$database->app_name = $this->app_name;
									$database->app_uuid = $this->app_uuid;
									$database->delete($array);
									unset($array);
							}
							unset($records);
					}
			}
		}

		public function delete_vendors($records) {

			//assign private variables
				$this->permission_prefix = 'device_vendor_';
				$this->list_page = 'device_vendors.php';
				$this->tables[] = 'device_vendors';
				$this->tables[] = 'device_vendor_functions';
				$this->tables[] = 'device_vendor_function_groups';
				$this->uuid_prefix = 'device_vendor_';

			if (permission_exists($this->permission_prefix.'delete')) {

				//add multi-lingual support
					$language = new text;
					$text = $language->get();

				//validate the token
					$token = new token;
					if (!$token->validate($_SERVER['PHP_SELF'])) {
						message::add($text['message-invalid_token'],'negative');
						header('Location: '.$this->list_page);
						exit;
					}

				//delete multiple records
					if (is_array($records) && @sizeof($records) != 0) {

						//build the delete array
							foreach ($records as $x => $record) {
								if (!empty($record['checked']) && $record['checked'] == 'true' && is_uuid($record['uuid'])) {
									foreach ($this->tables as $table) {
										$array[$table][$x][$this->uuid_prefix.'uuid'] = $record['uuid'];
									}
								}
							}

						//delete the checked rows
							if (!empty($array) && is_array($array) && @sizeof($array) != 0) {

								//grant temporary permissions
									$p = new permissions;
									$p->add('device_vendor_function_delete', 'temp');
									$p->add('device_vendor_function_group_delete', 'temp');

								//execute delete
									$database = new database;
									$database->app_name = $this->app_name;
									$database->app_uuid = $this->app_uuid;
									$database->delete($array);
									unset($array);

								//revoke temporary permissions
									$p->delete('device_vendor_function_delete', 'temp');
									$p->delete('device_vendor_function_group_delete', 'temp');

								//set message
									message::add($text['message-delete']);

							}
							unset($records);
					}
			}
		}

		public function delete_vendor_functions($records) {

			//assign private variables
				$this->permission_prefix = 'device_vendor_function_';
				$this->list_page = 'device_vendor_edit.php';
				$this->tables[] = 'device_vendor_functions';
				$this->tables[] = 'device_vendor_function_groups';
				$this->uuid_prefix = 'device_vendor_function_';

			if (permission_exists($this->permission_prefix.'delete')) {

				//add multi-lingual support
					$language = new text;
					$text = $language->get();

				//validate the token
					$token = new token;
					if (!$token->validate('/app/devices/device_vendor_functions.php')) {
						message::add($text['message-invalid_token'],'negative');
						header('Location: '.$this->list_page.'?id='.$this->device_vendor_uuid);
						exit;
					}

				//delete multiple records
					if (is_array($records) && @sizeof($records) != 0) {

						//build the delete array
							foreach ($records as $x => $record) {
								if (!empty($record['checked']) && $record['checked'] == 'true' && is_uuid($record['uuid'])) {
									foreach ($this->tables as $table) {
										$array[$table][$x][$this->uuid_prefix.'uuid'] = $record['uuid'];
									}
								}
							}

						//delete the checked rows
							if (!empty($array) && is_array($array) && @sizeof($array) != 0) {

								//grant temporary permissions
									$p = new permissions;
									$p->add('device_vendor_function_group_delete', 'temp');

								//execute delete
									$database = new database;
									$database->app_name = $this->app_name;
									$database->app_uuid = $this->app_uuid;
									$database->delete($array);
									unset($array);

								//revoke temporary permissions
									$p->delete('device_vendor_function_group_delete', 'temp');

								//set message
									message::add($text['message-delete']);

							}
							unset($records);
					}
			}
		}

		public function delete_profiles($records) {

			//assign private variables
				$this->permission_prefix = 'device_profile_';
				$this->list_page = 'device_profiles.php';
				$this->tables[] = 'device_profiles';
				$this->tables[] = 'device_profile_keys';
				$this->tables[] = 'device_profile_settings';
				$this->uuid_prefix = 'device_profile_';

			if (permission_exists($this->permission_prefix.'delete')) {

				//add multi-lingual support
					$language = new text;
					$text = $language->get();

				//validate the token
					$token = new token;
					if (!$token->validate($_SERVER['PHP_SELF'])) {
						message::add($text['message-invalid_token'],'negative');
						header('Location: '.$this->list_page);
						exit;
					}

				//delete multiple records
					if (is_array($records) && @sizeof($records) != 0) {

						//build the delete array
							foreach ($records as $x => $record) {
								if (!empty($record['checked']) && $record['checked'] == 'true' && is_uuid($record['uuid'])) {
									foreach ($this->tables as $table) {
										$array[$table][$x][$this->uuid_prefix.'uuid'] = $record['uuid'];
									}
								}
							}

						//delete the checked rows
							if (!empty($array) && is_array($array) && @sizeof($array) != 0) {

								//grant temporary permissions
									$p = new permissions;
									$p->add('device_profile_key_delete', 'temp');
									$p->add('device_profile_setting_delete', 'temp');

								//execute delete
									$database = new database;
									$database->app_name = $this->app_name;
									$database->app_uuid = $this->app_uuid;
									$database->delete($array);
									unset($array);

								//revoke temporary permissions
									$p->delete('device_profile_key_delete', 'temp');
									$p->delete('device_profile_setting_delete', 'temp');

								//set message
									message::add($text['message-delete']);

							}
							unset($records);
					}
			}
		}

		public function delete_profile_keys($records) {

			//assign private variables
				$this->permission_prefix = 'device_profile_key_';
				$this->list_page = 'device_profile_edit.php?id='.$this->device_profile_uuid;
				$this->table = 'device_profile_keys';
				$this->uuid_prefix = 'device_profile_key_';

			if (permission_exists($this->permission_prefix.'delete')) {

				//add multi-lingual support
					$language = new text;
					$text = $language->get();

				//validate the token
					$token = new token;
					if (!$token->validate($_SERVER['PHP_SELF'])) {
						message::add($text['message-invalid_token'],'negative');
						header('Location: '.$this->list_page);
						exit;
					}

				//delete multiple records
					if (is_array($records) && @sizeof($records) != 0) {

						//build the delete array
							foreach ($records as $x => $record) {
								if (!empty($record['checked']) && $record['checked'] == 'true' && is_uuid($record['uuid'])) {
									$array[$this->table][$x][$this->uuid_prefix.'uuid'] = $record['uuid'];
								}
							}

						//execute delete
							if (!empty($array) && is_array($array) && @sizeof($array) != 0) {
								$database = new database;
								$database->app_name = $this->app_name;
								$database->app_uuid = $this->app_uuid;
								$database->delete($array);
								unset($array);
							}
							unset($records);

					}
			}
		}

		public function delete_profile_settings($records) {

			//assign private variables
				$this->permission_prefix = 'device_profile_setting_';
				$this->list_page = 'device_profile_edit.php?id='.$this->device_profile_uuid;
				$this->table = 'device_profile_settings';
				$this->uuid_prefix = 'device_profile_setting_';

			if (permission_exists($this->permission_prefix.'delete')) {

				//add multi-lingual support
					$language = new text;
					$text = $language->get();

				//validate the token
					$token = new token;
					if (!$token->validate($_SERVER['PHP_SELF'])) {
						message::add($text['message-invalid_token'],'negative');
						header('Location: '.$this->list_page);
						exit;
					}

				//delete multiple records
					if (is_array($records) && @sizeof($records) != 0) {

						//build the delete array
							foreach ($records as $x => $record) {
								if (!empty($record['checked']) && $record['checked'] == 'true' && is_uuid($record['uuid'])) {
									$array[$this->table][$x][$this->uuid_prefix.'uuid'] = $record['uuid'];
								}
							}

						//execute delete
							if (!empty($array) && is_array($array) && @sizeof($array) != 0) {
								$database = new database;
								$database->app_name = $this->app_name;
								$database->app_uuid = $this->app_uuid;
								$database->delete($array);
								unset($array);
							}
							unset($records);

					}
			}
		}

		/**
		 * toggle records
		 */
		public function toggle($records) {

			//assign private variables
				$this->permission_prefix = 'device_';
				$this->list_page = 'devices.php';
				$this->table = 'devices';
				$this->uuid_prefix = 'device_';
				$this->toggle_field = 'device_enabled';
				$this->toggle_values = ['true','false'];

			if (permission_exists($this->permission_prefix.'edit')) {

				//add multi-lingual support
					$language = new text;
					$text = $language->get();

				//validate the token
					$token = new token;
					if (!$token->validate($_SERVER['PHP_SELF'])) {
						message::add($text['message-invalid_token'],'negative');
						header('Location: '.$this->list_page);
						exit;
					}

				//toggle the checked records
					if (is_array($records) && @sizeof($records) != 0) {

						//get current toggle state
							foreach ($records as $x => $record) {
								if (!empty($record['checked']) && $record['checked'] == 'true' && is_uuid($record['uuid'])) {
									$uuids[] = "'".$record['uuid']."'";
								}
							}
							if (is_array($uuids) && @sizeof($uuids) != 0) {
								$sql = "select ".$this->uuid_prefix."uuid as uuid, ".$this->toggle_field." as toggle from v_".$this->table." ";
								$sql .= "where (domain_uuid = :domain_uuid or domain_uuid is null) ";
								$sql .= "and ".$this->uuid_prefix."uuid in (".implode(', ', $uuids).") ";
								$parameters['domain_uuid'] = $_SESSION['domain_uuid'];
								$database = new database;
								$rows = $database->select($sql, $parameters, 'all');
								if (is_array($rows) && @sizeof($rows) != 0) {
									foreach ($rows as $row) {
										$states[$row['uuid']] = $row['toggle'];
									}
								}
								unset($sql, $parameters, $rows, $row);
							}

						//build update array
							$x = 0;
							foreach ($states as $uuid => $state) {
								$array[$this->table][$x][$this->uuid_prefix.'uuid'] = $uuid;
								$array[$this->table][$x][$this->toggle_field] = $state == $this->toggle_values[0] ? $this->toggle_values[1] : $this->toggle_values[0];
								$x++;
							}

						//save the changes
							if (!empty($array) && is_array($array) && @sizeof($array) != 0) {

								//save the array
									$database = new database;
									$database->app_name = $this->app_name;
									$database->app_uuid = $this->app_uuid;
									$database->save($array);
									unset($array);

								//write the provision files
									if (!empty($_SESSION['provision']['path']['text'])) {
										$prov = new provision;
										$prov->domain_uuid = $_SESSION['domain_uuid'];
										$response = $prov->write();
									}

								//set message
									message::add($text['message-toggle']);
							}
							unset($records, $states);
					}

			}
		}

		public function toggle_vendors($records) {

			//assign private variables
				$this->permission_prefix = 'device_vendor_';
				$this->list_page = 'device_vendors.php';
				$this->table = 'device_vendors';
				$this->uuid_prefix = 'device_vendor_';
				$this->toggle_field = 'enabled';
				$this->toggle_values = ['true','false'];

			if (permission_exists($this->permission_prefix.'edit')) {

				//add multi-lingual support
					$language = new text;
					$text = $language->get();

				//validate the token
					$token = new token;
					if (!$token->validate($_SERVER['PHP_SELF'])) {
						message::add($text['message-invalid_token'],'negative');
						header('Location: '.$this->list_page);
						exit;
					}

				//toggle the checked records
					if (is_array($records) && @sizeof($records) != 0) {

						//get current toggle state
							foreach ($records as $x => $record) {
								if (!empty($record['checked']) && $record['checked'] == 'true' && is_uuid($record['uuid'])) {
									$uuids[] = "'".$record['uuid']."'";
								}
							}
							if (is_array($uuids) && @sizeof($uuids) != 0) {
								$sql = "select ".$this->uuid_prefix."uuid as uuid, ".$this->toggle_field." as toggle from v_".$this->table." ";
								$sql .= "where ".$this->uuid_prefix."uuid in (".implode(', ', $uuids).") ";
								$database = new database;
								$rows = $database->select($sql, $parameters ?? null, 'all');
								if (is_array($rows) && @sizeof($rows) != 0) {
									foreach ($rows as $row) {
										$states[$row['uuid']] = $row['toggle'];
									}
								}
								unset($sql, $parameters, $rows, $row);
							}

						//build update array
							$x = 0;
							foreach ($states as $uuid => $state) {
								$array[$this->table][$x][$this->uuid_prefix.'uuid'] = $uuid;
								$array[$this->table][$x][$this->toggle_field] = $state == $this->toggle_values[0] ? $this->toggle_values[1] : $this->toggle_values[0];
								$x++;
							}

						//save the changes
							if (!empty($array) && is_array($array) && @sizeof($array) != 0) {

								//save the array
									$database = new database;
									$database->app_name = $this->app_name;
									$database->app_uuid = $this->app_uuid;
									$database->save($array);
									unset($array);

								//set message
									message::add($text['message-toggle']);
							}
							unset($records, $states);
					}

			}
		}

		public function toggle_vendor_functions($records) {

			//assign private variables
				$this->permission_prefix = 'device_vendor_function_';
				$this->list_page = 'device_vendor_edit.php';
				$this->table = 'device_vendor_functions';
				$this->uuid_prefix = 'device_vendor_function_';
				$this->toggle_field = 'enabled';
				$this->toggle_values = ['true','false'];

			if (permission_exists($this->permission_prefix.'edit')) {

				//add multi-lingual support
					$language = new text;
					$text = $language->get();

				//validate the token
					$token = new token;
					if (!$token->validate('/app/devices/device_vendor_functions.php')) {
						message::add($text['message-invalid_token'],'negative');
						header('Location: '.$this->list_page.'?id='.$this->device_vendor_uuid);
						exit;
					}

				//toggle the checked records
					if (is_array($records) && @sizeof($records) != 0) {

						//get current toggle state
							foreach ($records as $x => $record) {
								if (!empty($record['checked']) && $record['checked'] == 'true' && is_uuid($record['uuid'])) {
									$uuids[] = "'".$record['uuid']."'";
								}
							}
							if (is_array($uuids) && @sizeof($uuids) != 0) {
								$sql = "select ".$this->uuid_prefix."uuid as uuid, ".$this->toggle_field." as toggle from v_".$this->table." ";
								$sql .= "where ".$this->uuid_prefix."uuid in (".implode(', ', $uuids).") ";
								$database = new database;
								$rows = $database->select($sql, $parameters ?? null, 'all');
								if (is_array($rows) && @sizeof($rows) != 0) {
									foreach ($rows as $row) {
										$states[$row['uuid']] = $row['toggle'];
									}
								}
								unset($sql, $parameters, $rows, $row);
							}

						//build update array
							$x = 0;
							foreach ($states as $uuid => $state) {
								$array[$this->table][$x][$this->uuid_prefix.'uuid'] = $uuid;
								$array[$this->table][$x][$this->toggle_field] = $state == $this->toggle_values[0] ? $this->toggle_values[1] : $this->toggle_values[0];
								$x++;
							}

						//save the changes
							if (!empty($array) && is_array($array) && @sizeof($array) != 0) {

								//save the array
									$database = new database;
									$database->app_name = $this->app_name;
									$database->app_uuid = $this->app_uuid;
									$database->save($array);
									unset($array);

								//set message
									message::add($text['message-toggle']);
							}
							unset($records, $states);
					}

			}
		}

		public function toggle_profiles($records) {

			//assign private variables
				$this->permission_prefix = 'device_profile_';
				$this->list_page = 'device_profiles.php';
				$this->table = 'device_profiles';
				$this->uuid_prefix = 'device_profile_';
				$this->toggle_field = 'device_profile_enabled';
				$this->toggle_values = ['true','false'];

			if (permission_exists($this->permission_prefix.'edit')) {

				//add multi-lingual support
					$language = new text;
					$text = $language->get();

				//validate the token
					$token = new token;
					if (!$token->validate($_SERVER['PHP_SELF'])) {
						message::add($text['message-invalid_token'],'negative');
						header('Location: '.$this->list_page);
						exit;
					}

				//toggle the checked records
					if (is_array($records) && @sizeof($records) != 0) {

						//get current toggle state
							foreach ($records as $x => $record) {
								if (!empty($record['checked']) && $record['checked'] == 'true' && is_uuid($record['uuid'])) {
									$uuids[] = "'".$record['uuid']."'";
								}
							}
							if (!empty($uuids) && is_array($uuids) && @sizeof($uuids) != 0) {
								$sql = "select ".$this->uuid_prefix."uuid as uuid, ".$this->toggle_field." as toggle from v_".$this->table." ";
								$sql .= "where ".$this->uuid_prefix."uuid in (".implode(', ', $uuids).") ";
								$database = new database;
								$rows = $database->select($sql, $parameters ?? null, 'all');
								if (is_array($rows) && @sizeof($rows) != 0) {
									foreach ($rows as $row) {
										$states[$row['uuid']] = $row['toggle'];
									}
								}
								unset($sql, $parameters, $rows, $row);
							}

						//build update array
							$x = 0;
							if (!empty($states) && is_array($states) && @sizeof($states) != 0) {
								foreach ($states as $uuid => $state) {
									$array[$this->table][$x][$this->uuid_prefix.'uuid'] = $uuid;
									$array[$this->table][$x][$this->toggle_field] = $state == $this->toggle_values[0] ? $this->toggle_values[1] : $this->toggle_values[0];
									$x++;
								}
							}

						//save the changes
							if (!empty($array) && is_array($array) && @sizeof($array) != 0) {

								//save the array
									$database = new database;
									$database->app_name = $this->app_name;
									$database->app_uuid = $this->app_uuid;
									$database->save($array);
									unset($array);

								//set message
									message::add($text['message-toggle']);
							}
							unset($records, $states);
					}

			}
		}

		/**
		 * copy records
		 */
		public function copy_profiles($records) {

			//assign private variables
				$this->permission_prefix = 'device_profile_';
				$this->list_page = 'device_profiles.php';
				$this->table = 'device_profiles';
				$this->uuid_prefix = 'device_profile_';

			if (permission_exists($this->permission_prefix.'add')) {

				//add multi-lingual support
					$language = new text;
					$text = $language->get();

				//validate the token
					$token = new token;
					if (!$token->validate($_SERVER['PHP_SELF'])) {
						message::add($text['message-invalid_token'],'negative');
						header('Location: '.$this->list_page);
						exit;
					}

				//copy the checked records
					if (is_array($records) && @sizeof($records) != 0) {

						//get checked records
							foreach ($records as $x => $record) {
								if (!empty($record['checked']) && $record['checked'] == 'true' && is_uuid($record['uuid'])) {
									$uuids[] = "'".$record['uuid']."'";
								}
							}

						//create insert array from existing data
							if (!empty($uuids) && is_array($uuids) && @sizeof($uuids) != 0) {
								$sql = "select * from v_".$this->table." ";
								$sql .= "where (domain_uuid = :domain_uuid or domain_uuid is null) ";
								$sql .= "and ".$this->uuid_prefix."uuid in (".implode(', ', $uuids).") ";
								$parameters['domain_uuid'] = $_SESSION['domain_uuid'];
								$database = new database;
								$rows = $database->select($sql, $parameters, 'all');
								if (is_array($rows) && @sizeof($rows) != 0) {
									$y = $z = 0;
									foreach ($rows as $x => $row) {
										$primary_uuid = uuid();

										//copy data
											$array[$this->table][$x] = $row;

										//overwrite
											$array[$this->table][$x][$this->uuid_prefix.'uuid'] = $primary_uuid;
											$array[$this->table][$x]['device_profile_description'] = trim($row['device_profile_description'].' ('.$text['label-copy'].')');

										//keys sub table
											$sql_2 = "select * from v_device_profile_keys ";
											$sql_2 .= "where device_profile_uuid = :device_profile_uuid ";
											$sql_2 .= "order by ";
											$sql_2 .= "case profile_key_category ";
											$sql_2 .= "when 'line' then 1 ";
											$sql_2 .= "when 'memort' then 2 ";
											$sql_2 .= "when 'programmable' then 3 ";
											$sql_2 .= "when 'expansion' then 4 ";
											$sql_2 .= "else 100 end, ";
											$sql_2 .= "profile_key_id asc ";
											$parameters_2['device_profile_uuid'] = $row['device_profile_uuid'];
											$database = new database;
											$rows_2 = $database->select($sql_2, $parameters_2, 'all');
											if (is_array($rows_2) && @sizeof($rows_2) != 0) {
												foreach ($rows_2 as $row_2) {

													//copy data
														$array['device_profile_keys'][$y] = $row_2;

													//overwrite
														$array['device_profile_keys'][$y]['device_profile_key_uuid'] = uuid();
														$array['device_profile_keys'][$y]['device_profile_uuid'] = $primary_uuid;

													//increment
														$y++;

												}
											}
											unset($sql_2, $parameters_2, $rows_2, $row_2);

										//settings sub table
											$sql_3 = "select * from v_device_profile_settings where device_profile_uuid = :device_profile_uuid";
											$parameters_3['device_profile_uuid'] = $row['device_profile_uuid'];
											$database = new database;
											$rows_3 = $database->select($sql_3, $parameters_3, 'all');
											if (is_array($rows_3) && @sizeof($rows_3) != 0) {
												foreach ($rows_3 as $row_3) {

													//copy data
														$array['device_profile_settings'][$z] = $row_3;

													//overwrite
														$array['device_profile_settings'][$z]['device_profile_setting_uuid'] = uuid();
														$array['device_profile_settings'][$z]['device_profile_uuid'] = $primary_uuid;

													//increment
														$z++;

												}
											}
											unset($sql_3, $parameters_3, $rows_3, $row_3);

									}
								}
								unset($sql, $parameters, $rows, $row);
							}

						//save the changes and set the message
							if (!empty($array) && is_array($array) && @sizeof($array) != 0) {

								//grant temporary permissions
									$p = new permissions;
									$p->add('device_profile_key_add', 'temp');
									$p->add('device_profile_setting_add', 'temp');

								//save the array
									$database = new database;
									$database->app_name = $this->app_name;
									$database->app_uuid = $this->app_uuid;
									$database->save($array);
									unset($array);

								//revoke temporary permissions
									$p->delete('device_profile_key_add', 'temp');
									$p->delete('device_profile_setting_add', 'temp');

								//set message
									message::add($text['message-copy']);

							}
							unset($records);
					}

			}

		} //method

		/**
	 * Add ZTP Profile
	 */
	public static function add_ZTP_profile($action,$session_domain_name, $session_domain_uuid,$device_mac_address,$ztp_reference_id="",$old_mac_address="")
	{
		$POLYZTP = parse_ini_file('/var/www/envinfo.ini');
		$response = [];
                $ztp_company_id =  $POLYZTP['POLY_ZTP_COMPANYID'];
                $ztp_user_id =  $POLYZTP['POLY_ZTP_UN'];
                $ztp_password =  $POLYZTP['POLY_ZTP_PW'];
		$API_URL = "https://ztpconsole.polycom.com/inboundservlet/GenericServlet";

		$sql = "select ";
		$sql .= "ztp_profile ";
		$sql .= "from ";
		$sql .= "ztp_profile_setting ";
		$sql .= "where ";
		$sql .= "domain_uuid = :domain_uuid ";
		$parameters['domain_uuid'] = $session_domain_uuid;
		$database = new database;
		$profile = $database->select($sql, $parameters, 'column');
		
		if ($profile == '') {
			$response['status'] = "Error";
			$response['message'] = "Please provide ZTP - Profile.";
			return $response;
		}

		if($action == "add") {

			$subscriber = device::ZTP_subscriber($API_URL, $ztp_user_id, $ztp_password, $ztp_company_id);
		
		if (isset($subscriber['status']['@attributes']['ErrorCode']) && (empty($subscriber['status']['@attributes']['ErrorCode']) || $subscriber['status']['@attributes']['ErrorCode'] != '0000')) {
			//echo "php1";
			$response['status'] = "Error";
			$response['message'] = $subscriber['status']['@attributes']['ErrorMessage'];
			return $response;
		}

		$add_packege = device::ZTP_add_package($API_URL, $ztp_user_id, $ztp_password, $subscriber['status']['@attributes']['account_id']);
		if (isset($add_packege['status']['@attributes']['ErrorCode']) && (empty($add_packege['status']['@attributes']['ErrorCode']) || $add_packege['status']['@attributes']['ErrorCode'] != '0000')) {
			//echo "php2";
			$response['status'] = "Error";
			$response['message'] = $add_packege['status']['@attributes']['ErrorMessage'];
			return $response;
		}

		$add_sip_device = device::ZTP_add_SIP_device($API_URL, $ztp_user_id, $ztp_password, $subscriber['status']['@attributes']['account_id'],$device_mac_address,$profile);
		
		
		if (isset($add_sip_device['status']['@attributes']['ErrorCode']) && (empty($add_sip_device['status']['@attributes']['ErrorCode']) || $add_sip_device['status']['@attributes']['ErrorCode'] != '0000')) {
			//echo "php3";
			$response['status'] = "Error";
			$response['message'] = $add_sip_device['status']['@attributes']['ErrorMessage']."(ZTP Polycom)";
			return $response;
		} else {
			//echo "php4";
			$response['status'] = "Success";
			$response['message'] = "SIP Device add successfully.";
			$response['ztp_reference_id'] = $add_sip_device['response-details']['standard-response']['@attributes']['account-id'];
			
		}



		} else {


			$add_sip_device = device::ZTP_edit_SIP_device($API_URL, $ztp_user_id, $ztp_password, $ztp_reference_id,$device_mac_address,$profile,$old_mac_address);
		
		
		if (isset($add_sip_device['status']['@attributes']['ErrorCode']) && (empty($add_sip_device['status']['@attributes']['ErrorCode']) || $add_sip_device['status']['@attributes']['ErrorCode'] != '0000')) {
			//echo "php3";
			$response['status'] = "Error";
			$response['message'] = $add_sip_device['status']['@attributes']['ErrorMessage']."(ZTP Polycom)";
			return $response;
		} else {
			//echo "php4";
			$response['status'] = "Success";
			$response['message'] = "SIP Device add successfully.";
			$response['ztp_reference_id'] = $add_sip_device['response-details']['standard-response']['@attributes']['account-id'];
			
		}

		}

		
		return $response;
	}

	/**
	 * Start Create subscriber API 
	 */


	public static function ZTP_subscriber($API_URL, $ztp_user_id, $ztp_password, $ztp_company_id)
	{

		// String of all alphanumeric character
		$str_result = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';

		// Shuffle the $str_result and returns substring
		// of specified length
		$account_id = substr(str_shuffle($str_result), 0, 11);
		//$account_id = "20211004jc4";

		$xml_req = '<?xml version="1.0" encoding="UTF-8"?>
				<request xmlns="http://schemas.alopa.com/Inbound" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://schemas.alopa.com/Inbound
				C:\GenericInbound\schema\Generic-inbound-V1.1.xsd" userid="' . $ztp_user_id . '" password="' . $ztp_password . '" message-id="1001" >
				<create-subscriber account-id="' . $account_id . '" isp-name="' . $ztp_company_id . '">
				<PersonalInformation>
				<FirstName></FirstName>
				<LastName></LastName>
				<password></password>
				<address>
				<StreetAddress1></StreetAddress1>
				<StreetAddress2></StreetAddress2>
				<City></City>
				<State>KA</State>
				<Zipcode>560004</Zipcode>
				<Country>India</Country>
				</address>
				<phone>6618004</phone>
				<select-location></select-location>
				<cmmac></cmmac>
				</PersonalInformation>
				</create-subscriber>
				</request>';

		$curl = curl_init();

		curl_setopt_array($curl, array(
			CURLOPT_URL => $API_URL,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => '',
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 0,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => 'POST',
			CURLOPT_POSTFIELDS => $xml_req,
			CURLOPT_HTTPHEADER => array(
				'Content-Type: application/xml'
			),
		));

		$API_response = curl_exec($curl);

		curl_close($curl);

		$xml = simplexml_load_string($API_response);
		$json_string = json_encode($xml);
		$result_array = json_decode($json_string, TRUE);

		if (isset($result_array['status']['@attributes']['ErrorCode']) && !empty($result_array['status']['@attributes']['ErrorCode']) && $result_array['status']['@attributes']['ErrorCode'] == '0000') {
			$result_array['status']['@attributes']['account_id'] = $account_id;
			device::ztp_API_log($xml_req,$API_response);
		} else {
			if (strpos($result_array['status']['@attributes']['ErrorMessage'], "Duplicate entries exists") !== false) {
				device::ztp_API_log($xml_req,$API_response);
				//echo "Word Found!";
				$add_subscribere = device::ZTP_subscriber($ztp_user_id, $ztp_password, $ztp_company_id);
			} else {
				device::ztp_API_log($xml_req,$API_response);
			}
		}
		return $result_array;
	}

	/***
	 * End add packege API
	 */
	public static function ZTP_add_package($API_URL,$ztp_user_id, $ztp_password, $account_id)
	{
		$add_packege_xml_req = '<?xml version="1.0" encoding="UTF-8"?>
						<request xmlns="http://schemas.alopa.com/Inbound" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://schemas.alopa.com/Inbound
						C:\GenericInbound\schema\Generic-inbound-V1.1.xsd"  userid="' . $ztp_user_id . '"  password="' . $ztp_password . '"  message-id="111">
							<add-package account-id="'. $account_id.'">
								<package-data>
									<base-package-name>default</base-package-name>
								</package-data>
							</add-package>
						</request>';


		$curl = curl_init();

		curl_setopt_array($curl, array(
			CURLOPT_URL => $API_URL,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => '',
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 0,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => 'POST',
			CURLOPT_POSTFIELDS =>$add_packege_xml_req,
		   CURLOPT_HTTPHEADER => array(
			'Content-Type: application/xml',
		  ),
		));
		

		$package_API_response = curl_exec($curl);

		curl_close($curl);

		$xml = simplexml_load_string($package_API_response);
		$json_string = json_encode($xml);
		$result_array = json_decode($json_string, TRUE);
		device::ztp_API_log($add_packege_xml_req,$package_API_response);
		return $result_array;
	}

	/***
	 * End add packege API
	 */

	 public static function ZTP_add_SIP_device($API_URL,$ztp_user_id, $ztp_password, $account_id,$device_mac_address,$profile)
	{


		$add_SIP_device_xml_req = '<?xml version="1.0" encoding="UTF-8"?>
<request xmlns="http://schemas.alopa.com/Inbound" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://schemas.alopa.com/Inbound C:\\Users\\visha\\Desktop\\poly\\Generic-inbound-V1.1.xsd" userid="'.$ztp_user_id.'"  password="'.$ztp_password.'" >
        <add-sip-device  account-id="'.$account_id.'">
                <device-params>
                        <deviceId>'.$device_mac_address.'</deviceId>
                        <serialNo>'.$device_mac_address.'</serialNo>
                        <vendor>Polycom</vendor>
                        <vendorModel>Polycom_UCS_Device</vendorModel>
                </device-params>
                <sip-device-common-params>
                        <templateCriteria>'.$profile.'</templateCriteria>
                </sip-device-common-params>
                <package-data>
                        <base-package-name>default</base-package-name>
                </package-data>
        </add-sip-device>
</request>';

		$curl = curl_init();

		curl_setopt_array($curl, array(
			CURLOPT_URL => $API_URL,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => '',
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 0,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => 'POST',
			CURLOPT_POSTFIELDS =>$add_SIP_device_xml_req,
		   CURLOPT_HTTPHEADER => array(
			'Content-Type: application/xml',
		  ),
		));
		

		$package_API_response = curl_exec($curl);

		curl_close($curl);

		$xml = simplexml_load_string($package_API_response);
		$json_string = json_encode($xml);
		$result_array = json_decode($json_string, TRUE);
		device::ztp_API_log($add_SIP_device_xml_req,$package_API_response);
		return $result_array;
	}

	/***
	 * End add SIP Device
	 */



	 /***
	 * End edit device API
	 */	
	public static function ZTP_edit_SIP_device($API_URL,$ztp_user_id, $ztp_password, $account_id,$device_mac_address,$profile,$old_mac_address)
	{
		$edit_SIP_device_xml_req = '<?xml version="1.0" encoding="UTF-8"?>
		<request xmlns="http://schemas.alopa.com/Inbound" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://schemas.alopa.com/Inbound C:\DOCUME~1\Desktop\Generic-inbound-V1.1.xsd" userid="'.$ztp_user_id.'" password="'.$ztp_password.'" >
		<swap-sip-device xmlns="http://schemas.alopa.com/Inbound" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://schemas.alopa.com/Inbound C:\DOCUME~1\Desktop\Generic-inbound-V1.1.xsd" newDeviceId="'.$device_mac_address.'" account-id="'.$account_id.'">
		<device-params>
		<deviceId>'.$old_mac_address.'</deviceId>
		</device-params>
		</swap-sip-device>
		</request>';
		

		$curl = curl_init();

		curl_setopt_array($curl, array(
			CURLOPT_URL => $API_URL,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => '',
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 0,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => 'POST',
			CURLOPT_POSTFIELDS =>$edit_SIP_device_xml_req,
		   CURLOPT_HTTPHEADER => array(
			'Content-Type: application/xml',
		  ),
		));
		

		$edit_device_API_response = curl_exec($curl);

		curl_close($curl);

		$xml = simplexml_load_string($edit_device_API_response);
		$json_string = json_encode($xml);
		$result_array = json_decode($json_string, TRUE);
	
		
		device::ztp_API_log($edit_SIP_device_xml_req,$edit_device_API_response);
		return $result_array;
	}

	/***
	 * End edit SIP Device
	 */


	 /***
	 * End add ztp_API_log
	 */
	public static function ztp_API_log($request,$response)
	{
		$sql = "select user_uuid from v_users where username=:username ";
		$parameters['username'] = 'admin';
		$database = new database;
		$row = $database->select($sql, $parameters, 'row');
		$insert_uuid = $row['user_uuid'];
		unset($sql, $parameters, $rows, $row);
		$sql = "insert into ztp_polycom_api (api_uuid,request,response,insert_date,insert_user) VALUES ('".uuid()."','".$request."','".$response."',now(),'".$insert_uuid."');";
		$database = new database;
		$database->execute($sql);
		unset($sql);
//		$sql = "insert into ztp_polycom_api (api_uuid,request,response) VALUES ('".uuid()."','".$request."','".$response."');";
//		$database = new database;
//		$database->execute($sql);
//		unset($sql);

	}
	 /***
	 * End add ztp_API_log
	 */


	 /**
	 * Delete ZTP Polycom profile
	 */
	public static function delete_ZTP_profile($ztp_reference_id,$device_mac_address)
	{
		$POLYZTP = parse_ini_file('/var/www/envinfo.ini');
		$response = [];
                $ztp_company_id =  $POLYZTP['POLY_ZTP_COMPANYID'];
                $ztp_user_id =  $POLYZTP['POLY_ZTP_UN'];
                $ztp_password =  $POLYZTP['POLY_ZTP_PW'];
		$API_URL = "https://ztpconsole.polycom.com/inboundservlet/GenericServlet";


		$delete_SIP_device_xml_req = '<?xml version="1.0" encoding="UTF-8"?>
		<request xmlns="http://schemas.alopa.com/Inbound" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://schemas.alopa.com/Inbound C:\DOCUME~1\Desktop\Generic-inbound-V1.1.xsd" userid="'.$ztp_user_id.'" password="'.$ztp_password.'">
		<delete-sip-device xmlns="http://schemas.alopa.com/Inbound" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://schemas.alopa.com/Inbound C:\DOCUME~1\Desktop\Generic-inbound-V1.1.xsd" account-id="'.$ztp_reference_id.'">
		<device-params>
		<deviceId>'.$device_mac_address.'</deviceId>
		</device-params>
		</delete-sip-device>
		</request>';		

		$curl = curl_init();

		curl_setopt_array($curl, array(
			CURLOPT_URL => $API_URL,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => '',
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 0,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => 'POST',
			CURLOPT_POSTFIELDS =>$delete_SIP_device_xml_req,
		   CURLOPT_HTTPHEADER => array(
			'Content-Type: application/xml',
		  ),
		));
		

		$delete_device_API_response = curl_exec($curl);

		curl_close($curl);

		$xml = simplexml_load_string($delete_device_API_response);
		$json_string = json_encode($xml);
		$result_array = json_decode($json_string, TRUE);
	
		
		device::ztp_API_log($delete_SIP_device_xml_req,$delete_device_API_response);

		if (isset($result_array['status']['@attributes']['ErrorCode']) && (empty($result_array['status']['@attributes']['ErrorCode']) || $result_array['status']['@attributes']['ErrorCode'] != '0000')) {
			//echo "php1";
			$response['status'] = "Error";
			$response['message'] = $result_array['status']['@attributes']['ErrorMessage'];
			return $response;
		} else {

			$response['status'] = "Success";
			$response['message'] = "Device Deleted successfully";
			return $response;

		}	

	}


	/**
	 * Delete ZTP Polycom profile
	 */
	public static function copy_ZTP_profile($ztp_reference_id,$old_mac_address,$device_mac_address)
	{
		$POLYZTP = parse_ini_file('/var/www/envinfo.ini');
		$response = [];
                $ztp_company_id =  $POLYZTP['POLY_ZTP_COMPANYID'];
                $ztp_user_id =  $POLYZTP['POLY_ZTP_UN'];
                $ztp_password =  $POLYZTP['POLY_ZTP_PW'];
		$API_URL = "https://ztpconsole.polycom.com/inboundservlet/GenericServlet";

//echo $old_mac_address."++++".$device_mac_address;exit;
		$copy_SIP_device_xml_req = '<?xml version="1.0" encoding="UTF-8"?>
		<request xmlns="http://schemas.alopa.com/Inbound" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://schemas.alopa.com/Inbound C:\GenericInbound\schema\Generic-inbound-V1.1.xsd" userid="'.$ztp_user_id.'" password="'.$ztp_password.'" >
		<copy-sip-device old-mac="'.$old_mac_address.'" new-mac="'.$device_mac_address.'"  account-id="'.$ztp_reference_id.'">
		</copy-sip-device>
		</request>';		

		$curl = curl_init();

		curl_setopt_array($curl, array(
			CURLOPT_URL => $API_URL,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => '',
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 0,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => 'POST',
			CURLOPT_POSTFIELDS =>$copy_SIP_device_xml_req,
		   CURLOPT_HTTPHEADER => array(
			'Content-Type: application/xml',
		  ),
		));
		

		$copy_device_API_response = curl_exec($curl);

		curl_close($curl);

		$xml = simplexml_load_string($copy_device_API_response);
		$json_string = json_encode($xml);
		$result_array = json_decode($json_string, TRUE);
	
		device::ztp_API_log($copy_SIP_device_xml_req,$copy_device_API_response);

		if (isset($result_array['status']['@attributes']['ErrorCode']) && (empty($result_array['status']['@attributes']['ErrorCode']) || $result_array['status']['@attributes']['ErrorCode'] != '0000')) {
			//echo "php1";
			$response['status'] = "Error";
			$response['message'] = $result_array['status']['@attributes']['ErrorMessage'];
			return $response;
		} else {

			$response['status'] = "Success";
			$response['message'] = "Device copied successfully";
			$response['ztp_reference_id'] = $result_array['response-details']['standard-response']['@attributes']['account-id'];

			return $response;

		}	

	}

	} //class

?>
