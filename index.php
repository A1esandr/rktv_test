<!DOCTYPE html>
<html>
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>Rktv</title>
  </head>
<body>
  
<?php 
/* Производим все действия с таблицей только при наличии отправки с формы */
if(isset($_POST["submit"])) {

/* Выгрузка файла с компьютера пользователя */
  $uploaddir = 'files/';
  $uploadfile = $uploaddir . basename($_FILES['userfile']['name']);
  
  if (move_uploaded_file($_FILES['userfile']['tmp_name'], $uploadfile)) {
    echo "Файл корректен и был успешно загружен.\n<br>";
    
 /* Если файл успешно выгружен производим действия с таблицей */  
    include 'config.php';
    if(file_exists('./files/'.$_FILES['userfile']['name'])){
      
    /* Создаем массив для обновленных либо вновь созданных логинов */  
    $present_logins = array();
    
  /* Определяем тип выгруженного файла по трем последним символам в названии */    
      $file_type = substr($_FILES['userfile']['name'], -3,3);
      
      if($file_type=="xml"){
        $init_xml = simplexml_load_file('./files/'.$_FILES['userfile']['name']);
          
        $link = mysqli_connect($host,$user,$password,$db) or die("Error " . mysqli_error($link));
        
        for($i=0;$i<count($init_xml);$i++){
          
          $cur_login = $init_xml->user[$i]->login;
          $cur_password = $init_xml->user[$i]->password;
          $cur_email = $init_xml->user[$i]->email;
          $cur_name = $init_xml->user[$i]->name;
            
        /* Проверяем наличие пользователя с таким логином в таблице */  
          $query = "SELECT COUNT(*) FROM `users` WHERE users.login = '$cur_login'" or die("Error in the consult.." . mysqli_error($link));
          $result = mysqli_query($link, $query);
          while($row = mysqli_fetch_array($result)) {
            $quant = $row[0];
          }
          
          if($quant==1) {
  
            $query = "SELECT COUNT * FROM `users` WHERE users.login = '$cur_login'" or die("Error in the consult.." . mysqli_error($link));
            $result = mysqli_query($link, $query);
            while($row = mysqli_fetch_array($result)) {
              
              /* Проверяем наличие изменений в записи */
              $updated_now = 0;
              if($row['name'] != $cur_name){
                $query = "UPDATE `users` SET `name`= '$cur_name' WHERE users.login = '$cur_login'" or die("Error in the consult.." . mysqli_error($link));
                $result = mysqli_query($link, $query);
                $updated_now = 1;
                array_push($present_logins,$cur_login); 
              } else if($row['email'] != $cur_email){
                $query = "UPDATE `users` SET `email`= '$cur_email' WHERE users.login = '$cur_login'" or die("Error in the consult.." . mysqli_error($link));
                $result = mysqli_query($link, $query);
                if($updated_now == 0){
                array_push($present_logins,$cur_login);
                }
              }
            }

          } else {
        /* Если нет - создаем такого пользователя */      
            $query = "INSERT INTO users (login, password, name, email) VALUES 
            ('$cur_login', '$cur_password', '$cur_name', '$cur_email')" or die("Error in the consult.." . mysqli_error($link));
            $result = mysqli_query($link, $query); 
            array_push($present_logins,$cur_login);  
          }
              
        }
      } else if($file_type=="csv") {
   /* Если файл является csv */     
        $delim = ";";
        $n = 200000;
        
        if(($handle = fopen("./files/".$_FILES['userfile']['name'],"r"))!==FALSE){
          
          $link = mysqli_connect($host,$user,$password,$db) or die("Error " . mysqli_error($link));
  
          while(($csv = fgetcsv($handle,$n,$delim))!==FALSE){
  
  /* Предполагаем что запись в каждой строке csv файла идет в порядке login;password;name;email; */
            $cur_login = $csv[0];
            $cur_password = $csv[1];
            $cur_name = $csv[2];
            $cur_email = $csv[3];
              
            $query = "SELECT COUNT(*) FROM `users` WHERE users.login = '$cur_login'" or die("Error in the consult.." . mysqli_error($link));
            $result = mysqli_query($link, $query);
            while($row = mysqli_fetch_array($result)) {
              $quant = $row[0];
            }
            if($quant==1) { 
              $query = "SELECT COUNT * FROM `users` WHERE users.login = '$cur_login'" or die("Error in the consult.." . mysqli_error($link));
              $result = mysqli_query($link, $query);
              while($row = mysqli_fetch_array($result)) {
                $updated_now = 0;
                if($row['name'] != $cur_name){
                  $query = "UPDATE `users` SET `name`= '$cur_name' WHERE users.login = '$cur_login'" or die("Error in the consult.." . mysqli_error($link));
                  $result = mysqli_query($link, $query);
                  $updated_now = 1;
                  array_push($present_logins,$cur_login); 
                } else if($row['email'] != $cur_email){
                  $query = "UPDATE `users` SET `email`= '$cur_email' WHERE users.login = '$cur_login'" or die("Error in the consult.." . mysqli_error($link));
                  $result = mysqli_query($link, $query);
                  if($updated_now == 0){
                  array_push($present_logins,$cur_login);
                  }
                }
              }
  
            } else {
          /* Если нет - создаем такого пользователя */      
              $query = "INSERT INTO users (login, password, name, email) VALUES 
              ('$cur_login', '$cur_password', '$cur_name', '$cur_email')" or die("Error in the consult.." . mysqli_error($link));
              $result = mysqli_query($link, $query); 
              
              array_push($present_logins,$cur_login);  
            }
          }
        fclose($handle);
        }
      } else {
        die("Загруженный файл не является ни xml, ни csv файлом");
      }
      
      /*  Удаляем пользователей, которых нет в массиве обновленных */
      $deleted = 0;
      $query = "SELECT * FROM `users`" or die("Error in the consult.." . mysqli_error($link));
      $result = mysqli_query($link, $query);
      while($row = mysqli_fetch_array($result)) {
        $now_login = $row['login'];
        if(array_search($now_login,$updated_logins) == FALSE){
          $query = "DELETE * FROM `users` WHERE users.login = '$now_login'" or die("Error in the consult.." . mysqli_error($link));
          $result = mysqli_query($link, $query);
          $deleted++;
        }
      }
      
      mysqli_close($link);
      
      /* Общее количество обработанных записей */
      $updated = count($updated_logins);
      $all = $updated + $deleted;
      
      $message = "Обработано записей: ".$all."\n<br>";
      $message .= "Обновлено записей: ".$updated."\n<br>";
      $message .= "Удалено записей: ".$deleted."\n<br>";
      
      /* Отправка отчета на почту */
      mail("example@example.com","Записи",$message);
      
      /* Вывод отчета на экран */
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
