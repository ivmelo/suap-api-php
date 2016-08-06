<?php

require '../vendor/autoload.php';

use \Ivmelo\SUAPClient\SUAPClient;

date_default_timezone_set('America/Fortaleza');


// you can use either one...
$client = new SUAPClient('student_id', 'responsavel_access_key', true);
//$client = new SUAPClient('student_id', 'suap_password');

print_r($client->getGrades());
print_r($client->getStudentData());
