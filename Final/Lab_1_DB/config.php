<?php 
$host="localhost";
$user ="root";
$pass ="";
$dbname="Lab_1";
$conn = new mysqli ($host, $user, $pass, $dbname);
if ($conn-> connect_error)
    {
        die ("conection failed : ".$conn->connect_error);
    }

?>