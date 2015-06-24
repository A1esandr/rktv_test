<!DOCTYPE html>
<html>
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>Rktv</title>
  </head>
<body>
  
<?php 

if(isset($_POST["submit"])) {

$uploaddir = 'files/';
$uploadfile = $uploaddir . basename($_FILES['userfile']['name']);

if (move_uploaded_file($_FILES['userfile']['tmp_name'], $uploadfile)) {
    echo "Файл корректен и был успешно загружен.\n<br>";
 
include 'config.php';
if(file_exists('./files/'.$_FILES['userfile']['name'])){
  
$init_xml = simplexml_load_file('./files/'.$_FILES['userfile']['name']);
  
$link = mysqli_connect($host,$user,$password,$db) or die("Error " . mysqli_error($link));
$query = "SELECT COUNT(*) FROM `users`" or die("Error in the consult.." . mysqli_error($link));
$result = mysqli_query($link, $query);
while($row = mysqli_fetch_array($result)) {
  $all =$row[0];
}

$updated=0;

for($i=0;$i<count($init_xml);$i++){
  
  $cur_login = $init_xml->user[$i]->login;
  $cur_password = $init_xml->user[$i]->password;
  $cur_email = $init_xml->user[$i]->email;
  $cur_name = $init_xml->user[$i]->name;
  

$query = "SELECT COUNT(*) FROM `users` WHERE users.login = '$cur_login'" or die("Error in the consult.." . mysqli_error($link));
$result = mysqli_query($link, $query);
while($row = mysqli_fetch_array($result)) {
  $quant =$row[0];
}
  if($quant==1) { 
  
$query = "UPDATE `users` SET `name`= '$cur_name',`email`= '$cur_email',`updated`= 1 WHERE users.login = '$cur_login'" or die("Error in the consult.." . mysqli_error($link));
$result = mysqli_query($link, $query);
    
  } else {
    
$query = "INSERT INTO users (login, password, name, email, updated) VALUES 
('$cur_login', '$cur_password', '$cur_name', '$cur_email', 1)" or die("Error in the consult.." . mysqli_error($link));
$result = mysqli_query($link, $query); 
    
  }
    
$updated++;
}

$query = "DELETE FROM `users` WHERE users.updated = 0" or die("Error in the consult.." . mysqli_error($link));
$result = mysqli_query($link, $query);
$deleted = mysqli_affected_rows($link);
  
$query = "UPDATE `users` SET `updated`= 0" or die("Error in the consult.." . mysqli_error($link));
$result = mysqli_query($link, $query);

mysqli_close($link);
  $message = "Обработано записей: ".$all."\n<br>";
  $message .= "Обновлено записей: ".$updated."\n<br>";
  $message .= "Удалено записей: ".$deleted."\n<br>";
  mail("ac00untie@gmail.com","Записи",$message);
  echo $message;
}
  
} else {
    echo "Нет загруженного файла!\n";
}
  
}
  ?>
  
  <form method="post" enctype="multipart/form-data" action="/index.php">
    <input type="file" name="userfile" />
    <input type="submit" name="submit" value="Загрузить и обновить базу" />
  </form>

</body>
</html>
