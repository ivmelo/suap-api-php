<?php

require "../vendor/autoload.php";

use \Ivmelo\SUAPClient\SUAPClient;

date_default_timezone_set('America/Fortaleza');

$username = $argv[1];
$password = $argv[2];

$client = new SUAPClient($username, $password);

print_r($client->getGrades());
