<?php

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "mydb";

$db = mysqli_connect($servername, $username, $password, $dbname);

if (!$db) {
    die("connection error" . mysqli_connect_error());
}

?>