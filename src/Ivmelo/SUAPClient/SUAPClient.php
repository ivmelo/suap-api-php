<?php

namespace Ivmelo\SUAPClient;

use Goutte\Client;
use Symfony\Component\DomCrawler\Crawler;

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
     * Construct function.
     *
     * @param string $username       Matricula
     * @param string $password       User's password
     * @param bool   $is_access_code Whether use access code
     */
    public function __construct($username = null, $password = null, $is_access_code = false)
    {
        if ($username && $password) {
            $this->username = $username;
            $this->password = $password;
            $this->is_access_code = $is_access_code;
        }

        // Goutte client
        $this->client = new Client();

        // Create and use a guzzle client instance that will time out after 10 seconds
        $guzzle_client = new \GuzzleHttp\Client([
            'timeout'         => 10,
            'connect_timeout' => 10,
        ]);

        $this->client->setClient($guzzle_client);
    }

    /**
     * Sets the credetials for this instance.
     *
     * @param string $username       Matricula
     * @param string $password       User's password
     * @param bool   $is_access_code Whether use access code
     */
    public function setCredentials($username, $password, $is_access_code)
    {
        $this->username = $username;
        $this->password = $password;
        $this->is_access_code = $is_access_code;
    }

    /**
     * Does login according to the type of user.
     */
    public function doLogin()
    {
        if ($this->is_access_code) {
            $this->doResponsavelLogin();
        } else {
            $this->doAlunoLogin();
        }
    }

    /**
     *  Does login with ID and password.
     **/
    public function doAlunoLogin()
    {
        // get csrf token
        $this->crawler = $this->client->request('GET', $this->endpoint);
        $token = $this->crawler->filter('input[name="csrfmiddlewaretoken"]');
        $token = $token->attr('value');

        // get form and submit
        $form = $this->crawler->selectButton('Acessar')->form();
        $this->crawler = $this->client->submit($form, [
            'username'            => $this->username,
            'password'            => $this->password,
            'csrfmiddlewaretoken' => $token,
        ]);

        // get matricula number
        $meusdados_link = $this->crawler->selectLink('Meus Dados')->link()->getUri();
        $link_parts = explode('/', $meusdados_link);
        $this->matricula = $link_parts[5];
    }

    /**
     *  Does login with ID and access key.
     **/
    public function doResponsavelLogin()
    {
        // get csrf token
        $this->crawler = $this->client->request('GET', $this->responsavel_endpoint);
        $token = $this->crawler->filter('input[name="csrfmiddlewaretoken"]');
        $token = $token->attr('value');

        // get form and submit
        $form = $this->crawler->selectButton('Acessar')->form();
        $this->crawler = $this->client->submit($form, [
            'matricula'           => $this->username,
            'chave'               => $this->password,
            'csrfmiddlewaretoken' => $token,
        ]);

        // set matricula
        $info = $this->crawler->filter('table[class="info"]');
        $this->matricula = trim($info->filter('td')->eq(5)->text());
    }

    /**
     * Get this instance ID.
     *
     * @return string Student ID.
     **/
    public function getMatricula()
    {
        if (!$this->matricula) {
            $this->doLogin();
        }

        return $this->matricula;
    }

    /**
     * Return class information, schedule and location.
     *
     * @param  ano_periodo   year/term in yyyy.t format. Ex. '2016.1'.
     *
     * @return array Class info.
     */
    public function getClasses($ano_periodo = null)
    {
        if (!$this->matricula) {
            $this->doLogin();
        }

        // Endpoint.
        $url = $this->aluno_endpoint.$this->matricula.'/?tab=locais_aula_aluno';

        // If year/term is passed.
        if ($ano_periodo) {
            $url .= '&ano-periodo='.$ano_periodo;
        }

        // Go to the report card page.
        $this->crawler = $this->client->request('GET', $url);

        $courses_data = $this->getCoursesData($this->crawler);

        return $courses_data;
    }

    /**
     * Get courses information.
     *
     * @param Symfony\Component\DomCrawler\Crawler $crawler Crawler for the class schedule page.
     *
     * @return array Course information.
     */
    private function getCoursesData(Crawler $crawler)
    {
        $courses = $crawler->filter('table')->eq(1);

        $data = [];

        $rows = $courses->filter('tbody > tr')->count();

        for ($i = 0; $i < $rows; $i++) {
            $class_row = $courses->filter('tbody > tr')->eq($i);
            $class_data = [];

            // Diary, will be used as key.
            $class_data['diario'] = trim($class_row->filter('td')->eq(0)->text());

            // Component data.
            try {
                $componente = trim($class_row->filter('td dd')->eq(0)->text());
                $componente_data = explode(' - ', $componente);

                $class_data['codigo'] = $componente_data[0];
                $class_data['disciplina'] = $componente_data[1];
                $class_data['tipo'] = $componente_data[2];

                // Local, horário...
                $class_data['local'] = trim($class_row->filter('td')->eq(2)->text());
                $class_data['horario'] = trim($class_row->filter('td')->eq(3)->text());
            } catch (\Exception $e) {
            }

            // Not every course has registered instructors. Some course are assigned instructors later.
            try {
                $class_data['professores'] = array_map('trim', explode(',', trim($class_row->filter('td dd')->eq(1)->text())));
            } catch (\Exception $e) {
                $class_data['professores'] = [];
            }

            // If set, use diario as array key.
            if (isset($class_data['codigo']) && !empty($class_data['codigo'])) {
                $data[$class_data['codigo']] = $class_data;
            }
        }

        // If the user is not registered in courses, will return an empty array.
        return $data;
    }

    /**
     * Return the information for all courses for the specified
     * The default year/period is "chosen" by suap. Usually last period.
     *
     * @param string $ano_periodo Desired period
     *
     * @return array Course list
     */
    public function getGrades($ano_periodo = '')
    {
        if (!$this->matricula) {
            $this->doLogin();
        }

        // Replaces . for _ to make it compatible with the get schedule method.
        // Besides, it's way more common to write 2015.2 than 2015_2.
        $ano_periodo = str_replace('.', '_', $ano_periodo);

        // Go to the report card page.
        $this->crawler = $this->client->request('GET', $this->aluno_endpoint.$this->matricula.'/?tab=boletim'.'&ano_periodo='.$ano_periodo);

        // Find grades table.
        $grades = $this->crawler->filter('table[class="borda"]');
        $grade_rows = $grades->filter('tbody > tr');

        // Will store course data;
        $data = [];

        // Rows count.
        $rows = $grade_rows->count();

        // Loop through course data.
        for ($i = 0; $i < $rows; $i++) {

            // Web scraping sux. Pls, IF, kindly release Rest API. Thx.
            $course_data = [];

            // Get the row from the crawler.
            $grade_row = $grades->filter('tbody > tr')->eq($i);

            // Get column count.
            $columns = $grade_row->filter('td')->count();

            // Trim white spaces before diary.
            $course_data['diario'] = (int) trim($grade_row->filter('td')->eq(0)->text()) ? (int) trim($grade_row->filter('td')->eq(0)->text()) : null;

            // Explode course name and code from the same field.
            $namecode = explode(' - ', $grade_row->filter('td')->eq(1)->text());

            // Course code without name.
            $course_data['codigo'] = trim($namecode[0]);

            // Course name without course code.
            $course_data['disciplina'] = trim($namecode[1]);

            // Get total class-hours for the course.
            $course_data['carga_horaria'] = $this->getFieldValue($grade_row->filter('td')->eq(2)->text());

            // Number or classes given.
            $course_data['aulas'] = $this->getFieldValue($grade_row->filter('td')->eq(3)->text());

            // Absences.
            $course_data['faltas'] = $this->getFieldValue($grade_row->filter('td')->eq(4)->text());

            // Attendance.
            $course_data['frequencia'] = $this->getFieldValue($grade_row->filter('td')->eq(5)->text());

            // Situation.
            $course_data['situacao'] = strtolower($grade_row->filter('td')->eq(6)->text()) ? strtolower($grade_row->filter('td')->eq(6)->text()) : null;

            // High school students might have 2 bimester or 4 bimester courses.
            // That causes their report card to have more columns than the ones of college students.
            // To deal with that, we'll create an $node_number variable to adjust the node number accordingly.
            // When they have a 2 bimester course, their report card have 17 colums.
            // Some of those courses start in the 3rd bimester, well consider that later on.

            // Get if it's a high school course.
            $is_high_school = ($columns != 16);

            // Get wether the course skips the first and second bimester. (HIGH SCHOOL STUDENTS ONLY).
            $skips_12bm = ($grade_row->filter('td')->eq(7)->attr('colspan') == '4');

            // Get wether the course doesn't have third and fourth bimesters.
            $skips_34bm = ($grade_row->filter('td')->eq(11)->attr('colspan') == '4');

            // Next node number. Will be adjusted according to the type of course,
            // Number of bimesters and wether they skip the 1st 2nd or 3rd 4th bimesters.
            $node_number = 7;

            // Some high school courses, skips the 1st and 2nd bimesters.
            // If this course doesn't skip the 1st and 2nd bms...
            if (!$skips_12bm) {

                // First bimester, grade.
                try {
                    $course_data['bm1_nota'] = $this->getFieldValue($grade_row->filter('td')->eq($node_number++)->text());
                } catch (\Exception $e) {
                    $course_data['bm1_nota'] = null;
                }

                // First bimester, absences.
                try {
                    $course_data['bm1_faltas'] = $this->getFieldValue($grade_row->filter('td')->eq($node_number++)->text());
                } catch (\Exception $e) {
                    $course_data['bm1_faltas'] = null;
                }

                // Second bimester, grade.
                try {
                    $course_data['bm2_nota'] = $this->getFieldValue($grade_row->filter('td')->eq($node_number++)->text());
                } catch (\Exception $e) {
                    $course_data['bm2_nota'] = null;
                }

                // Second bimester, absences.
                try {
                    $course_data['bm2_faltas'] = $this->getFieldValue($grade_row->filter('td')->eq($node_number++)->text());
                } catch (\Exception $e) {
                    $course_data['bm2_faltas'] = null;
                }

                // Skips 3rd and 4th bimesters. Goes straight to "média".
                if ($skips_34bm) {
                    $node_number = 12;
                }
            }

            // Some hich school courses only have classes during the 1st and 2nd bimesters.
            if ($is_high_school && !$skips_34bm) {
                if ($skips_12bm) {
                    $node_number = 8;
                }

                // Third bimester, grade.
                try {
                    $course_data['bm3_nota'] = $this->getFieldValue($grade_row->filter('td')->eq($node_number++)->text());
                } catch (\Exception $e) {
                    $course_data['bm3_nota'] = null;
                }

                // Third bimester, absences.
                try {
                    $course_data['bm3_faltas'] = $this->getFieldValue($grade_row->filter('td')->eq($node_number++)->text());
                } catch (\Exception $e) {
                    $course_data['bm3_faltas'] = null;
                }

                // Fourth bimester, grade.
                try {
                    $course_data['bm4_nota'] = $this->getFieldValue($grade_row->filter('td')->eq($node_number++)->text());
                } catch (\Exception $e) {
                    $course_data['bm4_nota'] = null;
                }

                // Fourth bimester, absences.
                try {
                    $course_data['bm4_faltas'] = $this->getFieldValue($grade_row->filter('td')->eq($node_number++)->text());
                } catch (\Exception $e) {
                    $course_data['bm4_faltas'] = null;
                }
            }

            // Average (grade).
            try {
                $course_data['media'] = $this->getFieldValue($grade_row->filter('td')->eq($node_number++)->text());
            } catch (\Exception $e) {
                $course_data['media'] = null;
            }

            // NAF Grade.
            try {
                $course_data['naf_nota'] = $this->getFieldValue($grade_row->filter('td')->eq($node_number++)->text());
            } catch (\Exception $e) {
                $course_data['naf_nota'] = null;
            }

            // NAF absences.
            try {
                $course_data['naf_faltas'] = $this->getFieldValue($grade_row->filter('td')->eq($node_number++)->text());
            } catch (\Exception $e) {
                $course_data['naf_faltas'] = null;
            }

            // Final grade.
            try {
                $course_data['mfd'] = $this->getFieldValue($grade_row->filter('td')->eq($node_number++)->text());
            } catch (\Exception $e) {
                $course_data['mfd'] = null;
            }

            // Push data into the $data array.
            array_push($data, $course_data);
        }

        return $data;
    }

    /**
     * Parse a report card field into an integer. Returns null if the field is a dash or empty.
     *
     * @param string $field Report card field.
     *
     * @return mixed Field int value or null.
     */
    private function getFieldValue($field)
    {
        if (trim($field) == '-' || trim($field) == '') {
            return;
        }

        return (int) trim($field);
    }

    /**
     * Returns a lists of all courses for the specified period/year (default = last period).
     *
     * @param string $ano_periodo Desired period
     *
     * @return array Course list
     */
    public function getCourses($ano_periodo = '')
    {
        // $ano_periodo no formato yyyy_p (ex.: 2015_1)
        if (!$this->matricula) {
            $this->doLogin();
        }
        // Go to grades page.
        $this->crawler = $this->client->request('GET', $this->aluno_endpoint.$this->matricula.'/?tab=boletim'.'&ano_periodo='.$ano_periodo);
        // Get a crawler for the grades table.
        $grades = $this->crawler->filter('table[class="borda"]');
        $grade_rows = $grades->filter('tbody > tr');
        // Course data.
        $data = [];

        // Count rows.
        $rows = $grade_rows->count();

        for ($i = 0; $i < $rows; $i++) {
            $course_data = [];
            $grade_row = $grades->filter('tbody > tr')->eq($i);

          // Explode course name and code from the same field.
          $namecode = explode(' - ', $grade_row->filter('td')->eq(1)->text());
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
     *
     * @param string $course_code Course code
     * @param string $ano_periodo Desired period
     *
     * @return array Course list
     */
    public function getCourseData($course_code = '', $ano_periodo = '')
    {
        // Uses getGrades function as helper.
        $courses = $this->getGrades($ano_periodo);
        $data = [];

        if ($course_code != '') {
            // Loop through courses to find a specific one.
            foreach ($courses as $course) {
                if ($course['codigo'] == $course_code) {
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
     * Returns a list of courses filtered by name.
     *
     * @param string $ano_periodo  Desired period
     * @param string $course_names List of course names
     *
     * @return array List of filtered courses
     */
    public function filterCoursesByName($course_names, $ano_periodo = '')
    {
        // Uses getGrades function as helper.
        $courses = $this->getGrades($ano_periodo);
        // removes trailing white spaces and sets regex
        $course_names = '/'.str_replace(' ', '|', strtolower(trim($course_names))).'/';
        $data = [];

        foreach ($courses as $course) {
            if (preg_match($course_names, strtolower($course['disciplina']))) {
                array_push($data, $course);
            }
        }

        return $data;
    }

    /**
     * Gets student data.
     *
     * @return array Student personal data.
     **/
    public function getStudentData()
    {
        if (!$this->matricula) {
            $this->doLogin();
        }

        $this->crawler = $this->client->request('GET', $this->aluno_endpoint.$this->matricula.'?tab=dados_pessoais');

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
        $data['ira'] = floatval(str_replace(',', '.', trim($info->filter('td')->eq(13)->text())));
        $data['curso'] = trim($info->filter('td')->eq(15)->text());
        $data['matriz'] = trim($info->filter('td')->eq(17)->text());

        // Contact info.
        $contact_info = $this->crawler->filter('.box')->eq(4);

        $data['email_academico'] = trim($contact_info->filter('td')->eq(3)->text());
        $data['email_pessoal'] = trim($contact_info->filter('td')->eq(7)->text());
        $data['telefone'] = trim($contact_info->filter('td')->eq(9)->text());

        // We won't scrap addresses and other more personal info.

        return $data;
    }

    /**
     * Returns class schedule for a given day of the week.
     *
     * @param int    $today       Day of the week (1 for sunday, 2 for monday, 3 for tuesday...)
     * @param string $ano_periodo year/term in yyyy.t format. Ex. '2016.1'.
     *
     * @return array Class schedule for moning, afternoon and evening courses.
     */
    public function getSchedule($today = null, $ano_periodo = null)
    {
        if (!$this->matricula) {
            $this->doLogin();
        }

        // Endpoint.
        $url = $this->aluno_endpoint.$this->matricula.'/?tab=locais_aula_aluno';

        // If year/term is passed.
        if ($ano_periodo) {
            $url .= '&ano-periodo='.$ano_periodo;
        }

        // Get data from schedule page.
        $this->crawler = $this->client->request('GET', $url);
        $tables = $this->crawler->filter('.box')->eq(2)->filter('table');

        // No day given. Use today.
        if (!$today) {
            $today = date('w') + 1;
        }

        // Make day of the week start on sunday.
        if ($today == 1) {
            $today = 8;
        }

        // The table has an offset for the times.
        $today--;

        // Scrap schedule data from tables.
        $data = [];
        $tables->each(function (Crawler $table) use (&$data, $today) {
            $turno = trim($table->filter('thead')->filter('th')->eq(0)->text());
            $table->filter('tbody')->filter('tr')->each(function (Crawler $tr) use (&$data, $turno, $today) {
                $data[strtolower($turno)][trim($tr->filter('td')->eq(0)->text())] = trim($tr->filter('td')->eq($today)->text()) ? trim($tr->filter('td')->eq($today)->text()) : null;
            });
        });

        $courses_data = $this->getCoursesData($this->crawler);

        if (!empty($courses_data)) {
            // Replace course codes with class details.
            foreach ($data as $shift => $hours) {
                foreach ($data[$shift] as $time => $course) {
                    if ($course) {
                        $data[$shift][$time] = $courses_data[$course];
                    }
                }
            }
        } else {
            return [];
        }

        return $data;
    }

    /**
     * Returns class schedule for a week.
     *
     * @return array Class schedule for moning, afternoon and evening courses for each day.
     */
    public function getWeekSchedule()
    {
        if (!$this->matricula) {
            $this->doLogin();
        }

        // Get data from schedule page.
        $this->crawler = $this->client->request('GET', $this->aluno_endpoint.$this->matricula.'?tab=locais_aula_aluno');

        $tables = $this->crawler->filter('.box')->eq(2)->filter('table');
        $courses_data = $this->getCoursesData($this->crawler);

        // Scrap schedule data from tables.
        $data = [];
        $tables->each(function (Crawler $table) use (&$data, $courses_data) {
            $turno = strtolower(trim($table->filter('thead')->filter('th')->eq(0)->text()));
            $table->filter('tbody')->filter('tr')->each(function (Crawler $tr) use (&$data, $turno, &$index, $courses_data) {
                $horario = trim($tr->filter('td')->eq(0)->text());
                $index = 0;
                $tr->filter('td')->each(function (Crawler $td) use (&$data, &$index, $turno, $horario, $courses_data) {
                    if ($index > 0) {
                        if (isset($courses_data[trim($td->text())])) {
                            $data[($index % 7) + 1][$turno][$horario] = $courses_data[trim($td->text())];
                        } else {
                            $data[($index % 7) + 1][$turno][$horario] = trim($td->text());
                        }
                    }
                    $index++;
                });
            });
        });

        return $data;
    }
}
