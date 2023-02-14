<?php

// DB connection

$dsn = 'pgsql:dbname=myFilms;host=localhost';
$user = 'postgres';
$password = 'progres';

$db = new PDO($dsn, $user, $password);
$db->exec('SET search_path TO film');