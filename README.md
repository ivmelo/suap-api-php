# SUAP API PHP

[![SensioLabsInsight](https://insight.sensiolabs.com/projects/cca1ac97-8137-4887-86f1-78c927125cbd/mini.png)](https://insight.sensiolabs.com/projects/cca1ac97-8137-4887-86f1-78c927125cbd)
[![StyleCI](https://styleci.io/repos/64276422/shield)](https://styleci.io/repos/64276422)
[![Latest Stable Version](https://poser.pugx.org/ivmelo/suap-api-php/v/stable)](https://packagist.org/packages/ivmelo/suap-api-php)
[![Total Downloads](https://poser.pugx.org/ivmelo/suap-api-php/downloads)](https://packagist.org/packages/ivmelo/suap-api-php)
[![License](https://poser.pugx.org/ivmelo/suap-api-php/license)](https://packagist.org/packages/ivmelo/suap-api-php)
[![Safadão](https://img.shields.io/badge/safadão-aprova-yellowgreen.svg)](https://img.shields.io/badge/safadão-aprova-yellowgreen.svg)

Um cliente PHP (não oficial) para o SUAP (Sistema Unificado de Administração Publica).

For documentation in English [click here](https://github.com/ivmelo/suap-client/blob/master/README_EN.md). (No longer updated).

Este pacote permite que você tenha acesso aos dados do SUAP na sua aplicação PHP. (https://suap.ifrn.edu.br/)

É o componente principal do [SUAP Bot](https://telegram.me/suapbot).

Atualmente fornece informações de boletim (notas, frequência, cursos) e dados do aluno com alguns filtros e buscas simples. Funciona apenas no SUAP do IFRN, porém com um pouco de adaptação, pode funcionar no SUAP de outros IF's também.

Ele faz [scraping](https://en.wikipedia.org/wiki/Web_scraping) nas páginas do SUAP em busca dos dados desejados. Porém no futuro pretende-se usar a API REST do SUAP que _segundo informações, está em desenvolvimento_.


### Instalação
Este pacote está disponível através do composer.

Adicione a dependência abaixo no composer.json e execute ```composer update```.

```json
"require": {
    "ivmelo/suap-api-php": "^0.1.0"
}
```
Alternativamente, você pode instalar direto pela linha de comando:

```bash
$ composer require "ivmelo/suap-api-php": "^0.0.3"
```

### Uso
Você pode instanciar um cliente usando a matrícula do aluno e a senha ou a chave de acesso do responsável.

```php
$suap_client = SUAPClient('matricula', 'senha');
```
ou ainda

```php
$suap_client = SUAPClient('matricula', 'chave_de_acesso', true);
```
Repare que ao usar a chave de acesso, você precisa passar ```true``` como terceiro parâmetro do construtor.



### Boletim
Para receber dados do boletim do aluno, basta instanciar um cliente e chamar o método ```getGrades()```

```php
$grades = $suap_client->getGrades();
```

A saída será um array com informações sobre a disciplina encontradas no boletim do aluno.

```php
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

### Dados do Aluno
Para receber dados do aluno, basta chamar o método ```getStudentData()```.

```php
$grades = $suap_client->getStudentData();
```

A saída será um array com informações básicas do estudante e do curso.

```php
Array
(
    [nome] => Fulano da Silva
    [situacao] => Matriculado
    [matricula] => 20121014040000
    [ingresso] => 2012/1
    [cpf] => 123.456.789-00
    [periodo_referencia] => 4
    [ira] => 90.00
    [curso] => 01404 - Tecnologia em Análise e Desenvolvimento de Sistemas (2012) - Campus Natal-Central (CAMPUS NATAL - CENTRAL)
    [matriz] => 91 - Tecnologia em Análise e Desenvolvimento de Sistemas (2012)
    [email_academico] => fulano.dasilva@academico.ifrn.edu.br
    [email_pessoal] => email@example.com
    [telefone] => (84) 98765-4321
)

```

### Lista de Disciplinas
Para receber uma lista de disciplinas e códigos, use o método ```getCourses()```

```php
$courses = $suap_client->getCourses();
```

O método retornará um array com a lista de disciplinas do semestre atual e seus respectivos códigos.

```php
Array
(
    [0] => Array
        (
            [codigo] => TEC.0028
            [disciplina] => Desenvolvimento de Sistemas Coorporativos
        )

    [1] => Array
        (
            [codigo] => TEC.0010
            [disciplina] => Empreendedorismo
        )

    [2] => Array
        (
            [codigo] => TEC.0029
            [disciplina] => Gerencia de Projetos
        )

    [3] => Array
        (
            [codigo] => TEC.0080
            [disciplina] => Paradigmas de Linguagens de Programação
        )

    [4] => Array
        (
            [codigo] => TEC.0034
            [disciplina] => Seminário de Orientação ao Projeto de Desenvolvimento de Sistema Coorporativo
        )

    [5] => Array
        (
            [codigo] => TEC.0030
            [disciplina] => Teste de Software
        )

)
```

### Notas/Faltas de Uma Matéria Específica
Para pegar as notas/faltas de uma matéria específico, use o método ```getCourseData('course_code')``` passando o código da disciplina como parâmetro.

```php
$course = $suap_client->getCourseData('TEC.0080');
```

O retorno será um array com as notas dados da disciplina.
```php
Array
(
    [diario] => 16586
    [codigo] => TEC.0080
    [disciplina] => Paradigmas de Linguagens de Programação
    [carga_horaria] => 80
    [aulas] =>
    [faltas] =>
    [frequencia] => 100
    [situacao] => cursando
    [bm1_nota] =>
    [bm1_faltas] =>
    [bm2_nota] =>
    [bm2_faltas] =>
    [media] =>
    [naf_nota] =>
    [naf_faltas] =>
    [mfd] =>
)
```

### Filtrar Disciplinas por Nome
Você também pode fazer um filtro básico nas disciplinas do boletim.
Para isso, basta chamar o método ```filterCoursesByName('query')``` passando os termos que desejam serem "pesquisados" nos nomes das disciplinas do boletim.

```php
$data = $suap_client->getCousesByName('teste paradigma');
```

O resultado será um array com a(s) disciplina(s) que contenha(m) o termo pesquisado no título.

```php
Array
(
    [0] => Array
        (
            [diario] => 16586
            [codigo] => TEC.0080
            [disciplina] => Paradigmas de Linguagens de Programação
            [carga_horaria] => 80
            [aulas] =>
            [faltas] =>
            [frequencia] => 100
            [situacao] => cursando
            [bm1_nota] =>
            [bm1_faltas] =>
            [bm2_nota] =>
            [bm2_faltas] =>
            [media] =>
            [naf_nota] =>
            [naf_faltas] =>
            [mfd] =>
        )

    [1] => Array
        (
            [diario] => 15363
            [codigo] => TEC.0030
            [disciplina] => Teste de Software
            [carga_horaria] => 80
            [aulas] =>
            [faltas] =>
            [frequencia] => 100
            [situacao] => cursando
            [bm1_nota] =>
            [bm1_faltas] =>
            [bm2_nota] =>
            [bm2_faltas] =>
            [media] =>
            [naf_nota] =>
            [naf_faltas] =>
            [mfd] =>
        )

)
```

### Informações Sobre Disciplinas
A biblioteca também permite que você recupere dados de disciplina, incluindo locais de aula, horários, tipo, carga horária e nome dos professores.

```php
$client->getClasses()
```

O retorno será um array associativo com os dados das disciplinas.
```php
Array
(
    [TIN.0599] => Array
        (
            [diario] => 7351
            [codigo] => TIN.0599
            [disciplina] => Programação Orientada a Serviços(120H)
            [tipo] => Médio [120 h/160 Aulas]
            [local] => Laboratório de informática 04 - DIATINF - Informática (CNAT)
            [horario] => 2V34 / 5V56
            [professores] => Array
                (
                    [0] => Nome do Professor
                )

        )

    [TIN.0244] => Array
        (
            [diario] => 7353
            [codigo] => TIN.0244
            [disciplina] => Gestão Organizacional(30H)
            [tipo] => Médio [30 h/40 Aulas]
            [local] => C-21 - Bloco C (CNAT)
            [horario] => 2V12
            [professores] => Array
                (
                    [0] => Nome do Professor
                    [1] => Nome do Outro Professor
                )

        )

    [TIN.0400] => Array
        (
            [diario] => 13727
            [codigo] => TIN.0400
            [disciplina] => Francês(90H)
            [tipo] => Médio [90 h/120 Aulas]
            [local] => -
            [horario] => 3V123
            [professores] => Array
                (
                    [0] => Nome do Professor
                )

        )
)
```

Note que o formato das informações podem conter pequenas variações de acordo de o tipo de programa cursado pelo aluno (superior, médio, técnico, etc...).

### Horários de Aula
Para recuperar horários de aula, use o método ```getSchedule(dia_da_semana)``` passando o dia da semana (int) como parâmetro.

1 para domingo, 2 para segunda, 3 para terça...

Ex: para recuperar os horários da segunda feira:

```php
$client->getSchedule(2)
```

Isso retornará um array associativo usando os horários como chave, e as informações do curso como valores.

```php
Array
(
    [matutino] => Array
        (
            [07:00 - 07:45] =>
            [07:45 - 08:30] =>
            [08:50 - 09:35] =>
            [09:35 - 10:20] =>
            [10:30 - 11:15] =>
            [11:15 - 12:00] =>
        )

    [vespertino] => Array
        (
            [13:00 - 13:45] => Array
                (
                    [diario] => 7353
                    [codigo] => TIN.0244
                    [disciplina] => Gestão Organizacional(30H)
                    [tipo] => Médio [30 h/40 Aulas]
                    [local] => C-21 - Bloco C (CNAT)
                    [horario] => 2V12
                    [professores] => Array
                        (
                            [0] => Nome do Professor
                        )

                )

            [13:45 - 14:30] => Array
                (
                    [diario] => 7353
                    [codigo] => TIN.0244
                    [disciplina] => Gestão Organizacional(30H)
                    [tipo] => Médio [30 h/40 Aulas]
                    [local] => C-21 - Bloco C (CNAT)
                    [horario] => 2V12
                    [professores] => Array
                        (
                            [0] => Nome do Professor
                        )

                )

            [14:40 - 15:25] => Array
                (
                    [diario] => 7351
                    [codigo] => TIN.0599
                    [disciplina] => Programação Orientada a Serviços(120H)
                    [tipo] => Médio [120 h/160 Aulas]
                    [local] => Laboratório de informática 04 - DIATINF - Informática (CNAT)
                    [horario] => 2V34 / 5V56
                    [professores] => Array
                        (
                            [0] => Nome do Professor
                        )

                )

            [15:25 - 16:10] => Array
                (
                    [diario] => 7351
                    [codigo] => TIN.0599
                    [disciplina] => Programação Orientada a Serviços(120H)
                    [tipo] => Médio [120 h/160 Aulas]
                    [local] => Laboratório de informática 04 - DIATINF - Informática (CNAT)
                    [horario] => 2V34 / 5V56
                    [professores] => Array
                        (
                            [0] => Nome do Professor
                        )

                )

            [16:30 - 17:15] => Array
                (
                    [diario] => 7352
                    [codigo] => TIN.0598
                    [disciplina] => Projeto de Interface do Usuário(120H)
                    [tipo] => Médio [120 h/160 Aulas]
                    [local] => Laboratório de informática 07 - DIATINF - Informática (CNAT)
                    [horario] => 2V56 / 5V34
                    [professores] => Array
                        (
                            [0] => Nome do Professor
                        )

                )

            [17:15 - 18:00] => Array
                (
                    [diario] => 7352
                    [codigo] => TIN.0598
                    [disciplina] => Projeto de Interface do Usuário(120H)
                    [tipo] => Médio [120 h/160 Aulas]
                    [local] => Laboratório de informática 07 - DIATINF - Informática (CNAT)
                    [horario] => 2V56 / 5V34
                    [professores] => Array
                        (
                            [0] => Nome do Professor
                        )

                )

        )

    [noturno] => Array
        (
            [19:00 - 19:45] =>
            [19:45 - 20:30] =>
            [20:40 - 21:25] =>
            [21:25 - 22:10] =>
        )

)
```

### E se não houver informações no SUAP?
Caso alguma informação não seja encontrada no SUAP, os métodos retornarão arrays vazios.

Você pode usar o método ```! empty($data)``` para saber se os dados foram retornados com sucesso.


### Massa! Como isso funciona?
A biblioteca utiliza um cliente HTTP para fazer os requests ao SUAP, e um DOM Parser para procurar e extrair as informações relevantes das páginas HTML.

### Desenvolvimento (Como contribuir.)
Para ajudar no Desenvolvimento, clone o repositório, instale as dependências e use o arquivo test.php que encontra-se na pasta tests.

```php
$ git clone git@github.com:ivmelo/suap-api-php.git
$ cd suap-api-php
$ composer install
$ cd tests
$ php test.php <matricula> <chave>
```

O código em desenvolvimento mais recente está na branch ```dev```.

*TODOS OS PULL REQUESTS DEVEM SER FEITOS PARA A BRANCH ```dev```.*

### Coisas a Fazer:
1. Informações do Aluno; [DONE]
1. Horário e Local de Aulas [DONE];
1. Usar chave de acesso em vez de senha do aluno [DONE].
1. Histórico do Aluno;
1. Emitir documentos em PDF (declarações de matrícula, histórico, entre outros... Só funcionará se estiver autenticando com a senha).

Veja a sessão de [Issues](https://github.com/ivmelo/suap-api-php/issues) para mais coisas a fazer.

### Licença
The MIT License (MIT)

Copyright (c) 2016 Ivanilson Melo

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
