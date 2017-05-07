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

        // Meus Dados.
        print_r($client->getMeusDados());

        // Períodos Letivos.
        $periodosLetivos = $client->getMeusPeriodosLetivos();
        print_r($periodosLetivos);
        $year = end($periodosLetivos)['ano_letivo'];
        $term = end($periodosLetivos)['periodo_letivo'];

        // Boletim.
        print_r($client->getMeuBoletim($year, $term));

        // Turmas Virtuais.
        $turmasVirtuais = $client->getTurmasVirtuais($year, $term);
        print_r($turmasVirtuais);

        // Detalhes de Turma Virtual.
        print_r($client->getTurmaVirtual(end($turmasVirtuais)['id']));

        // Horários.
        print_r($client->getHorarios($year, $term));
    } else {
        // Autentica e retorna token.
        print_r($client->autenticar($student_id, $suap_key, true));
    }
} catch (Exception $e) {
    // Mostrar erros.
    echo $e;
}
