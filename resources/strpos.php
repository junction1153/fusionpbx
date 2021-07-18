<?php

function strpos_arr($haystack, $arr_needle){
    $valid = false;
    foreach($arr_needle as $key => $needle)
    {
        if(stripos($haystack, $needle) !== false)
        {
            $valid = $key;
            break;
        }
    }
    return $valid;
}
?>
