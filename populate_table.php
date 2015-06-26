<?php

require_once ('MysqliDb.php');
require_once ('config.php');

$init_xml = simplexml_load_file('users.xml');

$db = new MysqliDb (DB_SERVER, DB_USER, DB_PASS, DB_NAME);

for($i=0;$i<count($init_xml);$i++){
  
  $cur_login = $init_xml->user[$i]->login;
  $cur_pass = $init_xml->user[$i]->password;
  $cur_name = $init_xml->user[$i]->login;
  $cur_email = $cur_login."@example.com";

  $data = Array ("login" => "$cur_login",
                 "password" => "$cur_pass",
                 "name" => "$cur_name",
                 "email" => "$cur_email "
  );
  $id = $db->insert ('users', $data);
  if($id)
    echo 'user was created. Id=' . $id.'<br>';

}

echo 'ok';

?>
