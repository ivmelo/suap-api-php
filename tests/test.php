<?php

require dirname(__DIR__).'/vendor/autoload.php';

error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING);

use Ivmelo\SUAPClient\SUAPClient;

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

    print_r($client->getGrades());
    print_r($client->getStudentData());
    print_r($client->getCourses());
    print_r($client->getCourseData('TEC.0080'));
    print_r($client->filterCoursesByName('teste paradigma'));
    print_r($client->getClasses()); //$client->getClasses('2016.1')
    print_r($client->getSchedule(2)); //$client->getSchedule(2, '2016.2')

    // print_r($client->getSchedule(2, '2016.1'));
    // print_r($client->getSchedule(2, '2016.2'));
    // print_r($client->getSchedule(2));
    print_r($client->getWeekSchedule());
} catch (Exception $e) {
    // Print error.
    echo $e;
}
