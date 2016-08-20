<?php

require dirname(__DIR__).'/vendor/autoload.php';

use ivmelo\SUAPClient\SUAPClient;

date_default_timezone_set('America/Fortaleza');

// Get arguments from terminal.
// $ php test.php <student_id> <acess_key>
$student_id = $argv[1];
$suap_key = $argv[2];

try {
    // You can use either one of these.
    // $client = new SUAPClient('student_id', 'suap_password');
    // $client = new SUAPClient('student_id', 'suap_access_key', true);

    $client = new SUAPClient($student_id, $suap_key, true);

    // print_r($client->getGrades());
    // print_r($client->getStudentData());
    // print_r($client->getCourses());
    // print_r($client->getCourseData('TEC.0077'));
//    print_r($client->filterCoursesByName('de'));
    print_r($client->getHorarios());
    // print_r($client->getClasses());
} catch (Exception $e) {
    // Print error.
    echo $e;
}
