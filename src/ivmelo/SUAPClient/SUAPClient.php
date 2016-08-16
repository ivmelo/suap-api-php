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
    private $responsavel_endpoint = 'https://suap.ifrn.edu.br/edu/acesso_responsavel/';
    private $is_access_code = false;

    /**
     * Construct function
     * @param string  $username       Matricula
     * @param string  $password       User's password
     * @param boolean $is_access_code Whether use access code
     */
    function __construct($username = null, $password = null, $is_access_code = false)
    {
        if ($username && $password) {
            $this->username = $username;
            $this->password = $password;
            $this->is_access_code = $is_access_code;
        }

        // guzzle client
        $this->client = new Client();
    }

    /**
     * Sets the credetials for this instance
     * @param string $username       Matricula
     * @param string $password       User's password
     * @param boolean $is_access_code Whether use access code
     */
    public function setCredentials($username, $password, $is_access_code) {
        $this->username = $username;
        $this->password = $password;
        $this->is_access_code = $is_access_code;
    }

    /**
     * Does login for both cases (password or access key)
     */
    public function doLogin() {
        if ($this->is_access_code)
            $this->doResponsavelLogin();
        else
            $this->doAlunoLogin();
    }

    /**
    *  Does login with ID and password
    **/
    public function doAlunoLogin() {
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

    /**
    *  Does login with ID and access key
    **/
    public function doResponsavelLogin() {
        // get csrf token
        $this->crawler = $this->client->request('GET', $this->responsavel_endpoint);
        $token = $this->crawler->filter('input[name="csrfmiddlewaretoken"]');
        $token = $token->attr('value');

        // get form and submit
        $form = $this->crawler->selectButton('Acessar')->form();
        $this->crawler = $this->client->submit($form, [
            'matricula' => $this->username,
            'chave' => $this->password,
            'csrfmiddlewaretoken' => $token
        ]);

        // set matricula
        $info = $this->crawler->filter('table[class="info"]');
        $this->matricula = trim($info->filter('td')->eq(5)->text());
    }

    /**
    *  Get this instance ID
    **/
    public function getMatricula() {
        if (! $this->matricula) {
            $this->doLogin();
        }

        return $this->matricula;
    }

    /**
     * Return the information for all courses for the specified 
     * period/year (default = last period)
     * @param  string $ano_periodo Desired period
     * @return array               Course list
     */
    public function getGrades($ano_periodo = '') {
        // $ano_periodo no formato yyyy_p (ex.: 2015_1)
        if (! $this->matricula) {
            $this->doLogin();
        }

        // Go to the report card page.
        $this->crawler = $this->client->request('GET', $this->aluno_endpoint . $this->matricula . '/?tab=boletim' . '&ano_periodo=' . $ano_periodo);

        // Find grades table.
        $grades = $this->crawler->filter('table[class="borda"]');
        $grade_rows = $grades->filter('tbody > tr');

        // Will store course data;
        $data = [];

        // Loop through course data.
        for ($i = 0; $i < $grade_rows->count(); $i++) {

            $course_data = [];

            // Get the row from the crawler.
            $grade_row = $grades->filter('tbody > tr')->eq($i);

            // Trim white spaces before diary.
            $course_data['diario'] = (int) trim($grade_row->filter('td')->eq(0)->text()) ? (int) trim($grade_row->filter('td')->eq(0)->text()) : null;

            // Explode course name and code from the same field.
            $namecode = explode(" - ", $grade_row->filter('td')->eq(1)->text());

            // Course code without name.
            $course_data['codigo'] = trim($namecode[0]);

            // Course name without course code.
            $course_data['disciplina'] = trim($namecode[1]);

            // Get total class-hours for the course.
            $course_data['carga_horaria'] = (int) $grade_row->filter('td')->eq(2)->text() ? (int) $grade_row->filter('td')->eq(2)->text() : null;

            // Number or classes given.
            $course_data['aulas'] = (int) $grade_row->filter('td')->eq(3)->text() ? (int) $grade_row->filter('td')->eq(3)->text() : null;

            // Absences.
            $course_data['faltas'] = (int) $grade_row->filter('td')->eq(4)->text() ? (int) $grade_row->filter('td')->eq(4)->text() : null;

            // Attendance.
            $course_data['frequencia'] = (int) $grade_row->filter('td')->eq(5)->text() ? (int) $grade_row->filter('td')->eq(5)->text() : null;

            // Situation.
            $course_data['situacao'] = strtolower($grade_row->filter('td')->eq(6)->text()) ? strtolower($grade_row->filter('td')->eq(6)->text()) : null;

            // First bimester, grade.
            try {
                $course_data['bm1_nota'] = (int) $grade_row->filter('td')->eq(7)->text() ? (int) $grade_row->filter('td')->eq(7)->text() : null;
            } catch (\Exception $e) {
                $course_data['bm1_nota'] = null;
            }

            // First bimester, absences.
            try {
                $course_data['bm1_faltas'] = (int) $grade_row->filter('td')->eq(8)->text() ? (int) $grade_row->filter('td')->eq(8)->text() : null;
            } catch (\Exception $e) {
                $course_data['bm1_faltas'] = null;
            }

            // Second bimester, grade.
            try {
                $course_data['bm2_nota'] = (int) $grade_row->filter('td')->eq(9)->text() ? (int) $grade_row->filter('td')->eq(9)->text() : null;
            } catch (\Exception $e) {
                $course_data['bm2_nota'] = null;
            }

            // Second bimester, absences.
            try {
                $course_data['bm2_faltas'] = (int) $grade_row->filter('td')->eq(10)->text() ? (int) $grade_row->filter('td')->eq(10)->text() : null;
            } catch (\Exception $e) {

            }

            // Average (grade).
            try {
                $course_data['media'] = (int) $grade_row->filter('td')->eq(11)->text() ? (int) $grade_row->filter('td')->eq(11)->text() : null;
            } catch (\Exception $e) {
                $course_data['media'] = null;
            }

            // NAF Grade.
            try {
                $course_data['naf_nota'] = (int) $grade_row->filter('td')->eq(12)->text() ? (int) $grade_row->filter('td')->eq(12)->text() : null;
            } catch (\Exception $e) {
                $course_data['naf_nota'] = null;
            }

            // NAF absences.
            try {
                $course_data['naf_faltas'] = (int) $grade_row->filter('td')->eq(13)->text() ? (int) $grade_row->filter('td')->eq(13)->text() : null;
            } catch (\Exception $e) {
                $course_data['naf_faltas'] = null;
            }

            // Final grade.
            try {
                $course_data['mfd'] = (int) $grade_row->filter('td')->eq(14)->text() ? (int) $grade_row->filter('td')->eq(14)->text() : null;
            } catch (\Exception $e) {
                $course_data['mfd'] = null;
            }

            // Push data into the $data array.
            array_push($data, $course_data);
        }

        return $data;
    }

    /**
     * Returns a lists of all courses for the specified period/year (default = last period)
     * @param  string $ano_periodo Desired period
     * @return array               Course list
     */
    public function getCourses($ano_periodo = ''){
        // $ano_periodo no formato yyyy_p (ex.: 2015_1)
        if (! $this->matricula) {
            $this->doLogin();
        }
        // Go to grades page.
        $this->crawler = $this->client->request('GET', $this->aluno_endpoint . $this->matricula . '/?tab=boletim' . '&ano_periodo=' . $ano_periodo);
        // Get a crawler for the grades table.
        $grades = $this->crawler->filter('table[class="borda"]');
        $grade_rows = $grades->filter('tbody > tr');
        // Course data.
        $data = [];

        for ($i = 0; $i < $grade_rows->count(); $i++) {
          $course_data = [];
          $grade_row = $grades->filter('tbody > tr')->eq($i);

          // Explode course name and code from the same field.
          $namecode = explode(" - ", $grade_row->filter('td')->eq(1)->text());
          // Course code without name.
          $course_data['codigo'] = trim($namecode[0]);
          // Course name without course code.
          $course_data['disciplina'] = trim($namecode[1]);

          array_push($data, $course_data);
        }

        return $data;
    }

    /**
     * Gets the info for a specified course and period.
     * @param  string $course_code Course code
     * @param  string $ano_periodo Desired period
     * @return array               Course list
     */
    public function getCourseData($course_code = "", $ano_periodo = ''){
        // Uses getGrades function as helper.
        $courses = $this->getGrades($ano_periodo);
        $data = [];

        if ($course_code != ""){
            // Loop through courses to find a specific one.
            foreach ($courses as $course) {
                if ($course['codigo'] == $course_code){
                    $data = $course;
                }
            }
        } else {
            // Gets the first one in the list.
            $data = $courses[0];
        }

        return $data;
    }

    /**
     * Returns a list of courses filtered by name
     * @param  string $ano_periodo  Desired period
     * @param  string $course_names List of course names
     * @return array                List of filtered courses
     */
    public function filterCoursesByName($ano_periodo = '', $course_names)
    {
        // Uses getGrades function as helper.
        $courses = $this->getGrades($ano_periodo);
        // removes trailing white spaces and sets regex
        $course_names = '/'.str_replace(' ', ' | ', trim($course_names)).'/'
        $data = [];

        foreach ($courses as $course) {
            if ( preg_match($course_names, $course['disciplina']) ){
                array_push($data, $course)
            }
        }
        return $data;
    }

    /**
    *  Gets student data.
    **/
    public function getStudentData() {
        if (! $this->matricula) {
            $this->doLogin();
        }

        $this->crawler = $this->client->request('GET', $this->aluno_endpoint . $this->matricula . '?tab=dados_pessoais');

        // student data
        $data = [];

        // General data
        $info = $this->crawler->filter('table[class="info"]');

        // Personal data.
        $data['nome'] = trim($info->filter('td')->eq(1)->text());
        $data['cpf'] = trim($info->filter('td')->eq(9)->text());

        // Academic data.
        $data['situacao'] = trim($info->filter('td')->eq(3)->text());
        $data['matricula'] = trim($info->filter('td')->eq(5)->text());
        $data['ingresso'] = trim($info->filter('td')->eq(7)->text());
        $data['periodo_referencia'] = (int) trim($info->filter('td')->eq(11)->text());
        $data['ira'] = trim($info->filter('td')->eq(13)->text());
        $data['curso'] = trim($info->filter('td')->eq(15)->text());
        $data['matriz'] = trim($info->filter('td')->eq(17)->text());

        // Contact info.
        $contact_info = $this->crawler->filter('.box')->eq(4);

        $data['email_academico'] = trim($contact_info->filter('td')->eq(3)->text());
        $data['email_pessoal'] = trim($contact_info->filter('td')->eq(7)->text());
        $data['telefone'] = trim($contact_info->filter('td')->eq(9)->text());

        return $data;
    }
}
