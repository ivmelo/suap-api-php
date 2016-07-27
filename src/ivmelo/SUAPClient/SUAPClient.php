<?php
require_once 'vendor/autoload.php';

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
            $course_data['diary'] = (int) trim($grade_row->filter('td')->eq(0)->text()) ? (int) trim($grade_row->filter('td')->eq(0)->text()) : null;

            // explode course name and code from the same field
            $namecode = explode(" - ", $grade_row->filter('td')->eq(1)->text());

            // course code without name
            $course_data['course_code'] = trim($namecode[0]);

            // course name without course code
            $course_data['course'] = trim($namecode[1]);

            // get total class-hours for the course
            $course_data['class_hours'] = (int) $grade_row->filter('td')->eq(2)->text() ? (int) $grade_row->filter('td')->eq(2)->text() : null;

            // number or classes given
            $course_data['classes_given'] = (int) $grade_row->filter('td')->eq(3)->text() ? (int) $grade_row->filter('td')->eq(3)->text() : null;
            $course_data['absences'] = (int) $grade_row->filter('td')->eq(4)->text() ? (int) $grade_row->filter('td')->eq(4)->text() : null;
            $course_data['attendance'] = (int) $grade_row->filter('td')->eq(5)->text() ? (int) $grade_row->filter('td')->eq(5)->text() : null;
            $course_data['situation'] = strtolower($grade_row->filter('td')->eq(6)->text()) ? strtolower($grade_row->filter('td')->eq(6)->text()) : null;
            $course_data['bm1_grade'] = (int) $grade_row->filter('td')->eq(7)->text() ? (int) $grade_row->filter('td')->eq(7)->text() : null;
            $course_data['bm1_absences'] = (int) $grade_row->filter('td')->eq(8)->text() ? (int) $grade_row->filter('td')->eq(8)->text() : null;
            $course_data['bm2_grade'] = (int) $grade_row->filter('td')->eq(9)->text() ? (int) $grade_row->filter('td')->eq(9)->text() : null;
            $course_data['bm2_absences'] = (int) $grade_row->filter('td')->eq(10)->text() ? (int) $grade_row->filter('td')->eq(10)->text() : null;
            $course_data['average'] = (int) $grade_row->filter('td')->eq(11)->text() ? (int) $grade_row->filter('td')->eq(11)->text() : null;
            $course_data['n'] = (int) $grade_row->filter('td')->eq(12)->text() ? (int) $grade_row->filter('td')->eq(12)->text() : null;
            $course_data['f'] = (int) $grade_row->filter('td')->eq(13)->text() ? (int) $grade_row->filter('td')->eq(13)->text() : null;
            $course_data['mfd'] = (int) $grade_row->filter('td')->eq(14)->text() ? (int) $grade_row->filter('td')->eq(14)->text() : null;

            // push data into the $data array
            array_push($data, $course_data);
        }

        return $data;
    }

    // TODO: get student data...
    public function getStudentData() {
        if (! $this->matricula) {
            $this->doLogin();
        }

        $this->crawler = $this->client->request('GET', $this->aluno_endpoint . $this->matricula . '?tab=dados_pessoais');

        $info = $this->crawler->filter('table[class="info"]');

        return $info->html();

        //$grades = $this->crawler->filter('table[class="borda"]');
        //$grade_rows = $grades->filter('tbody > tr');
    }
}
