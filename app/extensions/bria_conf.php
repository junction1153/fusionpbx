<?php
$BriaAccess = parse_ini_file('../../../envinfo.ini');

ini_set('max_execution_time', 300); //300 seconds = 5 minutes

$api_url = $BriaAccess['BRIA_API_URL'];
$api_username = $BriaAccess['BRIA_API_UN'];
$api_password = $BriaAccess['BRIA_API_PW'];

//$user_domain = "trial.pbx02.jcnt.net"; //tmp set in conf file after dynamic as user domain.

$add_email_subject = "Bria Enterprise Credentials";
$add_email_body = "Hello, <br/><br/> Thank you for choosing Junction Cloud Connections. Please find your service configuration details below.<br/><br/><b>Username</b>: #USERNAME#@#DOMAIN# (Case-sensitive. Please use lowercase only)<br/><b>Temporary Password</b>: #PASSWORD#<br/><br/><b>NOTE</b>: This password is only valid for 2 hours. To change your password, please login, press Settings --> End User Portal --> Change Password<br/><br/>Please visit our <a href='https://junctioncc.atlassian.net/wiki/spaces/JCCKB/pages/831586379/Bria+Enterprise+App'>Bria Enterprise support page</a> for instructions on how to transfer calls, initiate conference calls, and getting started.<br/><br/><b>Download Links:</b><br/><a href='https://apps.apple.com/us/app/bria-enterprise/id523269027'>Bria Enterprise for iOS</a><br/><a href='https://play.google.com/store/apps/details?id=com.briaccs.voip&hl=en_US'>Bria Enterprise for Android</a><br/><a href='https://www.counterpath.com/enterpriseforwindows'>Bria Enterprise for Windows</a><br/><a href='https://www.counterpath.com/EnterpriseForMac'>Bria Enterprise for Mac OS</a><br/>";

$update_email_subject = "Bria Enterprise Credentials";
$update_email_body = "Hello, <br/><br/> Thank you for choosing Junction Cloud Connections. Please find your service configuration details below.<br/><br/><b>Username</b>: #USERNAME#@#DOMAIN# (Case-sensitive. Please use lowercase only)<br/><b>Temporary Password</b>: #PASSWORD#<br/><br/><b>NOTE</b>: This password is only valid for 2 hours. To change your password, please login, press Settings --> End User Portal --> Change Password<br/><br/>Please visit our <a href='https://junctioncc.atlassian.net/wiki/spaces/JCCKB/pages/831586379/Bria+Enterprise+App'>Bria Enterprise support page</a> for instructions on how to transfer calls, initiate conference calls, and getting started.<br/><br/><b>Download Links:</b><br/><a href='https://apps.apple.com/us/app/bria-enterprise/id523269027'>Bria Enterprise for iOS</a><br/><a href='https://play.google.com/store/apps/details?id=com.briaccs.voip&hl=en_US'>Bria Enterprise for Android</a><br/><a href='https://www.counterpath.com/enterpriseforwindows'>Bria Enterprise for Windows</a><br/><a href='https://www.counterpath.com/EnterpriseForMac'>Bria Enterprise for Mac OS</a><br/>";

//"Hello, <br/><br/> Your Bria User has been updated successfully.<br/><br/> Here is Information about your login detials<br/><br/><b>Username</b> : #USERNAME#<br/><br/><b>Password</b> : #PASSWORD#<br/><br/><b>Domain</b> : #DOMAIN#<br/><br/> Please contact to admin if any query.";

//$delete_email_subject = "Delete User Bria";
//$delete_email_body = "Hello, <br/><br/> Your Bria User <b>#USERNAME#</b> has been delete successfully.<br/><br/> Please contact to admin if any query.";


?>

