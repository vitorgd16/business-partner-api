<?php

/**
 * Function my_urlencode
 * Realiza um enconde na URL removendo caracteres não permitidos
 *
 * @param string $string String para ser codificada
 *
 * @return string URL Codificada
 */
function my_urlencode($string) {
    $entities = array('%21', '%2A', '%27', '%28', '%29', '%3B', '%3A', '%40', '%26', '%3D', '%2B', '%24', '%2C', '%2F', '%3F', '%25', '%23', '%5B', '%5D');
    $replacements = array('!', '*', "'", "(", ")", ";", ":", "@", "&", "=", "+", "$", ",", "/", "?", "%", "#", "[", "]");
    return str_replace($entities, $replacements, urlencode($string));
}

/**
 * Function my_array_key_exists
 * Retorna se uma propriedade de um array ou objeto existe
 *
 * @param string $property Propriedade do array para ser verificada
 * @param array|object $arr Array ou objeto para ser verificado
 * @return bool
 */
function my_array_key_exists($property, $arr) {
    if(empty($arr) || (!is_object($arr) && !is_array($arr))) {
        return FALSE;
    }

    if(is_array($arr)) {
        $arr = (object) $arr;
    }
    return property_exists($arr, $property);
}

/**
 * Function contemString.
 * Testa se existe a string na outra string
 *
 * @param string $str String para testar se contem a outra string
 * @param string $strSearch String que deve conter na outra string
 * @param bool $ignorarCase Permite ignorar o case sensitive
 * @return bool
 */
function contemString ($str, $strSearch, $ignorarCase = FALSE) {
    return ($ignorarCase ? stripos($str, $strSearch) : strpos($str, $strSearch)) !== false;
}

/**
 * Function decodificaTexto.
 * Decodifica tipos TEXT do MYSql.
 * @param string $str String para decodificar
 * @return string
 *
 * @ref https://stackoverflow.com/questions/2934563/how-to-decode-unicode-escape-sequences-like-u00ed-to-proper-utf-8-encoded-cha
 */
function decodificaTexto ($str) {
    return empty($str) ? $str : preg_replace_callback('/\\\\u([0-9a-fA-F]{4})/i', function ($match) {
        return mb_convert_encoding(pack('H*', $match[1]), 'UTF-8', 'UCS-2BE');
    }, $str);
}

/**
 * Function strEndsWith
 * Verifica se uma string termina com uma palavra/letra especifica
 *
 * @param string $haystack String para verificar
 * @param string $needle String final para comparar a string
 * @return bool
 *
 * @ref https://stackoverflow.com/questions/834303/startswith-and-endswith-functions-in-php
 */
function strEndsWith($haystack, $needle) {
    if(empty($haystack)) return false;
    if(empty($needle)) return true;

    return substr($haystack, (-1 * strlen($needle))) === $needle;
}

/**
 * Function removeStrEndsWith
 * Se a string for finalizada com os caracteres enviados para a função, os mesmos são removidos
 *
 * @param string $str String para verificar
 * @param string $endWith String final para comparar a string
 * @return string
 */
function removeStrEndsWith($str, $endWith) {
    if(empty($str) || empty($endWith)) return $str;

    if (strEndsWith($str, $endWith)) {
        $str = substr_replace($str, "", (-1 * strlen($endWith)));
    }
    return $str;
}

/**
 * Function xssCleanRecursive
 * Realiza uma varredura XSS Clean nas variaveis enviadas
 *
 * @param mixed $var Variavel para passar pela limpeza
 *
 * @return mixed Retorno da variavel com o XSS Clean
 */
