<?php

namespace kalyabin\sms\components;

/**
 * Компонент для работы с smsc.ru с помощью SOAP-интерфейса
 *
 * @package kalyabin\sms\components
 */
class SmsCSoapApi extends SmsSoapClient
{
    /**
     * @inheritdoc
     */
    public function getBalance()
    {
        $res = $this->client->__soapCall('get_balance', [
            [
                'login' => $this->login,
                'psw' => $this->password,
            ]
        ]);

        return isset($res->balanceresult->balance) ? $res->balanceresult->balance : 0;
    }

    /**
     * @inheritdoc
     */
    protected function internalSendSms($from, $to, $text)
    {
        $res = $this->client->__soapCall('send_sms', [
            [
                'login' => $this->login,
                'psw' => $this->password,
                'phones' => $to,
                'mes' => $text,
                'sender' => $from
            ]
        ]);

        $result = isset($res->sendresult) ? $res->sendresult : new \stdClass();

        return new SendSmsResult([
            'isSent' => !empty($result->id) && empty($result->error),
            'errorMessage' => $result->error,
            'errorCode' => $result->error,
            'providerData' => $res,
            'rawProviderData' => $res,
            'from' => $from,
            'to' => $to,
            'text' => $text,
        ]);
    }
}
