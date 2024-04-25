<?php

namespace App\Controllers;

use CodeIgniter\HTTP\CLIRequest;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;
use CodeIgniter\RESTful\ResourceController;

/**
 * Class BaseController
 *
 * BaseController provides a convenient place for loading components
 * and performing functions that are needed by all your controllers.
 *
 * For security be sure to declare any new methods as protected or private.
 */
abstract class BaseRESTController extends ResourceController
{
    /**
     * Instance of the main Request object.
     *
     * @var CLIRequest|IncomingRequest
     */
    protected $request;

    /**
     * An array of helpers to be loaded automatically upon
     * class instantiation. These helpers will be available
     * to all other controllers that extend BaseController.
     *
     * @var array
     */
    protected $helpers = ["funcoes"];

    /**
     * Guarda os parâmetros POST recebidos da requisição
     *
     * @var array
     */
    protected $post;

    /**
     * Constructor.
     */
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        session_cache_expire(0);
        parent::initController($request, $response, $logger);

        //Obtem o RAW Input da requisição
        $aux = file_get_contents('php://input');
        if(validaJson($aux)) {
            $aux = json_decode($aux, true);
        } else {
            $aux = array();
        }

        //Adicionamos valor para nossa váriavel de classe POST
        $this->post = array_merge($aux, $this->request->getPost());
        $aux = null;

        //Limpamos os POST's enviados para mantermos somente nos parametros da controller
        //Não limpamos os FILES pois pode ocasionar problemas
        $_POST = array();
        $this->request->setGlobal("post", null);

        // E.g.: $this->session = \Config\Services::session();
    }

    /**
     * Method exitDefault
     * Método de retorno de informações ao WS, padrão de mensagem
     *
     * @param string|null $codigoErro Código de erro para retorno (Se nulo, ignora mensagens de erro no retorno)
     * @param string|null $mensagemErro Mensagem de erro para retorno (Se nulo, ignora mensagens de erro no retorno)
     * @param string|array|null $dadosRetorno Dados para serem retornados no WS
     * @param array $opts Opções de retorno:
     *                      - http: Define o código HTTP para ser transmitido na header
     *
     * @return void
     */
    private function exitDefault($codigoErro, $mensagemErro, $dadosRetorno, $opts = array()) {
        header('Content-Type: application/json; charset=utf-8');

        $hasError = false;
        if(empty($opts)) {
            $opts = array();
        }

        $ret = array(
            'dateServer' => dataAgoraFormatada("Y-m-d H:i:s"),
            'data' => $dadosRetorno,
            'error' => array(
                'code' => $codigoErro,
                'msg' => $mensagemErro,
            ),
        );
        if(empty($codigoErro) || empty($mensagemErro)) {
            $ret['error'] = null;
        } else {
            $hasError = true;
            $ret['error']['code'] = (string) $ret['error']['code'];
            $ret['error']['msg'] = decodificaTexto($ret['error']['msg']);
            $ret['data'] = null;
        }

        //Tratativa para atender retornos de erros diretamente nos constructs de controllers
        $strRet = @json_encode($ret, JSON_INVALID_UTF8_SUBSTITUTE);
        if(json_last_error() !== JSON_ERROR_NONE) {
            $hasError = true;
            $ret['data'] = null;

            $ret['error']['code'] = ("JSON_" . json_last_error());
            $ret['error']['msg'] = json_last_error_msg();
            $strRet = json_encode($ret, true);
        }
        $ret = NULL;

        if($hasError) {
            //Por padrão, em caso de erro, definimos o código HTTP 400 na página
            $opts['http'] = !empty($opts['http']) ? $opts['http'] : 400;

            //Em caso de erro, definimos o código HTTP na página
            header_status($opts['http']);
        }
        echo $strRet;
        $strRet = null;
        exit(0);
    }

    /**
     * Method exitSuccess
     * Método de retorno de informações ao WS, padrão de mensagem de sucesso
     *
     * @param string|array|null $dadosRetorno Dados para serem retornados no WS
     * @param array $opts Opções de retorno:
     *
     * @return void
     */
    protected function exitSuccess($dadosRetorno, $opts = array()) {
        $this->exitDefault(null, null, $dadosRetorno, $opts);
    }

    /**
     * Method exitError
     * Método de retorno de informações ao WS, padrão de mensagem de erro
     *
     * @param string|null $codigoErro Código de erro para retorno (Se nulo, ignora mensagens de erro no retorno)
     * @param string|null $mensagemErro Mensagem de erro para retorno (Se nulo, ignora mensagens de erro no retorno)
     * @param array $opts Opções de retorno:
     *                      - http: Força um erro HTTP especifico. DEFAULT: 400.
     *
     * @return void
     */
    protected function exitError($codigoErro, $mensagemErro, $opts = array()) {
        if(empty($opts)) {
            $opts = array();
        }
        unset($opts['code']);
        unset($opts['msg']);

        $this->exitDefault($codigoErro, $mensagemErro, null, $opts);
    }
}
