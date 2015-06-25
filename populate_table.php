<?php
include 'config.php';

$init_xml = simplexml_load_file('users.xml');
$link = mysqli_connect($host,$user,$password,$db) or die("Error " . mysqli_error($link));


for($i=0;$i<count($init_xml);$i++){
  
  $cur_login = $init_xml->user[$i]->login;
  $cur_pass = $init_xml->user[$i]->password;
  $cur_name = $init_xml->user[$i]->login;
  $cur_email = $cur_login."@example.com";
  
$query = "INSERT INTO users (login, password, name, email) VALUES 
('$cur_login', '$cur_pass', '$cur_name', '$cur_email')" or die("Error in the consult.." . mysqli_error($link));
$result = mysqli_query($link, $query);

}

echo 'ok';
mysqli_close($link);
?>
