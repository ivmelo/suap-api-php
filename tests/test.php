<?php

require dirname(__DIR__).'/vendor/autoload.php';

error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING);

use Ivmelo\SUAP\SUAP;

date_default_timezone_set('America/Fortaleza');

/*
 * Recebe argumentos do terminal.
 *
 * Para pegar um Token:
 * $ php test.php <student_id> <acess_key>
 *
 * Depois, você pode usar o token para fazer os requests:
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
        // Se desejar, você pode passar o token no construtor.
        $client->setToken($token);
        print_r($client->getMeusDados());
        print_r($client->getMeuBoletim(2016, 2));
        print_r($client->getTurmasVirtuais(2017, 1));
        print_r($client->getTurmaVirtual(23115));
        print_r($client->getHorarios(2017, 1));
        print_r($client->getMeusPeriodosLetivos());
    } else {
        print_r($client->autenticar($student_id, $suap_key, true));
    }
} catch (Exception $e) {
    // Mostrar erros.
    echo $e;
}
