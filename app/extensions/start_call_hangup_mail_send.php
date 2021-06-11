<?php
$from_email = 'shapesupport@shape123.net';
date_default_timezone_set('America/New_York');
//$from_email    = 'patelharsh371@gmail.com';
$email_subject = 'Call Hangup';
if (isset($argv[4]) && $argv[4] != "2") {
    $email_body = 'Extension: #CALLERID_name# <br/> Phone number: #CALLERID_number# <br/> Call Duration: #CALLTIME# <br/> Date/time : #DATE#';
} else {
    $email_body = 'Caller ID Name: #CALLERID_name# <br/> Caller ID Number: #CALLERID_number# <br/> Extension: #Extension# <br/> Call Duration: #CALLTIME# <br/> Date/time : #DATE#';
}
if (isset($argv[1]) && $argv[1] != "") {
    $argv[1]    = str_replace('--', ' ', $argv[1]);
    $time       = gmdate("H:i:s", $argv[3]);
    $email_body = str_replace('#CALLERID_name#', $argv[1], $email_body);
    $email_body = str_replace('#CALLERID_number#', $argv[2], $email_body);
    $email_body = str_replace('#Extension#', $argv[5], $email_body);
    $email_body = str_replace('#DATE#', date('Y-m-d H:i:s'), $email_body);
    $email_body = str_replace('#CALLTIME#', $time, $email_body);
    require_once "root.php";
    require_once "resources/require.php";
    include_once "resources/phpmailer/class.phpmailer.php";
    include_once "resources/phpmailer/class.smtp.php";
    $sql               = "select default_setting_subcategory,default_setting_value from v_default_settings where default_setting_category='email'";
    //    $sql .= "where domain_uuid='".$_POST['domain_uuid']."'";
    $database          = new database;
    $parameters        = array();
    $bria_setting_rows = $database->select($sql, $parameters, 'all');
    $smtp              = array();
    if (!empty($bria_setting_rows)) {
        foreach ($bria_setting_rows as $key => $value) {
            $smtp[$value['default_setting_subcategory']] = $value['default_setting_value'];
        }
    }
    $mail = new PHPMailer;
    $mail->isSMTP();
    $mail->Host       = $smtp['smtp_host'];
    $mail->SMTPAuth   = $smtp['smtp_auth'];
    $mail->Username   = $smtp['smtp_username'];
    $mail->Password   = $smtp['smtp_password'];
    $mail->SMTPSecure = $smtp['smtp_secure'];
    $mail->Port       = "";
    $mail->From       = $smtp['smtp_from'];
    $mail->FromName   = $smtp['smtp_from_name'];
    //$mail->addAddress('patelharsh371@gmail.com', '');
    $mail->addAddress($from_email, '');
    $mail->addReplyTo($smtp['smtp_from'], $smtp['smtp_from_name']);
    $mail->WordWrap = 50;
    $mail->isHTML(true);
    $mail->Subject = $email_subject;
    $mail->Body    = $email_body;
    $mail->send();
    echo 1;
    exit;
} else {
    echo 0;
    exit;
}
?>

