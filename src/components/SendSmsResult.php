<?php

namespace kalyabin\sms\components;

/**
 * Состояние отправки сообщения
 *
 * @package common\components\sms
 */
class SendSmsResult extends \yii\base\BaseObject
{
    /**
     * @var boolean True, если сообщение было отправлено
     */
    public $isSent;

    /**
     * @var string Текст ошибки, если сообщение не было отправлено
     */
    public $errorMessage;

    /**
     * @var integer Код ошибки, если сообщение не было отправлено
     */
    public $errorCode;

    /**
     * @var array Поля, которые вернул провайдер в SOAP-сообщении
     */
    public $providerData;

    /**
     * @var string Необработанные поля, которые вернул провайдер в SOAP-сообщении
     */
    public $rawProviderData;

    /**
     * @var string Имя отправителя
     */
    public $from;

    /**
     * @var string Имя получателя
     */
    public $to;

    /**
     * @var string Текст сообщения
     */
    public $text;
}
