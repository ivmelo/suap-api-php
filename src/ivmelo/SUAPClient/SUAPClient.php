<?php namespace Ivmelo\SUAPClient;

use Goutte\Client;

/**
 * SUAPClient. Get data from SUAP.
 */
class SUAPClient
{
    private $username;
    private $password;
    private $client;
    private $crawler;
    private $matricula;
    private $endpoint = 'https://suap.ifrn.edu.br';
    private $aluno_endpoint = 'https://suap.ifrn.edu.br/edu/aluno/';

    function __construct($username = null, $password = null)
    {
        if ($username && $password) {
            $this->username = $username;
            $this->password = $password;
        }

        // guzzle client
        $this->client = new Client();
    }

    public function setCredentials($username, $password) {
        $this->username = $username;
        $this->password = $password;
    }

    public function doLogin() {
        // get csrf token
        $this->crawler = $this->client->request('GET', $this->endpoint);
        $token = $this->crawler->filter('input[name="csrfmiddlewaretoken"]');
        $token = $token->attr('value');

        // get form and submit
        $form = $this->crawler->selectButton('Acessar')->form();
        $this->crawler = $this->client->submit($form, [
            'username' => $this->username,
            'password' => $this->password,
            'csrfmiddlewaretoken' => $token
        ]);

        // get matricula number
        $meusdados_link = $this->crawler->selectLink('Meus Dados')->link()->getUri();
        $link_parts = explode('/', $meusdados_link);
        $this->matricula = $link_parts[5];
    }

    public function getMatricula() {
        if (! $this->matricula) {
            $this->doLogin();
        }

        return $this->matricula;
    }

    public function getGrades($ano_periodo = '') {
        // $ano_periodo no formato yyyy_p (ex.: 2015_1)

        if (! $this->matricula) {
            $this->doLogin();
        }

        // go to grades page
        $this->crawler = $this->client->request('GET', $this->aluno_endpoint . $this->matricula . '/?tab=boletim' . '&ano_periodo=' . $ano_periodo);

        // get and manipulate grades table
        $grades = $this->crawler->filter('table[class="borda"]');
        $grade_rows = $grades->filter('tbody > tr');

        // course data
        $data = [];

        // iterate over courses
        for ($i = 0; $i < $grade_rows->count(); $i++) {

            $course_data = [];
            $grade_row = $grades->filter('tbody > tr')->eq($i);

            // trim white spaces before diary
            $course_data['diario'] = (int) trim($grade_row->filter('td')->eq(0)->text()) ? (int) trim($grade_row->filter('td')->eq(0)->text()) : null;

            // explode course name and code from the same field
            $namecode = explode(" - ", $grade_row->filter('td')->eq(1)->text());

            // course code without name
            $course_data['codigo'] = trim($namecode[0]);

            // course name without course code
            $course_data['disciplina'] = trim($namecode[1]);

            // get total class-hours for the course
            $course_data['carga_horaria'] = (int) $grade_row->filter('td')->eq(2)->text() ? (int) $grade_row->filter('td')->eq(2)->text() : null;

            // number or classes given
            $course_data['aulas'] = (int) $grade_row->filter('td')->eq(3)->text() ? (int) $grade_row->filter('td')->eq(3)->text() : null;
            $course_data['faltas'] = (int) $grade_row->filter('td')->eq(4)->text() ? (int) $grade_row->filter('td')->eq(4)->text() : null;
            $course_data['frequencia'] = (int) $grade_row->filter('td')->eq(5)->text() ? (int) $grade_row->filter('td')->eq(5)->text() : null;
            $course_data['situacao'] = strtolower($grade_row->filter('td')->eq(6)->text()) ? strtolower($grade_row->filter('td')->eq(6)->text()) : null;
            $course_data['bm1_nota'] = (int) $grade_row->filter('td')->eq(7)->text() ? (int) $grade_row->filter('td')->eq(7)->text() : null;
            $course_data['bm1_faltas'] = (int) $grade_row->filter('td')->eq(8)->text() ? (int) $grade_row->filter('td')->eq(8)->text() : null;
            $course_data['bm2_nota'] = (int) $grade_row->filter('td')->eq(9)->text() ? (int) $grade_row->filter('td')->eq(9)->text() : null;
            $course_data['bm2_faltas'] = (int) $grade_row->filter('td')->eq(10)->text() ? (int) $grade_row->filter('td')->eq(10)->text() : null;
            $course_data['media'] = (int) $grade_row->filter('td')->eq(11)->text() ? (int) $grade_row->filter('td')->eq(11)->text() : null;
            $course_data['naf_nota'] = (int) $grade_row->filter('td')->eq(12)->text() ? (int) $grade_row->filter('td')->eq(12)->text() : null;
            $course_data['naf_faltas'] = (int) $grade_row->filter('td')->eq(13)->text() ? (int) $grade_row->filter('td')->eq(13)->text() : null;
            $course_data['mfd'] = (int) $grade_row->filter('td')->eq(14)->text() ? (int) $grade_row->filter('td')->eq(14)->text() : null;

            // push data into the $data array
            array_push($data, $course_data);
        }

        return $data;
    }

    public function getStudentData() {
        if (! $this->matricula) {
            $this->doLogin();
        }

        $this->crawler = $this->client->request('GET', $this->aluno_endpoint . $this->matricula . '?tab=dados_pessoais');

        // student data
        $data = [];

        // General data
        $info = $this->crawler->filter('table[class="info"]');

        $data['nome'] = trim($info->filter('td')->eq(1)->text());
        $data['situacao'] = trim($info->filter('td')->eq(3)->text());
        $data['matricula'] = trim($info->filter('td')->eq(5)->text());
        $data['ingresso'] = trim($info->filter('td')->eq(7)->text());
        $data['cpf'] = trim($info->filter('td')->eq(9)->text());
        $data['periodo_referencia'] = (int) trim($info->filter('td')->eq(11)->text());
        $data['ira'] = trim($info->filter('td')->eq(13)->text());
        $data['curso'] = trim($info->filter('td')->eq(15)->text());
        $data['matriz'] = trim($info->filter('td')->eq(17)->text());

        // Contact info
        $contact_info = $this->crawler->filter('.box')->eq(4);

        $data['email_academico'] = trim($contact_info->filter('td')->eq(3)->text());
        $data['email_pessoal'] = trim($contact_info->filter('td')->eq(7)->text());
        $data['telefone'] = trim($contact_info->filter('td')->eq(9)->text());

        return $data;
    }
}
