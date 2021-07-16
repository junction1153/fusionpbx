<?php
$records_get_day = 1;
require_once 'aws_config.php';
$start_date = gmdate('Y-m-d 00:00:01',strtotime('-'.$records_get_day.' days'));
$end_date = gmdate('Y-m-d 23:59:59',strtotime('-'.$records_get_day.' days'));
$start_time = strtotime($start_date);
$end_time = strtotime($end_date);

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require "/etc/fusionpbx/config.php";
$db = pg_connect( "host = ".$db_host." port = ".$db_port." dbname = ".$db_name." user = ".$db_username." password=".$db_password.""  );
if(!$db) {
	echo "Error : Unable to open database\n";
} 
$v_xml_cdr_result = pg_query($db,"select start_epoch,record_name,record_path,xml_cdr_uuid,domain_name from v_xml_cdr where record_name != '' and start_epoch > '".$start_time."' and start_epoch < '".$end_time."'");
//$v_xml_cdr_result = pg_query($db,"select start_epoch,record_name,record_path,xml_cdr_uuid,domain_name from v_xml_cdr where record_name != '' and start_epoch < '".$start_time."' ");
$v_xml_cdr_row=pg_fetch_all($v_xml_cdr_result);
echo "Total Count:::".count($v_xml_cdr_row)."::::::\n";
echo $i =$j = 0;
if(!empty($v_xml_cdr_row)){
	foreach($v_xml_cdr_row as $key=> $value){
echo "J:::::".$j."::::::::\n";
$j++;
		$path_explode = explode($value['domain_name'],$value['record_path']);
		$archive_path = isset($path_explode[1])?$path_explode[1]:"";
		$record_file = $value['record_path'].'/'.$value['record_name'];

		$file_ext = pathinfo($value['record_name'], PATHINFO_EXTENSION);
		if (file_exists($record_file) && $file_ext =='wav') {
$i++;
echo $i.":::".$value['xml_cdr_uuid'].":::::\n";
			$wav_file_ext = basename($value['record_name'], ".wav");
			$ogg_file_path = $value['record_path']."/".$wav_file_ext.".ogg";
			$cmd = "ffmpeg -y -i ".$record_file." ".$ogg_file_path;
			exec($cmd);
			pg_query($db,"UPDATE v_xml_cdr SET record_name = '".$wav_file_ext.".ogg' WHERE record_name = '".$value['record_name']."' and record_path = '".$value['record_path']."'");
			$value['record_name'] = $wav_file_ext.".ogg";
			$file_ext = pathinfo($value['record_name'], PATHINFO_EXTENSION);
			$file_name= $value['domain_name'].'/'.$value['record_name'];
			$file_Path = $ogg_file_path;
			$keys = basename($file_Path);
			try {
			    //Create a S3Client
			    $result = $s3Client->putObject([
				'Bucket' => $bucket,
				'Key' => $value['domain_name'].$archive_path.'/'.$keys,
				'SourceFile' => $file_Path,
			    ]);
			unlink($record_file);
			unlink($ogg_file_path);
			} catch (S3Exception $e) {
			    echo $e->getMessage() . "\n";
			}
		}elseif(file_exists($record_file) && $file_ext =='ogg'){
$i++;
echo $i.":ogg::".$value['xml_cdr_uuid'].":::::\n";
			$ogg_file_path = $record_file;
			$file_Path = $ogg_file_path;
			$keys = basename($file_Path);
			try {
			    //Create a S3Client
			    $result = $s3Client->putObject([
				'Bucket' => $bucket,
				'Key' => $value['domain_name'].$archive_path.'/'.$keys,
				'SourceFile' => $file_Path,
			    ]);
			unlink($ogg_file_path);
			} catch (S3Exception $e) {
			    echo $e->getMessage() . "\n";
			}

		}
	}
}
echo "COMPLETE EXIT:";
exit;
?>
