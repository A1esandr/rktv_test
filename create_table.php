<?php
include 'config.php';

$init_xml = simplexml_load_file('users.xml');
$link = mysqli_connect($host,$user,$password,$db) or die("Error " . mysqli_error($link));


for($i=0;$i<count($init_xml);$i++){
  
  $cur_login = $init_xml->user[$i]->login;
  $cur_pass = $init_xml->user[$i]->password;
  $cur_email = $cur_login."@example.com";

$query = "CREATE TABLE
    `users` (
        `id` INT(11) NOT NULL AUTO_INCREMENT,
        `login` CHAR(50) NOT NULL,
        `password` CHAR(150) NOT NULL,
        `name` CHAR(30) NOT NULL,
        `email` CHAR(50) NOT NULL,
        `updated` INT(2) NOT NULL,
        PRIMARY KEY(`id`)
    )" or die("Error in the consult.." . mysqli_error($link));
  
  $result = mysqli_query($link, $query);

}

echo 'ok';
mysqli_close($link);
?>
