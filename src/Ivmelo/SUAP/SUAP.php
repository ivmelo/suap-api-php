<?php

namespace Ivmelo\SUAP;

use GuzzleHttp\Client;

/**
 * Acessa dados do SUAP (Sistema Unificado de Administração Pública).
 *
 * @author Ivanilson Melo <meloivanilson@gmail.com>
 */
class SUAP
{
    /**
     * O token de acesso do usuário. Tokens tem 24 horas de validade.
     *
     * @var string Token de acesso.
     */
    private $token;

    /**
     * Endpoint do SUAP.
     *
     * @var string Endpoint de acesso ao suap.
     */
    private $endpoint = 'https://suap.ifrn.edu.br/api/v2/';

    /**
     * Um cliente GuzzleHttp para fazer os requests HTTP.
     *
     * @var GuzzleHttp\Client Cliente GuzzleHttp.
     */
    private $client;

    /**
     * Construtor. Pode ser vazio ou receber um token de acesso.
     *
     * @param string $token Token de acesso.
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
     * Autentica o usuário e retorna um token de acesso.
     * Pode-se usar a senha ou chave de acesso do aluno.
     *
     * @param string $username  Matrícula do aluno.
     * @param string $password  Senha do aluno ou chave de acesso do responsável.
     * @param bool   $accessKey Define se o login é por chave de acesso.
     * @param bool   $setToken  Define se deve salvar o token para requests subsequentes.
     *
     * @return array $data Array contendo o token de acesso.
     */
    public function autenticar($username, $password, $accessKey = false, $setToken = true)
    {
        // Se estiver acessando com uma chave de acesso...
        if ($accessKey) {
            $url = $this->endpoint.'autenticacao/acesso_responsaveis/';

            $params = [
                'matricula' => $username,
                'chave'     => $password,
            ];
        } else {
            $url = $this->endpoint.'autenticacao/token/';

            $params = [
                'username' => $username,
                'password' => $password,
            ];
        }

        $response = $this->client->request('POST', $url, [
            'form_params' => $params,
        ]);

        $data = false;

        if ($response->getStatusCode() == 200) {
            // Decodifica a resposta JSON para um array.
            $data = json_decode($response->getBody(), true);

            // Seta o token se solicitado. Padrão é true.
            if ($setToken && isset($data['token'])) {
                $this->setToken($data['token']);
            }
        }

        return $data;
    }

    /**
     * Seta o token para acesso a API.
     *
     * @param string $token Token de acesso.
     */
    public function setToken($token)
    {
        $this->token = $token;
    }

    /**
     * Pega os dados pessoais do aluno autenticado.
     *
     * @return array $data Dados pessoais do aluno.
     */
    public function getMeusDados()
    {
        $url = $this->endpoint.'minhas-informacoes/meus-dados/';

        return $this->doGetRequest($url);
    }

    /**
     * Pega os períodos letivos do aluno autenticado.
     *
     * @return array $data Períodos letivos do aluno.
     */
    public function getMeusPeriodosLetivos()
    {
        $url = $this->endpoint.'minhas-informacoes/meus-periodos-letivos/';

        return $this->doGetRequest($url);
    }

    /**
     * Pega o boletim do aluno autenticado.
     *
     * @param int $year Ano letivo.
     * @param int $term Período letivo.
     *
     * @return array $data Boletim do aluno.
     */
    public function getMeuBoletim($year, $term)
    {
        $url = $this->endpoint.'minhas-informacoes/boletim/'.$year.'/'.$term.'/';

        return $this->doGetRequest($url);
    }

    /**
     * Pega a listagem de turmas do aluno para o período solicitado.
     *
     * @param int $year Ano letivo.
     * @param int $term Período letivo.
     *
     * @return array $data Listagem de turmas do aluno.
     */
    public function getTurmasVirtuais($year, $term)
    {
        $url = $this->endpoint.'minhas-informacoes/turmas-virtuais/'.$year.'/'.$term.'/';

        return $this->doGetRequest($url);
    }

    /**
     * Pega detalhes sobre uma turma especificada.
     *
     * @param int $id Id da turma virtual.
     *
     * @return array $data Detalhes da turma virtual.
     */
    public function getTurmaVirtual($id)
    {
        $url = $this->endpoint.'minhas-informacoes/turmas-virtuais/'.$id.'/';

        return $this->doGetRequest($url);
    }

    /**
     * Retorna um array com o horário semanal de um aluno.
     *
     * @param int $year Ano letivo.
     * @param int $term Período letivo.
     *
     * @return array $schedule Horário semanal do aluno.
     */
    public function getHorarios($year, $term)
    {
        $classes = $this->getTurmasVirtuais($year, $term);

        $shifts = [];
        $shifts['M'][1]['hora'] = '07:00 - 07:45';
        $shifts['M'][2]['hora'] = '07:45 - 08:30';
        $shifts['M'][3]['hora'] = '08:50 - 09:35';
        $shifts['M'][4]['hora'] = '09:35 - 10:20';
        $shifts['M'][5]['hora'] = '10:30 - 11:15';
        $shifts['M'][6]['hora'] = '11:15 - 12:00';

        $shifts['V'][1]['hora'] = '13:00 - 13:45';
        $shifts['V'][2]['hora'] = '13:45 - 14:30';
        $shifts['V'][3]['hora'] = '14:40 - 15:25';
        $shifts['V'][4]['hora'] = '15:25 - 16:10';
        $shifts['V'][5]['hora'] = '16:30 - 17:15';
        $shifts['V'][6]['hora'] = '17:15 - 18:00';

        $shifts['N'][1]['hora'] = '19:00 - 19:45';
        $shifts['N'][2]['hora'] = '19:45 - 20:30';
        $shifts['N'][3]['hora'] = '20:40 - 21:25';
        $shifts['N'][4]['hora'] = '21:25 - 22:10';

        $schedule = [];
        $schedule[1] = $shifts;
        $schedule[2] = $shifts;
        $schedule[3] = $shifts;
        $schedule[4] = $shifts;
        $schedule[5] = $shifts;
        $schedule[6] = $shifts;
        $schedule[7] = $shifts;

        // Insere os dados da aula (2M12, 3V34) no local apropriado no array.
        foreach ($classes as $class) {
            $horarios = explode(' / ', $class['horarios_de_aula']);

            foreach ($horarios as $horario) {
                $day = $horario[0];
                $shift = $horario[1];

                $stringSize = strlen($horario);

                for ($i = 2; $i < $stringSize; $i++) {
                    $slot = $horario[$i];
                    $schedule[$day][$shift][$slot]['aula'] = $class;
                }
            }
        }

        return $schedule;
    }

    /**
     * Faz um request GET para um endpoint definido.
     *
     * @param string $url Url para fazer o request.
     *
     * @return array $data Dados retornados pela API.
     */
    private function doGetRequest($url)
    {
        $response = $this->client->request('GET', $url, [
            'headers' => [
                'Authorization' => 'JWT '.$this->token,
            ],
        ]);

        $data = false;

        if ($response->getStatusCode() == 200) {
            $data = json_decode($response->getBody(), true);
        }

        return $data;
    }
}
