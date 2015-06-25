<?php
include 'config.php';

$link = mysqli_connect($host,$user,$password,$db) or die("Error " . mysqli_error($link));

$query = "CREATE TABLE
    `users` (
        `login` CHAR(50) NOT NULL,
        `password` CHAR(150) NOT NULL,
        `name` CHAR(30) NOT NULL,
        `email` CHAR(50) NOT NULL,
        PRIMARY KEY(`login`)
    )" or die("Error in the consult.." . mysqli_error($link));
  
$result = mysqli_query($link, $query);

echo 'ok';
mysqli_close($link);
?>
