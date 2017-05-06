<?php

namespace Ivmelo\SUAP;

use GuzzleHttp\Client;

/**
 * Access data from SUAP (Sistema Unificado de Administração Pública).
 */
class SUAP
{
    /**
     * The user's access token.
     *
     * @var string
     */
    private $token;

    /**
     * Endpoint for SUAP.
     *
     * @var string
     */
    private $endpoint = 'https://suap.ifrn.edu.br/api/v2/';

    /**
     * A Goutte client to execute http requests.
     *
     * @var GuzzleHttp\Client
     */
    private $client;

    /**
     * Construct function.
     *
     * @param String $token
     */
    public function __construct($token = false)
    {
        if ($token) {
            $this->setToken($token);
        }

        // Create and use a guzzle client instance that will time out after 10 seconds
        $this->client = new Client([
            'timeout'         => 10,
            'connect_timeout' => 10,
        ]);
    }

    /**
     * Authenticate using the user's student ID and password.
     *
     * @param string $username
     * @param string $password
     *
     * @return Array $data
     */
    public function autenticar($username, $password, $access_key = false, $setToken = true)
    {
        $url = $this->endpoint . 'autenticacao/token/';

        $response = $this->client->request('POST', $url, [
            'form_params' => [
                'username' => $username,
                'password' => $password,
            ]
        ]);

        $data = false;

        if ($response->getStatusCode() == 200) {
            // Decode the JSON response in to an array.
            $data = json_decode($response->getBody(), true);

            // Set token if requested to do so. Default is true;
            if ($setToken && isset($data['token'])) {
                $this->setToken($data['token']);
            }
        }

        return $data;
    }

    /**
     * Set's the token for api access.
     *
     * @param String $token
     */
    public function setToken($token) {
        $this->token = $token;
    }

    /**
     * Get personal data for the authenticated student.
     *
     * @return Array $data
     */
    public function getMeusDados() {
        $url = $this->endpoint . 'minhas-informacoes/meus-dados/';
        return $this->doGetRequest($url);
    }

    /**
     * Get report card data for the authenticated student.
     *
     * @return Array $data
     */
    public function getMeuBoletim($year, $term) {
        $url = $this->endpoint . 'minhas-informacoes/boletim/' . $year . '/' . $term . '/';
        return $this->doGetRequest($url);
    }

    /**
     * Get a listing of the student classes for a given term.
     *
     * @return Array $data
     */
    public function getTurmasVirtuais($year, $term) {
        $url = $this->endpoint . 'minhas-informacoes/turmas-virtuais/' . $year . '/' . $term . '/';
        return $this->doGetRequest($url);
    }

    /**
     * Get details about a student class.
     *
     * @return Array $data
     */
    public function getTurmaVirtual($id) {
        $url = $this->endpoint . 'minhas-informacoes/turmas-virtuais/' . $id . '/';
        return $this->doGetRequest($url);
    }


    /**
     * Do a get request to a defined endpoint.
     *
     * @param String $url
     *
     * @return Array $data
     */
    private function doGetRequest($url)
    {
        $response = $this->client->request('GET', $url, [
            'headers' => [
                'Authorization' => 'JWT ' . $this->token,
            ]
        ]);

        $data = false;

        if ($response->getStatusCode() == 200) {
            // Decode the JSON response in to an array.
            $data = json_decode($response->getBody(), true);
        }

        return $data;
    }


    /**
     * Return the weekly schedule of a student.
     *
     * @param Integer $year
     * @param Integer $term
     *
     * @return Array $schedules
     */
    public function getAulas($year, $term)
    {
        $classes = $this->getTurmasVirtuais($year, $term);

        $shifts = [];
        $shifts['M'][1]['time'] = '07:00 - 07:45';
        $shifts['M'][2]['time'] = '07:45 - 08:30';
        $shifts['M'][3]['time'] = '08:50 - 09:35';
        $shifts['M'][4]['time'] = '09:35 - 10:20';
        $shifts['M'][5]['time'] = '10:30 - 11:15';
        $shifts['M'][6]['time'] = '11:15 - 12:00';

        $shifts['V'][1]['time'] = '13:00 - 13:45';
        $shifts['V'][2]['time'] = '13:45 - 14:30';
        $shifts['V'][3]['time'] = '14:40 - 15:25';
        $shifts['V'][4]['time'] = '15:25 - 16:10';
        $shifts['V'][5]['time'] = '16:30 - 17:15';
        $shifts['V'][6]['time'] = '17:15 - 18:00';

        $shifts['N'][1]['time'] = '19:00 - 19:45';
        $shifts['N'][2]['time'] = '19:45 - 20:30';
        $shifts['N'][3]['time'] = '20:40 - 21:25';
        $shifts['N'][4]['time'] = '21:25 - 22:10';


        $schedule = [];
        $schedule[1] = $shifts;
        $schedule[2] = $shifts;
        $schedule[3] = $shifts;
        $schedule[4] = $shifts;
        $schedule[5] = $shifts;
        $schedule[6] = $shifts;
        $schedule[7] = $shifts;

        // Put the data into an array for easy manipulation.
        foreach ($classes as $class) {
            $horarios = explode(' / ', $class['horarios_de_aula']);

            foreach ($horarios as $horario) {

                $day = $horario[0];
                $shift = $horario[1];

                for ($i = 2; $i < strlen($horario); $i++) {
                    $slot = $horario[$i];
                    $schedule[$day][$shift][$slot]['aula'] = $class;
                }
            }
        }

        return $schedule;
    }

}
