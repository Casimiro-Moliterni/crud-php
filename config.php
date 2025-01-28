<?php

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "test";

// crea connessione 
$conn = new mysqli($servername,$username,$password,$dbname);
// check connessione 
if($conn->connect_error){
    die('Connection failed'. $conn->connect_error);
}
$sqlDropUsers = "DROP TABLE IF EXISTS Users";

if($conn->query($sqlDropUsers) === TRUE){

    echo "tabella eliminata con successo" . $sqlDropUsers ;

} else {
    echo "error:" . $sqlDropUsers . "<br>" . $conn->error;
}

$sqlCreateUsers = "CREATE TABLE Users (

id INT AUTO_INCREMENT PRIMARY KEY,
name varchar(150),
email varchar(250) UNIQUE,
password varchar(20)

)";
if($conn->query($sqlCreateUsers) === TRUE){

    echo "new table create" . $sqlCreateUsers ;

} else {
    echo "error:" . $sqlCreateUsers . "<br>" . $conn->error;
}
$conn->close();

?>