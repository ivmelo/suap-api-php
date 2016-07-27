# SUAPClient
A PHP client for SUAP (Sistema Unificado de Administração Publica).

This package allows you to access data from SUAP. (https://suap.ifrn.edu.br/)

It shows grades, attendance and courses.

### Instalation
This package is available through composer.

Add this to your composer.json and run `composer update`
```
"require": {
    "ivmelo/suapclient": "@dev"
}
```

### Usage
```
$suap_client = SUAPClient('student_id', 'suap_password');
// or
$suap_client = SUAPClient();
$suap_client->setCredentials('student_id', 'suap_password');
```

Getting data.
```
$grades = $suap_client->getGrades();
```

The output will be an array with course information.

```
Array
(
    [0] => Array
        (
            [diary] => 7441
            [course_code] => TEC.0025
            [course] => Arquitetura de Software
            [class_hours] => 80
            [classes_given] => 46
            [absences] => 14
            [attendance] => 70
            [situation] => cursando
            [bm1_grade] =>
            [bm1_absences] => 6
            [bm2_grade] =>
            [bm2_absences] => 8
            [average] =>
            [n] =>
            [f] =>
            [mfd] =>
        )

    [1] => Array
        (
            [diary] => 9693
            [course_code] => TEC.0077
            [course] => Desenvolvimento de Jogos
            [class_hours] => 80
            [classes_given] => 64
            [absences] => 22
            [attendance] => 66
            [situation] => cursando
            [bm1_grade] => 90
            [bm1_absences] => 14
            [bm2_grade] =>
            [bm2_absences] => 8
            [average] => 36
            [n] =>
            [f] =>
            [mfd] => 36
        )

    [2] => Array
        (
            [diary] => 7440
            [course_code] => TEC.0023
            [course] => Desenvolvimento de Sistemas Distribuídos
            [class_hours] => 120
            [classes_given] => 86
            [absences] => 10
            [attendance] => 89
            [situation] => cursando
            [bm1_grade] => 82
            [bm1_absences] => 2
            [bm2_grade] =>
            [bm2_absences] => 8
            [average] =>
            [n] =>
            [f] =>
            [mfd] =>
        )

    [3] => Array
        (
            [diary] => 7428
            [course_code] => TEC.0004
            [course] => Epistemologia da Ciência
            [class_hours] => 40
            [classes_given] => 16
            [absences] => 16
            [attendance] =>
            [situation] => cancelado
            [bm1_grade] =>
            [bm1_absences] => 16
            [bm2_grade] =>
            [bm2_absences] =>
            [average] =>
            [n] =>
            [f] =>
            [mfd] =>
        )

)
```

## How does it work?
It uses a web crawler to go through the HTML page and grab the relevant information.

## TODO List
1. Get student info;
1. Get history;
1. Class schedule and location.
