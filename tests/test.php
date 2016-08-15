<?php

require '../vendor/autoload.php';

use \Ivmelo\SUAPClient\SUAPClient;

date_default_timezone_set('America/Fortaleza');


// you can use either one...
$client = new SUAPClient('student_id', 'access_key', true);
//$client = new SUAPClient('student_id', 'suap_password');

print_r($client->getGrades());
print_r($client->getStudentData());
print_r($client->getCourses());
print_r($client->getCourseData('TEC.0023'));
