<?php
require_once ('MysqliDb.php');
require_once ('config.php');

$db = new MysqliDb (DB_SERVER, DB_USER, DB_PASS, DB_NAME);

$users = $db->rawQuery(
"CREATE TABLE
    `users` (
        `login` CHAR(50) NOT NULL,
        `password` CHAR(150) NOT NULL,
        `name` CHAR(30) NOT NULL,
        `email` CHAR(50) NOT NULL,
        PRIMARY KEY(`login`)
    )");

echo 'ok';
?>
