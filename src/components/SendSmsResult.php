<?php

namespace kalyabin\sms\components;

use yii\base\Object;

/**
 * Состояние отправки сообщения
 *
 * @package common\components\sms
 */
class SendSmsResult extends Object
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
}
