# SUAP API PHP

[![SensioLabsInsight](https://insight.sensiolabs.com/projects/cca1ac97-8137-4887-86f1-78c927125cbd/mini.png)](https://insight.sensiolabs.com/projects/cca1ac97-8137-4887-86f1-78c927125cbd)
[![StyleCI](https://styleci.io/repos/64276422/shield)](https://styleci.io/repos/64276422)
[![Latest Stable Version](https://poser.pugx.org/ivmelo/suap-api-php/v/stable)](https://packagist.org/packages/ivmelo/suap-api-php)
[![Total Downloads](https://poser.pugx.org/ivmelo/suap-api-php/downloads)](https://packagist.org/packages/ivmelo/suap-api-php)
[![License](https://poser.pugx.org/ivmelo/suap-api-php/license)](https://packagist.org/packages/ivmelo/suap-api-php)

Um wrapper PHP para a [API](http://suap.ifrn.edu.br/api/docs/) do [SUAP (Sistema Unificado de Administração Publica)](http://portal.ifrn.edu.br/tec-da-informacao/servicos-ti/menus/servicos/copy2_of_suap) do IFRN. Este pacote permite que você tenha acesso aos dados do SUAP na sua aplicação PHP.

É o componente principal do [SUAP Bot](https://telegram.me/suapbot).

Atualmente fornece informações de boletim (notas, frequência), cursos, horários, locais de aula e dados do aluno.

Este pacote foi atualizado para acessar os dados através da API oficial do SUAP, e não mais fazendo web scraping. Caso deseje utilizar a versão que faz web scraping, veja a tag `0.2.0`.


### Instalação
Para instalar, recomenda-se o uso do [Composer](https://getcomposer.org).

Adicione a dependência abaixo no seu composer.json e execute ```composer update```.

```json
"require": {
    "ivmelo/suap-api-php": "1.0.*"
}
```
Alternativamente, você pode instalar diretamente pela linha de comando:

```bash
$ composer require "ivmelo/suap-api-php": "1.0.*"
```

### Uso
Você pode instanciar um cliente usando um token de acesso, ou usar o construtor vazio.

```php
$suap = SUAP('token');
// ou
$suap = SUAP();
$suap->setToken('token');

```

### Autenticação

Para autenticar, basta usar chamar o método `autenticar($usuario, $chave)`.

```
$suap = SUAP();
$data = $suap->autenticar('20121014040000', 'senhaouchave');
```

O método retornará um array com um token de acesso (`$data['token']`).
```
Array
(
    [token] => eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9eyJ1c2VybmFtZSI6IjIwMTIxMDE0MDQwMDgzIiwib3JpZ19pYXQiOjE0OTQwMjcyMDksInVzZXJfaWQiOjEwODQyLCJlbWFpbCI6Iml2YW5pbHNvbi5tZWxvQGFjYWRlbWljby5pZnJuLmVkdS5iciIsImV4cCI6MTQ5NDExMzYwOX0
)
```

O token será salvo no objeto para que seja reutilizado nos requests subsequentes. Caso não deseje salvar o token, basta setar o quarto parâmetro do construtor como false: `autenticar($matricula, $senha, true, false)`.

Você também pode utilizar a chave de acesso de responsável para efetuar o login. Para isso, basta passar true como terceiro parâmetro do método `autenticar($matricula, $chave, true)`.

```
$suap = SUAP();
$suap->autenticar('20121014040000', 'chave', true);
```

Para obter a chave de acesso, faça login no SUAP, e vá em "Meus Dados" > "Dados pessoais" > "Dados Gerais" e procure por "Chave de Acesso. Ela deve ter 5 dígitos e ser algo parecido com ```4d5f9```.


### Dados do Aluno
Para receber dados do aluno, basta chamar o método `getMeusDados()`.

```php
$meusDados = $suap->getMeusDados();
```

A saída será um array com informações básicas do estudante e do curso.

```
Array
(
    [id] => 123456
    [matricula] => 20121014040000
    [nome_usual] => Nome Sobrenome
    [email] => nome.sobrenome@academico.ifrn.edu.br
    [url_foto_75x100] => /media/alunos/000000.jpg
    [tipo_vinculo] => Aluno
    [vinculo] => Array
        (
            [matricula] => 20121014040000
            [nome] => Nome Completo Do Estudante
            [curso] => Tecnologia em Análise e Desenvolvimento de Sistemas
            [campus] => CNAT
            [situacao] => Matriculado
            [cota_sistec] =>
            [cota_mec] =>
            [situacao_sistemica] => Migrado do Q-Acadêmico para o SUAP
        )

)
```


### Períodos Letivos
Para receber os períodos letivos do aluno use o método `getMeusPeriodosLetivos()`.

```php
$meusDados = $suap->getMeusPeriodosLetivos();
```

A saída será um array com a listagem de períodos letivos do aluno.

```
Array
(
    [0] => Array
        (
            [ano_letivo] => 2012
            [periodo_letivo] => 1
        )

    [1] => Array
        (
            [ano_letivo] => 2012
            [periodo_letivo] => 2
        )

    ...

    [10] => Array
        (
            [ano_letivo] => 2017
            [periodo_letivo] => 1
        )

)
```

### Boletim
Para receber dados do boletim do aluno, basta instanciar um cliente e chamar o método `getMeuBoletim($anoLetivo, $periodoLetivo)`.

```
$boletim = $suap->getMeuBoletim(2017, 1);
```

A saída será um array com informações sobre a disciplina encontradas no boletim do aluno.

Alunos do ensino superior só terão as notas da etapa 1 e 2.

```
Array
(
    [0] => Array
        (
            [codigo_diario] => 15360
            [disciplina] => TEC.0028 - Desenvolvimento de Sistemas Coorporativos
            [segundo_semestre] =>
            [carga_horaria] => 80
            [carga_horaria_cumprida] => 76
            [numero_faltas] => 8
            [percentual_carga_horaria_frequentada] => 90
            [situacao] => Aprovado
            [quantidade_avaliacoes] => 2
            [nota_etapa_1] => Array
                (
                    [nota] => 92
                    [faltas] => 0
                )

            [nota_etapa_2] => Array
                (
                    [nota] => 50
                    [faltas] => 8
                )

            [nota_etapa_3] => Array
                (
                    [nota] =>
                    [faltas] => 0
                )

            [nota_etapa_4] => Array
                (
                    [nota] =>
                    [faltas] => 0
                )

            [media_disciplina] => 67
            [nota_avaliacao_final] => Array
                (
                    [nota] =>
                    [faltas] => 0
                )

            [media_final_disciplina] => 67
        )

    [1] => Array
        (
            [codigo_diario] => 15359
            [disciplina] => TEC.0010 - Empreendedorismo
            [segundo_semestre] =>
            [carga_horaria] => 40
            [carga_horaria_cumprida] => 40
            [numero_faltas] => 6
            [percentual_carga_horaria_frequentada] => 85
            [situacao] => Aprovado
            [quantidade_avaliacoes] => 2
            [nota_etapa_1] => Array
                (
                    [nota] => 80
                    [faltas] => 2
                )

            [nota_etapa_2] => Array
                (
                    [nota] => 100
                    [faltas] => 4
                )

            [nota_etapa_3] => Array
                (
                    [nota] =>
                    [faltas] => 0
                )

            [nota_etapa_4] => Array
                (
                    [nota] =>
                    [faltas] => 0
                )

            [media_disciplina] => 92
            [nota_avaliacao_final] => Array
                (
                    [nota] =>
                    [faltas] => 0
                )

            [media_final_disciplina] => 92
        )

    ...

    [5] => Array
        (
            [codigo_diario] => 15363
            [disciplina] => TEC.0030 - Teste de Software
            [segundo_semestre] =>
            [carga_horaria] => 80
            [carga_horaria_cumprida] => 72
            [numero_faltas] => 24
            [percentual_carga_horaria_frequentada] => 67
            [situacao] => Aprovado
            [quantidade_avaliacoes] => 2
            [nota_etapa_1] => Array
                (
                    [nota] => 47
                    [faltas] => 20
                )

            [nota_etapa_2] => Array
                (
                    [nota] => 73
                    [faltas] => 4
                )

            [nota_etapa_3] => Array
                (
                    [nota] =>
                    [faltas] => 0
                )

            [nota_etapa_4] => Array
                (
                    [nota] =>
                    [faltas] => 0
                )

            [media_disciplina] => 63
            [nota_avaliacao_final] => Array
                (
                    [nota] =>
                    [faltas] => 0
                )

            [media_final_disciplina] => 63
        )

)
```

### Listagem de Turmas Virtuais
Para visualizar a listagem das turmas virtuais, incluindo ids, horários e locais de aula, use o método `getTurmasVirtuais($anoLetivo, $periodoLetivo)`.

```php
$turmasVirtuais = $suap->getTurmasVirtuais(2017, 1);
```

O método retornará um array com a lista de disciplinas do semestre atual junto com outras informações sobre as mesmas.

```
Array
(
    [0] => Array
        (
            [id] => 20118
            [sigla] => TEC.0011
            [descricao] => Gestão de Tecnologia da Informação
            [observacao] =>
            [locais_de_aula] => Array
                (
                    [0] => Audio de Visual 03 - DIATINF - Prédio Anexo - 1º Andar (CNAT)
                )

            [horarios_de_aula] => 2V34 / 3V56
        )

    [1] => Array
        (
            [id] => 20119
            [sigla] => TEC.0012
            [descricao] => Computador e Sociedade
            [observacao] =>
            [locais_de_aula] => Array
                (
                    [0] => Audio de Visual 03 - DIATINF - Prédio Anexo - 1º Andar (CNAT)
                )

            [horarios_de_aula] => 3V34
        )

    [2] => Array
        (
            [id] => 20120
            [sigla] => TEC.0036
            [descricao] => Seminário de Orientação para Trabalho de Conclusão de Curso
            [observacao] =>
            [locais_de_aula] => Array
                (
                    [0] => Audio de Visual 03 - DIATINF - Prédio Anexo - 1º Andar (CNAT)
                )

            [horarios_de_aula] => 4V34
        )

    [3] => Array
        (
            [id] => 20102
            [sigla] => TEC.0004
            [descricao] => Epistemologia da Ciência
            [observacao] =>
            [locais_de_aula] => Array
                (
                    [0] => Audio de Visual 02 - DIATINF - Informática (CNAT)
                )

            [horarios_de_aula] => 3M56
        )

    [4] => Array
        (
            [id] => 23115
            [sigla] => TEC.0075
            [descricao] => Aplicações com Interfaces Ricas
            [observacao] =>
            [locais_de_aula] => Array
                (
                    [0] => Laboratório 06 - DIATINF - Informática (CNAT)
                )

            [horarios_de_aula] => 2M56 / 4M56
        )

)
```

### Detalhes de Turma Virtual
Para visualizar os detalhes de uma turma virtual, basta usar o método `getTurmaVirtual($idDaTurma)`.

```
$course = $suap->getTurmaVirtual(23115);
```

O retorno será um array com os detalhes da turma incluindo participantes, aulas, materiais de aula, professores e etc...
```
Array
(
    [id] => 23115
    [ano_letivo] => 2017
    [periodo_letivo] => 1
    [componente_curricular] => TEC.0075 - Aplicações com Interfaces Ricas (NCT) - Graduação [60 h/80 Aulas] - Curso 404
    [professores] => Array
        (
            [0] => Array
                (
                    [matricula] => 123456
                    [foto] => /media/fotos/75x100/ABCEDF000000.jpg
                    [email] => email.professor@ifrn.edu.br
                    [nome] => Nome do Professor
                )

        )

    [locais_de_aula] => Array
        (
            [0] => Laboratório 06 - DIATINF - Informática (CNAT)
        )

    [data_inicio] => 2017-03-21
    [data_fim] => 2017-08-01
    [participantes] => Array
        (
            [0] => Array
                (
                    [matricula] => 20121000000000
                    [foto] => /media/alunos/75x100/000000.jpg
                    [email] => email.do.aluno@academico.ifrn.edu.br
                    [nome] => Nome do Aluno
                )

            [1] => Array
                (
                    [matricula] => 20121000000000
                    [foto] => /media/alunos/75x100/000000.jpg
                    [email] => email.do.aluno@academico.ifrn.edu.br
                    [nome] => Nome do Aluno
                )

            [2] => Array
                (
                    [matricula] => 20121000000000
                    [foto] => /media/alunos/75x100/000000.jpg
                    [email] => email.do.aluno@academico.ifrn.edu.br
                    [nome] => Nome do Aluno
                )

            [3] => Array
                (
                    [matricula] => 20121000000000
                    [foto] => /media/alunos/75x100/000000.jpg
                    [email] => email.do.aluno@academico.ifrn.edu.br
                    [nome] => Nome do Aluno
                )

        )

    [aulas] => Array
        (
            [0] => Array
                (
                    [etapa] => 1
                    [professor] => Nome do Professor
                    [quantidade] => 2
                    [faltas] => 0
                    [conteudo] => Isolated Storage.
                    [data] => 2017-05-03
                )

            [1] => Array
                (
                    [etapa] => 1
                    [professor] => Nome do Professor
                    [quantidade] => 2
                    [faltas] => 2
                    [conteudo] => Treinamento em Python.
                    [data] => 2017-04-26
                )

            [2] => Array
                (
                    [etapa] => 1
                    [professor] => Nome do Professor
                    [quantidade] => 2
                    [faltas] => 0
                    [conteudo] => Introdução ao Python.
                    [data] => 2017-04-24
                )

        )

    [materiais_de_aula] => Array
        (
            [0] => Array
                (
                    [url] => /media/edu/material_aula/material_de_aula.pdf
                    [data_vinculacao] => 2017-04-18
                    [descricao] => Exemplo Silverlight (DataGrid)
                )

            [1] => Array
                (
                    [url] => /media/edu/material_aula/material_de_aula.pdf
                    [data_vinculacao] => 2017-04-07
                    [descricao] => Estilos no Silverlight (Exemplos)
                )

            [2] => Array
                (
                    [url] => /media/edu/material_aula/material_de_aula.pdf
                    [data_vinculacao] => 2017-04-05
                    [descricao] => Silverlight Exemplos01
                )
        )

)

```

### Horários de Aula
Para recuperar horários de aula no formato de array, use o método `getHorarios($anoLetivo, $periodoLetivo)`.

```php
$horarios = $suap->getHorarios(2017, 1);
```

Isso retornará um array associativo usando dias da semana como chave (1: dom, 2: seg, 3: ter...), o turno como subchave (M: matutino, V: vespertino, N: noturno) e o slot da aula como segunda subchave(1-6).

I.E. Para pegar a quarta aula da terça feira a tarde:
```
print_r($schedule[3]['V'][4]);
```

O retorno do método pode ser visto a seguir (Algumas partes foram omitidas usando `...`).
```
Array
(
    [1] => Array
        (
            [M] => Array
                (
                    ...
                )

            [V] => Array
                (
                    ...
                )

            [N] => Array
                (
                    ...
                )

        )

    [2] => Array
        (
            [M] => Array
                (
                    [1] => Array
                        (
                            [time] => 07:00 - 07:45
                        )

                    ...

                    [5] => Array
                        (
                            [time] => 10:30 - 11:15
                            [aula] => Array
                                (
                                    [id] => 23115
                                    [sigla] => TEC.0075
                                    [descricao] => Aplicações com Interfaces Ricas
                                    [observacao] =>
                                    [locais_de_aula] => Array
                                        (
                                            [0] => Laboratório 06 - DIATINF - Informática (CNAT)
                                        )

                                    [horarios_de_aula] => 2M56 / 4M56
                                )

                        )

                    [6] => Array
                        (
                            [time] => 11:15 - 12:00
                            [aula] => Array
                                (
                                    [id] => 23115
                                    [sigla] => TEC.0075
                                    [descricao] => Aplicações com Interfaces Ricas
                                    [observacao] =>
                                    [locais_de_aula] => Array
                                        (
                                            [0] => Laboratório 06 - DIATINF - Informática (CNAT)
                                        )

                                    [horarios_de_aula] => 2M56 / 4M56
                                )

                        )

                )

            [V] => Array
                (
                    [1] => Array
                        (
                            [time] => 13:00 - 13:45
                        )

                    [2] => Array
                        (
                            [time] => 13:45 - 14:30
                        )

                    [3] => Array
                        (
                            [time] => 14:40 - 15:25
                            [aula] => Array
                                (
                                    [id] => 20118
                                    [sigla] => TEC.0011
                                    [descricao] => Gestão de Tecnologia da Informação
                                    [observacao] =>
                                    [locais_de_aula] => Array
                                        (
                                            [0] => Audio de Visual 03 - DIATINF - Prédio Anexo - 1º Andar (CNAT)
                                        )

                                    [horarios_de_aula] => 2V34 / 3V56
                                )

                        )

                    [4] => Array
                        (
                            [time] => 15:25 - 16:10
                            [aula] => Array
                                (
                                    [id] => 20118
                                    [sigla] => TEC.0011
                                    [descricao] => Gestão de Tecnologia da Informação
                                    [observacao] =>
                                    [locais_de_aula] => Array
                                        (
                                            [0] => Audio de Visual 03 - DIATINF - Prédio Anexo - 1º Andar (CNAT)
                                        )

                                    [horarios_de_aula] => 2V34 / 3V56
                                )

                        )

                    [5] => Array
                        (
                            [time] => 16:30 - 17:15
                        )

                    [6] => Array
                        (
                            [time] => 17:15 - 18:00
                        )

                )

            [N] => Array
                (
                    [1] => Array
                        (
                            [time] => 19:00 - 19:45
                        )

                    ...

                    [4] => Array
                        (
                            [time] => 21:25 - 22:10
                        )

                )

        )

    [3] => Array
        (
            ...
        )

    ...

    [7] => Array
        (
            ...
        )

)
```

### E se ocorrer algum erro durante o request?
Caso algum erro ocorra durante o request, o cliente HTTP lançará exceções. Isto inclui falha no login, 404, 500, etc...

Você deve usar try-catch blocks para tratar os possíveis erros dentro da sua aplicação sempre que usar algum método da API.

### Desenvolvimento (Como contribuir)
Para ajudar no Desenvolvimento, clone o repositório, instale as dependências e use o arquivo test.php que encontra-se na pasta tests.

```bash
$ git clone git@github.com:ivmelo/suap-api-php.git
$ cd suap-api-php
$ composer install
$ cd tests
$ php test.php <matricula> <chave>
```

Altere o arquivo `test.php` de acordo com a sua preferência, mas evite comitar mudanças a menos que tenha adicionado alguma funcionalidade nova a biblioteca.

O código em desenvolvimento mais recente está na branch `master`.

### Coisas a Fazer:
Veja a sessão de [Issues](https://github.com/ivmelo/suap-api-php/issues) para ver o que falta fazer ou se tem algum bug precisando de atenção.

### Versões Anteriores
Para ver as versões anteriores da biblioteca (Incluindo as que fazem web scraping), veja as tags do projeto.

### Licença
The MIT License (MIT)

Copyright (c) 2016 Ivanilson Melo

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
