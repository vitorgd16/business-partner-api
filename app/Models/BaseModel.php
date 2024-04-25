<?php

namespace App\Models;

use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Debug\Timer;
use CodeIgniter\Model;

/**
 * Class BaseModel
 *
 * BaseModel provides a convenient place for loading components
 * and performing functions that are needed by all your models.
 *
 * For security be sure to declare any new methods as protected or private.
 */
abstract class BaseModel extends Model
{
    /**
     * An array of helpers to be loaded automatically upon
     * class instantiation. These helpers will be available
     * to all other controllers that extend BaseController.
     *
     * @var array
     */
    protected $helpers = ["funcoes"];

    /**
     * Data/hora ao iniciar a chamada do model
     *
     * @var string
     */
    private static $dtAgora;

    /**
     * Guarda o último erro da model
     *
     * @var array
     */
    private static $lastError = array();

    /**
     * Called during initialization. Appends
     * our custom field to the module's model.
     */
    protected function initialize() {
        // Do Not Edit This Line
        parent::initialize();

        helper($this->helpers);
        if(!isset(self::$dtAgora)) {
            self::$dtAgora = dataAgoraFormatada("Y-m-d H:i:s");
        }
    }

    /**
     * Method getDtAgora.
     * Obtem a Data/hora do inicio da chamada do model
     *
     * @return string
     */
    public function getDtAgora() {
        return self::$dtAgora;
    }

    /**
     * Method getLastError
     * Obtem o erro da última execução de query caso haja
     *
     * @return array
     */
    public function getLastError() {
        return self::$lastError;
    }

    /**
     * Method setLastError
     * Guarda o erro da última execução de query
     *
     * @param array $lastError Array com o 'code' e a 'msg' do erro
     */
    public function setLastError($lastError = array()) {
        if(!empty(self::$lastError) && !empty($lastError)) {
            return;
        }

        if(empty($lastError)) {
            $lastError = array();
        }
        self::$lastError = $lastError;
    }
}
