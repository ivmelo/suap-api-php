<?php

require dirname(__DIR__).'/vendor/autoload.php';

error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING);

use Ivmelo\SUAP\SUAP;

date_default_timezone_set('America/Fortaleza');

/**
 * Get arguments from terminal.
 *
 * To get a token:
 * $ php test.php <student_id> <acess_key>
 *
 * Then, you can use your token to get student data:
 * $ php test.php <token>
 *
 */
if (count($argv) == 2) {
    $token = $argv[1];
} else {
    $student_id = $argv[1];
    $suap_key = $argv[2];
}

try {
    $client = new SUAP();

    if (isset($token)) {
        $client->setToken($token); // You can use the constructor the same way.
        print_r($client->getMeusDados());
        print_r($client->getMeuBoletim(2017, 1));
        print_r($client->getTurmasVirtuais(2017, 1));
        print_r($client->getTurmaVirtual(23115));
        print_r($client->getAulas(2017, 1));
    } else {
        $data = $client->autenticar($student_id, $suap_key);
        echo 'Token: ' . $data['token'] . "\n";
    }

} catch (Exception $e) {
    // Print exception message.
    echo $e;
}
