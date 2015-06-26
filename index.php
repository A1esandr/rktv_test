<?php 
require_once('MysqliDb.php');
require_once('config.php');
error_reporting(E_ALL);

$data = array();

function mainFunction () {
    global $db;

/* Производим все действия с таблицей только при наличии отправки с формы BEGIN */
if(isset($_POST["submit"])) {
  
  
  
  

/* Выгрузка файла с компьютера пользователя */
  $uploaddir = 'files/';
  $uploadfile = $uploaddir . basename($_FILES['userfile']['name']);
  
  
  
  /* Если файл успешно выгружен производим действия с таблицей BEGIN */ 
  if (move_uploaded_file($_FILES['userfile']['tmp_name'], $uploadfile)) {
    echo "Файл корректен и был успешно загружен.\n<br>";
    
  
    
    
    /* Проверка на наличие выгруженнго файла BEGIN */
    if(file_exists('./files/'.$_FILES['userfile']['name'])){
      
    /* Создаем массив для существующих, но не обновленных логинов */  
    $present_logins = array();
    /* Создаем массив для существующих и обновленных либо вновь созданных логинов */
    $updated_logins = array();
    /* Создаем массив для впервые созданных логинов */
    $created_logins = array();
    
  /* Определяем тип выгруженного файла по трем последним символам в названии */    
      $file_type = substr($_FILES['userfile']['name'], -3,3);
      
      
      try {
        
        
      if($file_type=="xml"){
        
        $init_xml = simplexml_load_file('./files/'.$_FILES['userfile']['name']);
          
        for($i=0;$i<count($init_xml);$i++){
          
          $cur_login = $init_xml->user[$i]->login;
          $cur_password = $init_xml->user[$i]->password;
          $cur_email = $init_xml->user[$i]->email;
          $cur_name = $init_xml->user[$i]->name;
         
         /* Проверяем наличие пользователя с таким логином в таблице */
         $db->where ('login', $cur_login); 
         $user = $db->get('users');
         if ($db->count > 0) {
  
              /* Проверяем наличие изменений в записи */
              $updated_now = 0;
              if((!empty($cur_name))&&($user['name'] != $cur_name)){
                $data = Array (
                    'name' => $cur_name
                );
                $db->where ('login', $cur_login);
                $db->update ('users', $data);
                
                $updated_now = 1;
                array_push($updated_logins,$cur_login); 
              } else if((!empty($cur_email))&&($row['email'] != $cur_email)){
                
                $data = Array (
                    'email' => $cur_email
                );
                $db->where ('login', $cur_login);
                $db->update ('users', $data);
                
                if($updated_now == 0){
                  array_push($updated_logins,$cur_login);
                }
              }
              array_push($present_logins,$cur_login);
            

          } else {
        /* Если нет - создаем такого пользователя */
            $data = Array ("login" => $cur_login,
                           "password" => $cur_pass,
                           "name" => $cur_name,
                           "email" => $cur_email
            );
            $db->insert ('users', $data);
            
            array_push($created_logins,$cur_login);  
          }
              
        }
      } else if($file_type=="csv") {
   /* Если файл является csv-файлом */     
        $delim = ";";
        $n = 200000;
        
        if(($handle = fopen("./files/".$_FILES['userfile']['name'],"r"))!==FALSE){
          
          while(($csv = fgetcsv($handle,$n,$delim))!==FALSE){
  
  /* Предполагаем что запись в каждой строке csv файла идет в порядке login;password;name;email; */
            $cur_login = $csv[0];
            $cur_password = $csv[1];
            $cur_name = $csv[2];
            $cur_email = $csv[3];
              
            /* Проверяем наличие пользователя с таким логином в таблице */
             $db->where ('login', $cur_login); 
             $user = $db->get('users');
             if ($db->count > 0) {
      
                  /* Проверяем наличие изменений в записи */
                  $updated_now = 0;
                  if((!empty($cur_name))&&($user['name'] != $cur_name)){
                    $data = Array (
                        'name' => $cur_name
                    );
                    $db->where ('login', $cur_login);
                    $db->update ('users', $data);
                    
                    $updated_now = 1;
                    array_push($updated_logins,$cur_login); 
                  } else if((!empty($cur_email))&&($row['email'] != $cur_email)){
                    
                    $data = Array (
                        'email' => $cur_email
                    );
                    $db->where ('login', $cur_login);
                    $db->update ('users', $data);
                    
                    if($updated_now == 0){
                      array_push($updated_logins,$cur_login);
                    }
                  }
                  array_push($present_logins,$cur_login);
                
    
              } else {
            /* Если нет - создаем такого пользователя */
                $data = Array ("login" => $cur_login,
                               "password" => $cur_pass,
                               "name" => $cur_name,
                               "email" => $cur_email
                );
                $db->insert ('users', $data);
                
                array_push($created_logins,$cur_login);  
              }
          
          }
        fclose($handle);
        }
        
      } else {
        die("Загруженный файл не является ни xml, ни csv файлом");
      }
       
        
        
        
      } catch (Exception $e) {
          echo 'Поймано исключение: ',  $e->getMessage(), "\n";
      } finally {
        
        
        
      /*  Удаляем пользователей, которых нет в массиве обновленных */
      $deleted = 0;
      $now_login = FALSE;
      
      $users = $db->get('users');
      if ($db->count > 0)
        foreach ($users as $user) { 
          
            $now_login = $user['login'];
            if(
                (array_search($now_login,$present_logins) == FALSE)&&
                (array_search($now_login,$updated_logins) == FALSE)&&
                (array_search($now_login,$created_logins) == FALSE)
            ){
              
              $db->where('login', $now_login);
              if($db->delete('users')) $deleted++;
              
            }
        }
      
      

      /* Общее количество обработанных записей */
      $updated = count($updated_logins);
      $created = count($created_logins);
      $all = $updated + $created + $deleted;
      
      $message = "Обработано записей: ".$all."\n<br>";
      $message .= "Обновлено записей: ".$updated."\n<br>";
      $message .= "Создано записей: ".$created."\n<br>";
      $message .= "Удалено записей: ".$deleted."\n<br>";
      
      /* Отправка отчета на почту */
      mail("example@example.com","Записи",$message);
      
      /* Вывод отчета на экран */
      echo $message;
        
        
        
      }
        
        
    }
  /* Проверка на наличие выгруженнго файла END */
      
      
      
      
  } else {
      echo "Нет загруженного файла!\n";
  }
 /* Если файл успешно выгружен производим действия с таблицей END */ 
    
    
    
    
    
}
/* Производим все действия с таблицей только при наличии отправки с формы END */  

    
}

$db = new MysqliDb (DB_SERVER, DB_USER, DB_PASS, DB_NAME);

?>
<!DOCTYPE html>
<html>
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>Rktv</title>
  </head>
<body>
  
  <?php mainFunction();?>
  
  <form method="post" enctype="multipart/form-data" action="/index.php">
    <input type="file" name="userfile" />
    <input type="submit" name="submit" value="Загрузить и обновить базу" />
  </form>

</body>
</html>
