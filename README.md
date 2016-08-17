# SUAPClient
Um cliente PHP para o SUAP (Sistema Unificado de Administração Publica).

For documentation in English [click here](https://github.com/ivmelo/suap-client/blob/master/README_EN.md). (No longer updated).

Este pacote permite que você tenha acesso aos dados do SUAP na sua aplicação. (https://suap.ifrn.edu.br/)

É o componente principal do [SUAP Bot](https://telegram.me/suapbot).

Atualmente fornece informações de boletim (notas, frequência, cursos) e dados do aluno com alguns filtros e buscas simples. Funciona apenas no SUAP do IFRN, porém com um pouco de adaptação, pode funcionar no SUAP de outros IF's também.

Ele faz [scraping](https://en.wikipedia.org/wiki/Web_scraping) nas páginas do SUAP em busca dos dados desejados. Porém no futuro pretende-se usar a API REST do SUAP que _segundo informações_, está em desenvolvimento.

**Todos os _Pull Requests_ devem ser feitos para a branch ```dev```!**

### Instalação
Este pacote está disponível através do composer.

Adicione a dependência abaixo no composer.json e execute ```composer update```.

```json
"require": {
    "ivmelo/suapclient": "^0.0.1"
}
```
Alternativamente, você pode instalar direto pela linha de comando:

```bash
$ composer require "ivmelo/suapclient": "^0.0.1"
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
Para receber dados do boletim do aluno, basta instanciar um cliente e chamar o método ```getGrades();```

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
Para receber dados do aluno, basta chamar o método ```getStudentData();```.

```php
$grades = $suap_client->getStudentData();
```

A saída será um array com informações básicas do estudante e do curso.

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

### Massa! Como isso funciona?
A biblioteca utiliza um cliente HTTP para fazer os requests ao SUAP, e um DOM Parser para _minerar_ as informações relevantes das páginas HTML.

## TODO:
1. Informações do Aluno; [DONE]
1. Histórico do Aluno;
1. Horário e Local de Aulas;
1. Usar chave de acesso em vez de senha do aluno [DONE].
