<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');
require_once 'aws_config.php';
$is_exist = 'Not-exist';
//$argv[3]::::1-check_exist 2-download.
if(isset($argv) && isset($argv[1]) && isset($argv[2]) && isset($argv[3])){


	$file_tmp_path = '/var/www/fusionpbx/aws_s3/tmp/';
	$dirname = $argv[1];
	$filename = $file_tmp_path."/".$dirname;

	if (!file_exists($filename)) {
	    mkdir( $file_tmp_path."/".$dirname, 0777);
	}

	$file_check_bucket = $argv[1]."/".$argv[2];
	$info = $s3Client->doesObjectExist($bucket, $file_check_bucket);

	if ($info){
		$is_exist = 'Exist';
	}else{
		if(isset($argv[4]) && $argv[4] != ''){
			$file_check_bucket = $argv[1].$argv[4]."/".$argv[2];
			$info = $s3Client->doesObjectExist($bucket, $file_check_bucket);
			if ($info){
				$is_exist = 'Exist';
			}
		}
	}
	if($argv[3] ==2 && $is_exist == 'Exist'){
		$file_name = $file_check_bucket;
		$save_path =$file_tmp_path.$argv[1]."/".$argv[2];
		//Listing all S3 Bucket
		$result = $s3Client->getObject(array(
			'Bucket' => $bucket,
			'Key' => $file_name,
			'SaveAs' => $save_path
		    ));
	}
}
print_r($is_exist);
exit;

?>
