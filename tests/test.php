<?php

require '../vendor/autoload.php';

use \Ivmelo\SUAPClient\SUAPClient;

date_default_timezone_set('America/Fortaleza');

$username = readline('Username(matricula): ');
$password = readline('Password: ');

$client = new SUAPClient($username, $password);

print_r($client->getGrades());
