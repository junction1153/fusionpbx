<?php
$dir_path = '/var/www/fusionpbx/aws_s3/tmp/';
delTree($dir_path);
function delTree($dir){
$files = array_diff(scandir($dir), array('.', '..')); 
        foreach ($files as $file) { 
            (is_dir("$dir/$file")) ? delTree("$dir/$file") : unlink("$dir/$file"); 
        }
}
exit;
?>
