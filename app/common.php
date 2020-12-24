<?php



function p($var){
    echo '<pre>';
    var_dump($var);
    echo '</pre>';
}

function envStrToArray($name ,$defualt = '',$delimiter = ','){
    return explode(',',env($name,$defualt));
}




