<?php
/**
 * Copyright 2010-2019 Amazon.com, Inc. or its affiliates. All Rights Reserved.
 *
 * This file is licensed under the Apache License, Version 2.0 (the "License").
 * You may not use this file except in compliance with the License. A copy of
 * the License is located at
 *
 * http://aws.amazon.com/apache2.0/
 *
 * This file is distributed on an "AS IS" BASIS, WITHOUT WARRANTIES OR
 * CONDITIONS OF ANY KIND, either express or implied. See the License for the
 * specific language governing permissions and limitations under the License.
 *
 * ABOUT THIS PHP SAMPLE: This sample is part of the SDK for PHP Developer Guide topic at
 * https://docs.aws.amazon.com/sdk-for-php/v3/developer-guide/s3-examples-creating-buckets.html
 *
 */

require 'vendor/autoload.php';

use Aws\S3\S3Client;  
use Aws\Exception\AwsException;

/**
 * List your Amazon S3 buckets.
 *
 * This code expects that you have AWS credentials set up per:
 * https://docs.aws.amazon.com/sdk-for-php/v3/developer-guide/guide_credentials.html
 */
$AAWS = parse_ini_file('/var/www/envinfo.ini');

$key = $AAWS['S3_KEY'];
$secret = $AAWS['S3_SECRET'];

//Create a S3Client 
$s3Client = new S3Client([
  'region' => 'us-east-1',
  'version' => 'latest',
  'credentials' => [
        'key' => $key,
        'secret' => $secret,
  ]
]);
$bucket = $AAWS['S3_BUCKET'];

$file_name= "harsh/test.php";
$save_path ='/var/www/fusionpbx/aws_s3/tmp/harsh.php';
//Listing all S3 Bucket
$result = $s3Client->getObject(array(
        'Bucket' => $bucket,
        'Key' => $file_name,
        'SaveAs' => $save_path
    ));
if(!empty($result)){
echo "File There";
}else{
echo "No File There";

}
//print_r($result); 
 echo "!1";exit;

