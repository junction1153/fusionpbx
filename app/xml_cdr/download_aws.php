<?php
//HP:CHANGES_START_AWS
//require_once 'aws_s3/aws_config.php';
//HP:CHANGES_END_AWS
if(isset($_GET['file_name']) && isset($_GET['domain_name']) && $_GET['file_name'] != '' && $_GET['domain_name'] != ''){
	if($_GET['t'] == 'local'){
		ob_clean();
		$file_tmp_path = '/var/www/fusionpbx/aws_s3/tmp/';
		$domain_name= $_GET['domain_name'];
		$file_name= $_GET['file_name'];
		$file_check_bucket = $domain_name."/".$file_name;
		$record_name = $file_check_bucket;
		$fd = fopen($file_tmp_path.$file_check_bucket, "rb");
		$file_ext = pathinfo($file_name, PATHINFO_EXTENSION);
		switch ($file_ext) {
			case "wav" : header("Content-Type: audio/x-wav"); break;
			case "mp3" : header("Content-Type: audio/mpeg"); break;
			case "ogg" : header("Content-Type: audio/ogg"); break;
		}
		$file_name = preg_replace('#[^a-zA-Z0-9_\-\.]#', '', $file_name);
		header('Content-Disposition: attachment; filename="'.$file_name.'"');
		header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
		header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past
		ob_clean();
		fpassthru($fd);
	}else{
		$path_explode = explode($_GET['domain_name'],$_GET['record_path']);
		$archive_path = isset($path_explode[1])?$path_explode[1]:"";
		$domain_name= $_GET['domain_name'];
		$file_name= $_GET['file_name'];
		$cmd_output = exec('/usr/bin/php /var/www/fusionpbx/aws_s3/aws_check_and_get.php '.$domain_name.' '.$file_name.' 2 '.$archive_path);
		if($cmd_output != 'Not-exist'){
			ob_clean();
			$file_tmp_path = '/var/www/fusionpbx/aws_s3/tmp/';
			$file_check_bucket = $domain_name."/".$file_name;
			$record_name = $file_check_bucket;
			$fd = fopen($file_tmp_path.$file_check_bucket, "rb");
			$file_ext = pathinfo($file_name, PATHINFO_EXTENSION);
			switch ($file_ext) {
				case "wav" : header("Content-Type: audio/x-wav"); break;
				case "mp3" : header("Content-Type: audio/mpeg"); break;
				case "ogg" : header("Content-Type: audio/ogg"); break;
			}
			$record_name = preg_replace('#[^a-zA-Z0-9_\-\.]#', '', $record_name);
			header('Content-Disposition: attachment; filename="'.$record_name.'"');
			header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
			header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past
			ob_clean();
			fpassthru($fd);
			if(isset($_GET['flag']) && $_GET['flag'] == 'ajax'){
				echo $cmd_output;exit;
			}
		}else{	
			if(isset($_GET['flag']) && $_GET['flag'] == 'ajax'){
				echo $cmd_output;exit;
			}
			include_once "root.php";
			require_once "resources/require.php";

			message::add("No Recording File Available in Bucket.",'negative');
			header('Location: xml_cdr.php');
			exit;
		}		
	}
}else{
			if(isset($_GET['flag']) && $_GET['flag'] == 'ajax'){
				echo $cmd_output;exit;
			}
			include_once "root.php";
			require_once "resources/require.php";

			message::add("No Recording File Available in Bucket.",'negative');
			header('Location: xml_cdr.php');
			exit;


}
?>
