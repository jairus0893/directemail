<?php
function rand_colorCode(){
$r = dechex(mt_rand(0,255)); // generate the red component
$g = dechex(mt_rand(0,255)); // generate the green component
$b = dechex(mt_rand(0,255)); // generate the blue component
$rgb = $r.$g.$b;
if($r == $g && $g == $b){
$rgb = substr($rgb,0,3); // shorter version
}
return $rgb;
}
?>