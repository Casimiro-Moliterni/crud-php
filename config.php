<?php

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "test";

// protocol url 
define('PROTOCOL_URL','http://');
// base path 
define('BASE_PATH','/crud-php');
// url base 
define('URL_ROOT',PROTOCOL_URL.$_SERVER['DOCUMENT_ROOT'].BASE_PATH);
// path base 
define('PATH_ROOT',$_SERVER['DOCUMENT_ROOT'].BASE_PATH);

// crea connessione 
$conn = new mysqli($servername,$username,$password,$dbname);
// check connessione 
if($conn->connect_error){
    die('Connection failed'. $conn->connect_error);
}


?>