<?php

    while (!mysql_ping($dblink)) 
    {
        mysql_close($dblink);

        echo "\n\n" . date("Y-m-d h:i:s") . " ((( LOST DB CONNECTION! ))) \n\n";
        include "dbconfig.php";
    }    
?>