function xssCleanRecursive($var){
    if(empty($var)) return $var;

    /**
     * Realiza uma limpeza em strings
     *
     * @param string $data String para ser verificada
     *
     * @return string
     *
     * @ref https://stackoverflow.com/questions/1336776/xss-filtering-function-in-php
     */
    $xssClean = function ($data){
        if(empty($data)) return "";

        // Fix &entity\n;
        $data = str_replace(array('&amp;','&lt;','&gt;'), array('&amp;amp;','&amp;lt;','&amp;gt;'), $data);
        $data = preg_replace('/(&#*\w+)[\x00-\x20]+;/u', '$1;', $data);
        $data = preg_replace('/(&#x*[0-9A-F]+);*/iu', '$1;', $data);
        $data = html_entity_decode($data, ENT_COMPAT, 'UTF-8');

        // Remove any attribute starting with "on" or xmlns
        $data = preg_replace('#(<[^>]+?[\x00-\x20"\'])(?:on|xmlns)[^>]*+>#iu', '$1>', $data);

        // Remove javascript: and vbscript: protocols
        $data = preg_replace('#([a-z]*)[\x00-\x20]*=[\x00-\x20]*([`\'"]*)[\x00-\x20]*j[\x00-\x20]*a[\x00-\x20]*v[\x00-\x20]*a[\x00-\x20]*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iu', '$1=$2nojavascript...', $data);
        $data = preg_replace('#([a-z]*)[\x00-\x20]*=([\'"]*)[\x00-\x20]*v[\x00-\x20]*b[\x00-\x20]*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iu', '$1=$2novbscript...', $data);
        $data = preg_replace('#([a-z]*)[\x00-\x20]*=([\'"]*)[\x00-\x20]*-moz-binding[\x00-\x20]*:#u', '$1=$2nomozbinding...', $data);

        // Only works in IE: <span style="width: expression(alert('Ping!'));"></span>
        $data = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?expression[\x00-\x20]*\([^>]*+>#i', '$1>', $data);
        $data = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?behaviour[\x00-\x20]*\([^>]*+>#i', '$1>', $data);
        $data = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:*[^>]*+>#iu', '$1>', $data);

        // Remove namespaced elements (we do not need them)
        $data = preg_replace('#<\/*\w+:\w[^>]*+>#i', '', $data);

        do
        {
            // Remove really unwanted tags
            $old_data = $data;
            $data = preg_replace('#<\/*(?:applet|b(?:ase|gsound|link)|embed|frame(?:set)?|i(?:frame|layer)|l(?:ayer|ink)|meta|object|s(?:cript|tyle)|title|xml)[^>]*+>#i', '[REMOVED]', $data);
        }
        while ($old_data !== $data);

        // we are done...
        return $data;
    };

    if(!is_array($var)) {
        return $xssClean($var);
    }

    foreach ($var AS $key => $dado){
        $var[$key] = xssCleanRecursive($dado);
    }

    return $var;
}

/**
 * Function validaJson.
 * Valida uma string para saber se a mesma é um JSON
 * @param string $str String para testar o JSON
 * @return bool
 *
 * @ref https://stackoverflow.com/questions/6041741/fastest-way-to-check-if-a-string-is-json-in-php
 */
function validaJson($str) {
    if (!empty($str) && is_string($str)) {
        @json_decode($str);
        return (json_last_error() === JSON_ERROR_NONE);
    }
    return FALSE;
}

/**
 * Function validaDate.
 * Verifica se a data enviada é uma data válida no formato especificado
 * @param string $data Data para ser verificada por seu formato
 * @param string $formato Formato para verificar a data
 * @return bool
 *
 * @ref https://stackoverflow.com/questions/19271381/correctly-determine-if-date-string-is-a-valid-date-in-that-format
 */
function validaDate($data, $formato = "Y-m-d") {
    if (empty($data)) return FALSE;
    if (empty($formato)) $formato = "Y-m-d";

    $d = DateTime::createFromFormat($formato, $data);
    return $d && $d->format($formato) === $data;
}

/**
 * Function getFilteredKey.
 * Valida uma string para saber se a mesma é um JSON
 * @param mixed $obj Array de dados ou váriavel para obtenção segura
 * @param string|null $key Key do array de dados para obtenção segura. Se NULL, verifica a váriavel enviada
 * @param array $opts Opções para a obtenção segura da key do array
 *
 * @return mixed
 */
function getFilteredKey($obj, $key = null, $opts = array()) {
    $ret = ((!empty($opts) && my_array_key_exists('ifNULL', $opts)) ? $opts['ifNULL'] : null);
    if($obj === NULL || ($key !== NULL && (!is_array($obj) || !my_array_key_exists($key, $obj) || $obj[$key] === NULL))) {
        return $ret;
    }

    $fnFilter = function ($ret, $opts) {
        if(empty($opts)) {
            return $ret;
        }

        if(!empty($opts['decodeStr'])) {
            $ret = decodificaTexto($ret);
        }
        if(!empty($opts['xss_clean'])) {
            $ret = xssCleanRecursive($ret);
        }
        if(!empty($opts['trim'])) {
            $ret = trim($ret);
        }

        if(!empty($opts['urld'])) {
            $ret = urldecode($ret);
        }

        return $ret;
    };

    $ret = null;
    $ret = (($key !== NULL) ? $obj[$key] : $obj);
    if(is_array($ret) || is_object($ret)) {
        $item = array();
        $keyAux = 0;
        foreach ($ret AS $keyAux => $item) {
            if(is_array($ret) || is_object($ret)) {
                $ret[$keyAux] = getFilteredKey($item, NULL, $opts);
            } else {
                $ret[$keyAux] = $fnFilter($item, $opts);
            }
        }
        $keyAux = null;
        $item = null;
    } else {
        $ret = $fnFilter($ret, $opts);
    }

    return $ret;
}

/**
 * Method toDate.
 * Gera um objeto DateTime para testes em programação com datas
 * @param string $date Data para ser transformada
 * @param string $format Formato para a data ser transformada em objeto
 * @return DateTime
 */
function toDate($date, $format = 'Y-m-d') {
    if(!validaDate($date, $format)) return null;
    return DateTime::createFromFormat($format, $date);
}

/**
 * Function dataAgoraFormatada.
 * Pega a data de agora no formato escolhido na timezone escolhida.
 * @param string $formatoData Formato para a data
 * @param string $timeZone TimeZone da data
 * @return string
 *
 * @ref http://php.net/manual/pt_BR/function.date.php
 */
function dataAgoraFormatada ($formatoData = "Y-m-d", $timeZone = "America/Sao_Paulo") {
    if (empty($formatoData)) $formatoData = "Y-m-d";
    if (empty($timeZone)) $timeZone = "America/Sao_Paulo";

    date_default_timezone_set($timeZone);
    $dt = null;
    try{
        $dt = new DateTime('NOW');
    } catch (Exception $err) {
        $dt = null;
    }
    return !empty($dt) ? $dt->format($formatoData) : "";
}

/**
 * Function dataParaOutraData.
 * Faz a conversão de uma data válida para outra.
 * @param string $data Data para ser convertida
 * @param string $formatoDe Formato da data enviada
 * @param string $formatoPara Formato para ser convertido
 * @return string
 *
 * @ref https://secure.php.net/manual/pt_BR/datetime.createfromformat.php
 */
function dataParaOutraData($data, $formatoDe = "Y-m-d", $formatoPara = "d/m/Y") {
    if (empty($formatoDe)) $formatoDe = "Y-m-d";
    if (empty($formatoPara)) $formatoPara = "d/m/Y";

    if(!validaDate($data, $formatoDe)) return "";
    return DateTime::createFromFormat($formatoDe, $data)->format($formatoPara);
}

/**
 * Method dateDiffDays.
 * Pega a diferença em dias entre duas datas
 *
 * @param string $dataInicio Data de inicio
 * @param string $dataFim Data final
 * @param string $formatInicio Formato da data inicial enviada
 * @param string $formatFim Formato da data final enviada
 *
 * @return int Resultado de dias
 *
 * @ref https://stackoverflow.com/questions/676824/how-to-calculate-the-difference-between-two-dates-using-php
 */
function dateDiffDays (
    $dataInicio, $dataFim,
    $formatInicio = "Y-m-d", $formatFim = "Y-m-d"
) {
    if (empty($formatInicio)) $formatInicio = "Y-m-d";
    if (empty($formatFim))    $formatFim = "Y-m-d";
    $dataInicio = dataParaOutraData($dataInicio, $formatInicio, 'Y-m-d');
    $dataFim = dataParaOutraData($dataFim, $formatFim, 'Y-m-d');

    if (
        !validaDate($dataInicio, 'Y-m-d') ||
        !validaDate($dataFim, 'Y-m-d')
    ) return FALSE;

    $dataInicio = toDate($dataInicio, 'Y-m-d');
    $dataFim    = toDate($dataFim, 'Y-m-d');
    $diff = $dataFim->diff($dataInicio)->format('%a');

    return abs($diff);
}

/**
 * Function getDiaSemana.
 * Pega o dia da semana referente à uma data
 * @param string $date Data para ser testada
 * @param string $format Formato da data
 * @return string
 *
 * @ref https://forum.imasters.com.br/topic/237012-descobrir-dia-da-semana/
 */
function getDiaSemana($date, $format = 'Y-m-d') {
    // Traz o dia da semana para qualquer data informada
    if(empty($format)) $format = 'Y-m-d';
    if(!validaDate($date, $format)) return "";

    $diasemana = DateTime::createFromFormat($format, $date)->format('w');
    switch($diasemana){
        case 0:
            $diasemana = "domingo";
            break;
        case 1:
            $diasemana = "segunda-feira";
            break;
        case 2:
            $diasemana = "terça-feira";
            break;
        case 3:
            $diasemana = "quarta-feira";
            break;
        case 4:
            $diasemana = "quinta-feira";
            break;
        case 5:
            $diasemana = "sexta-feira";
            break;
        case 6:
            $diasemana = "sábado";
            break;
        default:
            $diasemana = "";
            break;
    }

    return $diasemana;
}

/**
 * Function getMes.
 * Pega o mês por extenso de acordo com a data enviada.
 * @param string $date Data para ser testada
 * @param string $format Formato da data
 * @return string Mês em String
 */
function getMes($date, $format = 'Y-m-d') {
    // Traz o dia da semana para qualquer data informada
    if(empty($format)) $format = 'Y-m-d';
    if(!validaDate($date, $format)) return "";

    $mes = DateTime::createFromFormat($format, $date)->format('m');
    switch ($mes){
        case 1:
            $mes = "janeiro";
            break;
        case 2:
            $mes = "fevereiro";
            break;
        case 3:
            $mes = "março";
            break;
        case 4:
            $mes = "abril";
            break;
        case 5:
            $mes = "maio";
            break;
        case 6:
            $mes = "junho";
            break;
        case 7:
            $mes = "julho";
            break;
        case 8:
            $mes = "agosto";
            break;
        case 9:
            $mes = "setembro";
            break;
        case 10:
            $mes = "outubro";
            break;
        case 11:
            $mes = "novembro";
            break;
        case 12:
            $mes = "dezembro";
            break;
        default:
            $mes = "";
            break;
    }

    return $mes;
}

/**
 * Function dataParaDataBrPorExtenso.
 * Transforma uma data de um formato especifico para uma data por extenso em pt-br;
 * @param string $date Data para ser transformada
 * @param string $formatoDe Formato da data enviada
 * @param bool $upperFirst Define se a primeira letra do texto de retorno vai ser upper ou lower case
 * @param string $timeZone Timezone
 * @return string
 */
function dataParaDataBrPorExtenso (
    $date, $formatoDe = "Y-m-d H:i:s",
    $upperFirst = TRUE, $timeZone = "America/Sao_Paulo"
) {
    if(empty($timeZone)) $timeZone = "America/Sao_Paulo";
    if(empty($formatoDe)) $formatoDe = "Y-m-d H:i:s";
    if(!validaDate($date, $formatoDe)) return NULL;

    $functionSeparaObj = function ($date) {
        $date = explode(' ', $date);
        $data = explode('-', $date[0]);
        $hora = explode(':', $date[1]);

        return (object) array (
            'dia'        => str_pad($data[2], 2, "0", STR_PAD_LEFT),
            'mes'        => str_pad($data[1], 2, "0", STR_PAD_LEFT),
            'ano'        => str_pad($data[0], 4, "0", STR_PAD_LEFT),
            'hora'       => str_pad($hora[0], 2, "0", STR_PAD_LEFT),
            'minuto'     => str_pad($hora[1], 2, "0", STR_PAD_LEFT),
            'segundo'    => str_pad($hora[2], 2, "0", STR_PAD_LEFT),
            'diaSemana'  => getDiaSemana($date[0], 'Y-m-d'),
            'mesExtenso' => getMes($date[0]),
        );
    };

    $date = dataParaOutraData($date, $formatoDe, 'Y-m-d H:i:s');
    $dateOBJ = $functionSeparaObj($date);

    $dateNow = dataAgoraFormatada('Y-m-d H:i:s', $timeZone);
    $dateNowOBJ = $functionSeparaObj($dateNow);
    $functionSeparaObj = null;

    $complementoTime = "";
    if(contemString($formatoDe, "H")) {
        $complementoTime = ", às $dateOBJ->hora" . "h";
        if(contemString($formatoDe, "i")) {
            $complementoTime .= " e $dateOBJ->minuto" . "min";
        }
    }

    $ret = null;
    if(dateDiffDays($date, $dateNow, 'Y-m-d H:i:s', 'Y-m-d H:i:s') === 1) {
        if(toDate($dateNow, 'Y-m-d H:i:s') > toDate($date, 'Y-m-d H:i:s')) {
            $ret = "ontem, $dateOBJ->diaSemana";
        } else {
            $ret = "amanhã, $dateOBJ->diaSemana";
        }
    } elseif(dateDiffDays($date, $dateNow, 'Y-m-d H:i:s', 'Y-m-d H:i:s') === 2) {
        if(toDate($dateNow, 'Y-m-d H:i:s') > toDate($date, 'Y-m-d H:i:s')) {
            $ret = "anteontem, $dateOBJ->diaSemana";
        }
    }

    if(empty($ret)) {
        if ($dateOBJ->ano == $dateNowOBJ->ano) {
            if ($dateOBJ->mes == $dateNowOBJ->mes) {
                if ($dateOBJ->dia == $dateNowOBJ->dia) {
                    $ret = "hoje, $dateOBJ->diaSemana";
                } else {
                    $ret = "dia $dateOBJ->dia deste mês, $dateOBJ->diaSemana";
                }
            } elseif ($dateOBJ->mes == ($dateNowOBJ->mes - 1)) {
                $ret = "$dateOBJ->diaSemana, dia $dateOBJ->dia do mês passado";
            } else {
                $ret = "$dateOBJ->diaSemana, $dateOBJ->dia de $dateOBJ->mesExtenso";
            }
        } elseif ($dateOBJ->ano == ($dateNowOBJ->ano - 1)) {
            $ret = "$dateOBJ->diaSemana, $dateOBJ->dia de $dateOBJ->mesExtenso do ano passado";
        } else {
            $ret = "$dateOBJ->diaSemana, $dateOBJ->dia de $dateOBJ->mesExtenso de $dateOBJ->ano";
        }
    }
    $ret .= $complementoTime;
    $complementoTime = null;
    $dateOBJ = null;
    $dateNow = null;
    $dateNowOBJ = null;

    return $upperFirst ? ucfirst($ret) : $ret;
}

/**
 * Function header_status
 * Força um status para o retorno da URL
 *
 * @param int $httpCode HTTP Code para forçar o retorno
 * @param null|string $httpStr HTTP String que a URL retornará para um HTTP Code de erro
 *
 * @ref https://stackoverflow.com/questions/4162223/how-to-send-500-internal-server-error-error-from-a-php-script
 */
function header_status($httpCode, $httpStr = null) {
    if (is_cli()) {
        return;
    }

    if (empty($httpCode) || !filter_var($httpCode, FILTER_VALIDATE_INT)) {
        header_status(500, "Status codes must be an integer");
        exit(0);
    }

    $httpCodes = array (
        100 => 'Continue',
        101 => 'Switching Protocols',
        102 => 'Processing',
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        207 => 'Multi-Status',
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        307 => 'Temporary Redirect',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Requested Range Not Satisfiable',
        417 => 'Expectation Failed',
        422 => 'Unprocessable Entity',
        423 => 'Locked',
        424 => 'Failed Dependency',
        426 => 'Upgrade Required',
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
        506 => 'Variant Also Negotiates',
        507 => 'Insufficient Storage',
        509 => 'Bandwidth Limit Exceeded',
        510 => 'Not Extended',
    );
    if($httpStr !== NULL) {
        $httpCodes[$httpCode] = $httpStr;
    } elseif(empty($httpCodes[$httpCode]) && $httpStr === NULL) {
        $httpCodes[$httpCode] = "Unknown";
    }

    if (strpos(PHP_SAPI, 'cgi') === 0) {
        header('Status: ' . $httpCode . ' ' . $httpCodes[$httpCode], TRUE);
        return;
    }

    $server_protocol = (
        isset($_SERVER['SERVER_PROTOCOL']) &&
        in_array(
            $_SERVER['SERVER_PROTOCOL'],
            array(
                'HTTP/1.0',
                'HTTP/1.1',
                'HTTP/2'
            ), TRUE
        )
    ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.1';

    header($server_protocol . ' ' . $httpCode . ' ' . $httpCodes[$httpCode], true, $httpCode);
}

/**
 * Function getUrl
 * Garante a URL formatada.
 * @param string $str URL para ser formatada
 * @param bool $apenasDominio Garante pegar apenas o dominio da URL ou não
 * @param string|bool $protocol Define o protocolo a ser retornado (HTTP / HTTPS) FALSE para manter o original
 * @return string Dominio formatado
 */
function getUrl($str, $apenasDominio = TRUE, $protocol = FALSE) {
    $protocol = formatProtocol($protocol);
    $ret = '';

    $str = trim($str);
    if(
        empty($protocol) &&
        strlen($str) >= 5 &&
        strtolower(substr($str, 0, 5)) === "https"
    ) {
        $ret = 'https://';
    } elseif(
        empty($protocol) &&
        strlen($str) >= 4 &&
        strtolower(substr($str, 0, 4)) === "http"
    ) {
        $ret = 'http://';
    } elseif (!empty($protocol)) {
        $ret = $protocol;
    }

    $str = str_replace('\\', '/', $str);
    $str = str_replace('http://', '', $str);
    $str = str_replace('https://', '', $str);
    $str = str_replace('www.', '', $str);
    $str = removeStrEndsWith($str, '/');
    $str = trim($str);

    if($apenasDominio){
        $str = explode('/', $str);
        if(!empty($str[0])) $ret .= $str[0];
    }else{
        $ret .= $str;
    }
    $ret = my_urlencode($ret);

    return $ret;
}

/**
 * Function montaGetUrl.
 * Monta os parametros GETs na URL
 * @param string $url URL para ser alterada
 * @param array $paramsGet Parâmetros GET para a URL
 * @return string
 */
function montaGetUrl($url, $paramsGet = array()) {
    if(empty($paramsGet)) $paramsGet = array();
    if(!contemString($url, "?")){
        if(empty($url)) {
            $url = "?";
        } else {
            $url = removeStrEndsWith($url, '/') . '?';
        }
    } else {
        $url .= "&";
    }

    $getParteUrl = "";
    foreach ($paramsGet AS $getParam => $valorGet){
        if(!empty($getParteUrl)) $getParteUrl .= '&';
        $getParteUrl .= $getParam . '=' . urlencode($valorGet);
    }

    $url .= $getParteUrl;
    if(empty($getParteUrl)) {
        $url = substr($url, 0,-1);
    }
    $getParteUrl = null;

    return $url;
}

/**
 * Function formatProtocol.
 * Retorna o protocolo formatado se o protocolo for válido, caso contrário FALSE
 *
 * @param string|bool $protocol Protocolo para formatar
 *
 * @return string|bool
 */
function formatProtocol($protocol){
    if(empty($protocol)) return FALSE;
    $protocol = strtolower($protocol);
    $protocol = str_replace('\\', '/', $protocol);
    $protocol = str_replace('/', '', $protocol);
    $protocol = str_replace(':', '', $protocol);
    $protocol = trim($protocol);

    if($protocol === "http" || $protocol === "https") {
        return $protocol . "://";
    }
    return FALSE;
}

/**
 * Method chamarWS.
 * Faz requisições no WS de acordo com as configurações enviadas
 * @param string $url URL do WS
 * @param string $tipoRequest Define o tipo da requisição, como POST, GET, DELETE, PUT...
 * @param array $params Parametros de envio da requisição ao WS
 *                      'get' => Define os GET's da requisição. O array pode ser montado de duas formas:
 *                               - Array numérico contendo como valor o GET: array("var1=2","var2=teste")
 *                               - Array contendo a chave e valor do GET: array("var1" => 1,"var2" => "teste")
 *                      'post' => Define os POST's da requisição. O array pode ser montado da seguinte forma:
 *                                - Array contendo a chave e valor do POST: array("var1" => 1,"var2" => "teste")
 *                                O POST também pode ser enviado em formato texto se enviada a opção 'useRaw'.
 *                                No caso de ser necessário enviar FILE's pela requisição, siga as seguintes instruções:
 *                                - Caso a chave dos FILE's não seja numérica não envie a mesma chave em FILE's e POST's
 *                                - Caso a chave dos FILE's seja numérica então não utilize o prefixo "f_" como chave
 *                                  dos POST's
 *                                Arquivos são convertidos em POST's no CURL.
 *                      'sslPeer' => Define o tipo de verificação do certificado SSL para o PEER.
 *                                   Normalmente em requisições HTTPS deve ser enviado FALSE nessa opção.
 * @return string
 */
function chamarWS($url, $tipoRequest, $params = array()) {
    $tipoRequest = strtoupper($tipoRequest);

    $params['timeout'] = 15;
    $params['maxRedir'] = 10;
    $params['follow'] = TRUE;

    if(empty($params['get']))     $params['get'] = array();
    if(empty($params['post']))    $params['post'] = array();

    if(!my_array_key_exists("sslPeer", $params)) $params['sslPeer'] = NULL;
    elseif(empty($params['sslPeer']) && $params['sslPeer'] !== NULL) $params['sslPeer'] = 0;
    elseif(!empty($params['sslPeer'])) $params['sslPeer'] = 1;

    if(!my_array_key_exists("follow", $params)) $params['follow'] = NULL;
    elseif(empty($params['follow'])) $params['follow'] = FALSE;
    else $params['follow'] = TRUE;

    $url = montaGetUrl(getUrl($url, false, false), $params['get']);

    $sendPost = "";
    if(!empty($params['post'])){
        if(is_array($params['post']) || is_object($params['post'])) {
            $sendPost = json_encode($params['post'], TRUE);
        } else {
            $sendPost = $params['post'];
        }
    }
    $params['post'] = null;
    $params['post'] = $sendPost;
    $sendPost = null;

    $ch = curl_init();

    curl_setopt($ch,  CURLOPT_URL,  $url);
    curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_NONE);
    curl_setopt($ch, CURLOPT_ENCODING, "");
    curl_setopt($ch,  CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_NONE);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_MAXREDIRS, $params['maxRedir']);
    curl_setopt($ch,  CURLOPT_FOLLOWLOCATION, $params['follow']);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $tipoRequest);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $params['timeout']);
    curl_setopt($ch, CURLOPT_TIMEOUT, $params['timeout']);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
    if(!empty($params['post'])) {
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params['post']);
    }
    if($params['sslPeer'] !== NULL) curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $params['sslPeer']);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    if($httpCode < 200 || $httpCode > 299) {
        $response = json_encode(
            array(
                'cError' => array(
                    'code' => $httpCode,
                    'msg' => addslashes(curl_error($ch)),
                ),
                'response' => (validaJson($response) ? json_decode($response, true) : addslashes($response ?? "")),
            ),
            true
        );
    }
    curl_close($ch);

    return $response;
}