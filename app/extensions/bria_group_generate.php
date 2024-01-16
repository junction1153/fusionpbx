
<?php
	include_once "bria_conf.php";

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
			$group_name = $value['@attributes']['groupName'];
		}else{
			$group_name = $value['@attributes']['groupName'];
		}
		$group_array[$group_name] = $group_name;
	}
	$text = array("date"=>strtotime(gmdate('Y-m-d')),"group_array"=>$group_array);
	$var_str = var_export($text, true);
	$var = "<?php\n\n\$group_content = $var_str;\n\n?>";
	file_put_contents('bria_group_content.php', $var);
	echo "success";exit;
