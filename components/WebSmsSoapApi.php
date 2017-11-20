<?php

namespace kalyabin\sms\components;

/**
 * Компонент для работы с websms.ru с помощью SOAP-интерфейса.
 *
 * @package common\components
 */
class WebSmsSoapApi extends SmsSoapClient
{
    /**
     * @var bool Использовать тестовый режим отправки сообщений или нет
     */
    public $useTestingMode = false;

    /**
     * @inheritdoc
     */
    public function getBalance()
    {
        $res = $this->client->__soapCall('getBalance', [
            [
                'login' => $this->login,
                'pass' => $this->password,
            ]
        ]);

        if (isset($res->return)) {
            $reader = new \SimpleXMLElement($res->return);
            return isset($reader->balance) ? $reader->balance : 0;
        }

        return 0;
    }

    /**
     * @inheritdoc
     */
    protected function internalSendSms($from, $to, $text)
    {
        $res = $this->client->__soapCall('sendSMS', [
            [
                'login' => $this->login,
                'pass' => $this->password,
                'fromPhone' => $this->from,
                'messText' => $text,
                'toPhone' => $to,
                'userMessId' => 0,
                'packageId' => 0,
                'GMT' => 0,
                'sendDate' => gmdate('d.m.Y H:i:s'),
                'test' => (int) $this->useTestingMode,
            ]
        ]);

        if (isset($res->return)) {
            $reader = new \SimpleXMLElement($res->return);
            return new SendSmsResult([
                'isSent' => isset($reader->record->state) && ($reader->record->state == 'Accepted' || $reader->record->state == 'Success'),
                'providerData' => $reader,
                'rawProviderData' => $res->return,
            ]);
        }

        return new SendSmsResult([
            'isSent' => false,
        ]);
    }
}
