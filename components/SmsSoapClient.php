<?php

namespace kalyabin\sms\components;

use yii\base\Component;

/**
 * Базовый класс для построения SOAP-клиентов для отправки SMS-сообщений.
 *
 * @package common\components
 */
abstract class SmsSoapClient extends Component
{
    /**
     * @var string Адрес WSDL
     */
    public $wsdl;

    /**
     * @var string Логин для авторизации
     */
    public $login;

    /**
     * @var string Пароль для авторизации
     */
    public $password;

    /**
     * @var string Имя отправителя по умолчанию
     */
    public $from;

    /**
     * @var array Опции для создания SOAP-клиента
     */
    public $soapOptions = [];

    /**
     * @var bool При создании SOAP-клиента использовать HTTP-авторизацию
     */
    public $useHttpAuthorization = false;

    /**
     * @var string Категория для логирования ошибок
     */
    public $logCategory = 'sms';

    /**
     * @var \SoapClient
     */
    protected $client;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        $defaultOptions = [
            'compression' => SOAP_COMPRESSION_ACCEPT | SOAP_COMPRESSION_GZIP,
            'trace' => true,
            'cache_wsdl' => WSDL_CACHE_BOTH,
            'connection_timeout' => 5,
            'features' => SOAP_SINGLE_ELEMENT_ARRAYS | SOAP_USE_XSI_ARRAY_TYPE
        ];

        $options = array_merge($defaultOptions, $this->soapOptions);
        if ($this->useHttpAuthorization) {
            $options = array_merge($options, [
                'login' => $this->login,
                'password' => $this->password,
            ]);
        }

        $this->client = new \SoapClient($this->wsdl, $options);
    }

    /**
     * Получить SOAP-клиент
     *
     * @return \SoapClient
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * Получить баланс на счёте
     *
     * @return string
     */
    abstract public function getBalance();

    /**
     * Внутренний метод для отправки сообщений
     *
     * @param string $from Имя отправителя
     * @param string $to Имя получателя
     * @param string $text Текст сообщения
     *
     * @return SendSmsResult
     */
    abstract protected function internalSendSms($from, $to, $text);

    /**
     * Отправить SMS с отправителем, отличным от отправителя по умолчанию
     *
     * @param string $from Имя отправителя
     * @param string $to Имя получателя
     * @param string $text Текст сообщения
     *
     * @return SendSmsResult
     */
    public function sendSmsFrom($from, $to, $text)
    {
        return $this->internalSendSms($from, $to, $text);
    }

    /**
     * Отправить SMS с отправителем по умолчанию
     *
     * @param string $to Адрес получателя
     * @param string $text Текст сообщения
     *
     * @return SendSmsResult
     */
    public function sendSms($to, $text)
    {
        return $this->sendSmsFrom($this->from, $to, $text);
    }
}
