<?php

namespace App\Controllers\Admin;

use \App\Controllers\BaseRESTController;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * Class BusinessPartner
 * Retorno de dados relacionados aos registros de parceiros
 */
class BusinessPartner extends BaseRESTController {
    /**
     * Váriavel responsável por guardar a instância do model Admin\BusinessPartnerModel
     *
     * @var \App\Models\Admin\BusinessPartnerModel
     */
    protected $recordModel;

    /**
     * Constructor.
     */
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger) {
        parent::initController($request, $response, $logger);
        $this->recordModel = new \App\Models\Admin\BusinessPartnerModel();
    }

    /**
     * Method getData
     * Obtêm o(s) parceiro(s) cadastrado(s)
     *
     * @param string|null $id ID do registro para consulta. Se não enviado, todos os registros serão obtidos
     *
     * @return void
     */
    public function getData($id = null) {
        $id = getFilteredKey($id, null, DFL_FILTERED_GET_STR_KEYS_OPTS);
        $ret = $this->recordModel->search($id);

        if(!empty($this->recordModel->getLastError())) {
            $ret = $this->recordModel->getLastError();
            $this->exitError($ret['code'], $ret['msg'], $ret);
        }

        $this->exitSuccess($ret);
    }

    /**
     * Method saveData
     * Salva ou atualiza os dados de um parceiro
     *
     * @param string|null $id ID do registro para atualizar os dados. Se não enviado, um novo registro será criado
     *
     * @return void
     */
    public function saveData($id = null) {
        $id = getFilteredKey($id, null, DFL_FILTERED_GET_STR_KEYS_OPTS);
        $dados = getFilteredKey($this->post, null, DFL_FILTERED_POST_KEYS_OPTS);

        $ret = $this->recordModel->saveData($dados, $id);
        if(!empty($this->recordModel->getLastError())) {
            $ret = $this->recordModel->getLastError();
            $this->exitError($ret['code'], $ret['msg'], $ret);
        }
        $this->exitSuccess($ret);
    }

    /**
     * Method remove
     * Deleta um parceiro cadastrado
     *
     * @param string $id ID do registro para deletar
     *
     * @return void
     */
    public function remove($id) {
        $id = getFilteredKey($id, null, DFL_FILTERED_GET_STR_KEYS_OPTS);

        $ret = $this->recordModel->remove($id);
        if(!empty($this->recordModel->getLastError())) {
            $ret = $this->recordModel->getLastError();
            $this->exitError($ret['code'], $ret['msg'], $ret);
        }
        $this->exitSuccess($ret);
    }
}