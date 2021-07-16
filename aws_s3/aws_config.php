<?php

$AAWS = parse_ini_file('/var/www/envinfo.ini');
$key = $AAWS['S3_KEY'];
$secret = $AAWS['S3_SECRET'];

$bucket = $AAWS['S3_BUCKET'];

require_once 'vendor/autoload.php';
use Aws\S3\S3Client;  
use Aws\Exception\AwsException;

//Create a S3Client 
$s3Client = new S3Client([
//  'profile' => 'default',
  'region' => 'us-east-1',
  'version' => 'latest',
  'credentials' => [
	'key' => $key,
	'secret' => $secret,
  ]
]);

?>
