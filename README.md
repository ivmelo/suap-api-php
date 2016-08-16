# SUAPClient
A PHP client for SUAP (Sistema Unificado de Administração Publica).

This package allows you to access data from SUAP. (https://suap.ifrn.edu.br/)

It shows grades, attendance and courses.

**All pull requests should be made to the ```dev``` branch!**

### Instalation
This package is available through composer.

Add this to your composer.json and run `composer update`
```json
"require": {
    "ivmelo/suapclient": "^0.0.1"
}
```

### Usage
```php
$suap_client = SUAPClient('student_id', 'suap_password');
// or
$suap_client = SUAPClient();
$suap_client->setCredentials('student_id', 'suap_password');
```

You can also use your _responsável_ access key (chave de acesso), which can be found under the 'Dados Pessoais' tab in your suap account.

It's located in the 'Dados Gerais' session.

Please notice that if using an access key, you need to suply "true" to the third argument in the constructor.
```php
$suap_client = SUAPClient('student_id', 'access_key', true);
```

#### Getting course data.
```php
$grades = $suap_client->getGrades();
```

The output will be an array with course information.

```
Array
(
    [0] => Array
        (
            [diario] => 7441
            [codigo] => TEC.0025
            [disciplina] => Arquitetura de Software
            [carga_horaria] => 80
            [aulas] => 50
            [faltas] => 16
            [frequencia] => 68
            [situacao] => cursando
            [bm1_nota] =>
            [bm1_faltas] => 6
            [bm2_nota] =>
            [bm2_faltas] => 10
            [media] =>
            [naf_nota] =>
            [naf_faltas] =>
            [mfd] =>
        )

    [1] => Array
        (
            [diario] => 9693
            [codigo] => TEC.0077
            [disciplina] => Desenvolvimento de Jogos
            [carga_horaria] => 80
            [aulas] => 72
            [faltas] => 28
            [frequencia] => 62
            [situacao] => cursando
            [bm1_nota] => 90
            [bm1_faltas] => 14
            [bm2_nota] =>
            [bm2_faltas] => 14
            [media] => 36
            [naf_nota] =>
            [naf_faltas] =>
            [mfd] => 36
        )

    [2] => Array
        (
            [diario] => 7440
            [codigo] => TEC.0023
            [disciplina] => Desenvolvimento de Sistemas Distribuídos
            [carga_horaria] => 120
            [aulas] => 96
            [faltas] => 12
            [frequencia] => 88
            [situacao] => cursando
            [bm1_nota] => 82
            [bm1_faltas] => 2
            [bm2_nota] =>
            [bm2_faltas] => 10
            [media] =>
            [naf_nota] =>
            [naf_faltas] =>
            [mfd] =>
        )
)

```

#### Getting student data
```php
$grades = $suap_client->getStudentData();
```

The output will be an array with student information.

```
Array
(
    [nome] => Fulano da Silva
    [situacao] => Matriculado
    [matricula] => 20121014040000
    [ingresso] => 2012/1
    [cpf] => 123.456.789-00
    [periodo_referencia] => 4
    [ira] => 90,00
    [curso] => 01404 - Tecnologia em Análise e Desenvolvimento de Sistemas (2012) - Campus Natal-Central (CAMPUS NATAL - CENTRAL)
    [matriz] => 91 - Tecnologia em Análise e Desenvolvimento de Sistemas (2012)
    [email_academico] => fulano.dasilva@academico.ifrn.edu.br
    [email_pessoal] => email@example.com
    [telefone] => (84) 98765-4321
)

```

## How does it work?
It uses a web crawler to go through the HTML page and grab the relevant information.

## TODO List
1. Get student info; [DONE]
1. Get history;
1. Class schedule and location;
1. Use the student access code instead of the SUAP password.
