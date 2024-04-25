<?php

namespace App\Models\Admin;

use App\Models\Admin\Auxiliary\LogsModel;
use App\Models\BaseModel;
use function App\Models\Admin\Registrations\configureDefaultParamsSearch;
use function App\Models\Admin\Registrations\configureDefaultParamsSearchByScore;
use function App\Models\Admin\Registrations\createQuerySearchElements;
use function App\Models\Admin\Registrations\getDefaultRetSearchByScore;
use function App\Models\Admin\Registrations\getLimitPartQuerys;
use function App\Models\Admin\Registrations\hasFullAccess;
use const App\Models\Admin\Registrations\SYSTEM_LOGS_TYPE_DELETE;
use const App\Models\Admin\Registrations\SYSTEM_LOGS_TYPE_INSERT;
use const App\Models\Admin\Registrations\SYSTEM_LOGS_TYPE_UPDATE;

/**
 * Class BusinessPartnerModel
 * Manipulação de dados relacionados aos registros de parceiros
 */
class BusinessPartnerModel extends BaseModel {
    /**
     * Define o segmento da URL para envio das requisições
     * @var string
     */
    private $urlSegment = "BusinessPartners";

    /**
     * Called during initialization. Appends
     * our custom field to the module's model.
     */
    protected function initialize() {
        // Do Not Edit This Line
        parent::initialize();
    }

    /**
     * Method normalizeData
     * Normaliza os dados de um parceiro retornado
     *
     * @param array $item Item para ser normalizado
     *
     * @return array
     */
    private function normalizeData($item) {
        $aux = explode(".", $item['createdAt'])[0];
        $aux = explode("T", $aux);
        $item['createdAt'] = $aux[0] . " " . $aux[1];
        $aux = null;

        $item['createdAtBR'] = dataParaDataBrPorExtenso($item['createdAt']);
        return $item;
    }

    /**
     * Method search
     * Retorna os registros do sistema
     *
     * @param string|null $id ID do registro para consulta. Se não enviado, todos os registros serão obtidos
     *
     * @return mixed
     */
    public function search($id = null) {
        $url = WS_BASE_URL . $this->urlSegment;
        if(!empty($id)) {
            $url .= "/" . $id;
        }

        $ret = json_decode(chamarWS($url, "GET"), true);
        if(!empty($ret) && empty($ret['cError'])) {
            if(!empty($id)) {
                $ret = $this->normalizeData($ret);
            } else {
                $key = 0;
                $item = array();
                foreach ($ret AS $key => $item) {
                    $ret[$key] = $this->normalizeData($item);
                }
                $key = null;
                $item = null;
            }
        } else {
            $ret = array();
            if(!empty($id)) {
                $ret = (object) $ret;
            }
        }

        return $ret;
    }

    /**
     * Method saveData
     * Salva ou atualiza o registro enviado
     *
     * @param array $dados Dados já validados para serem salvos no banco
     * @param string|null $id ID para atualização dos dados. Se não enviado, um novo registro será criado
     *
     * @return int|null ID do registro inserido. NULL em caso de erro
     */
    public function saveData($dados, $id) {
        if(empty($dados) || empty($dados['CardName'])) {
            $error = SYSTEM_ERRORS['form.data'];
            $error["msg"] = "Nome do Parceiro não informado!";

            $this->setLastError($error);
            return FALSE;
        }
        if(empty($dados['avatar'])) {
            $dados['avatar'] = null;
        } else {
            if(!empty($id)) {
                $error = SYSTEM_ERRORS['form.data'];
                $error["msg"] = "Avatar do Parceiro não pode ser atualizado!";

                $this->setLastError($error);
                return FALSE;
            } elseif(!filter_var($dados['avatar'], FILTER_VALIDATE_URL)) {
                $error = SYSTEM_ERRORS['form.data'];
                $error["msg"] = "URL do Avatar do Parceiro inválida!";

                $this->setLastError($error);
                return FALSE;
            }
        }

        unset($dados['CardCode']);
        unset($dados['createdAt']);
        unset($dados['createdAtBR']);
        if(!empty($id)) {
            unset($dados['avatar']);
        }

        $url = WS_BASE_URL . $this->urlSegment;
        $tipoRequisicao = "POST";
        if(!empty($id)) {
            $url .= "/" . $id;
            $tipoRequisicao = "PUT";
        }

        $retWS = json_decode(
            chamarWS(
                $url,
                $tipoRequisicao,
                array(
                    "post" => $dados
                ),
            ),
            true
        );
        return !empty($retWS['CardCode']) ? $retWS['CardCode'] : null;
    }

    /**
     * Method remove
     * Remove um registro
     *
     * @param string $id ID do registro para remover
     *
     * @return bool
     */
    public function remove($id) {
        if(empty($id)) {
            $error = SYSTEM_ERRORS['form.data'];
            $error["msg"] = "ID do Parceiro não informado!";

            $this->setLastError($error);
            return FALSE;
        }

        $retWS = json_decode(chamarWS((WS_BASE_URL . $this->urlSegment . "/" . $id), "DELETE"), true);
        return !empty($retWS['CardCode']);
    }
}
