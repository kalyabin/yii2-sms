<?php

namespace tests;

use kalyabin\sms\components\SendSmsResult;
use kalyabin\sms\components\SmsCSoapApi;
use PHPUnit\Framework\TestCase;

/**
 * Тестирование класса SmsCSoapApi
 *
 * @see SmsCSoapApi
 *
 * @package tests
 */
class CaseSmsCSoapApiTest extends TestCase
{
    /**
     * @var SmsCSoapApi
     */
    protected $api;

    /**
     * @var \SoapClient|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $soap;

    protected function setUp()
    {
        $this->api = new SmsCSoapApi([
            'wsdl' => 'http://smsc.ru/sys/soap.php?wsdl',
            'login' => 'login',
            'password' => 'password',
            'from' => 'testing',
        ]);

        $this->soap = $this->getMockFromWsdl(
            __DIR__ . '/smsc.wsdl.xml', 'ServiceSoap'
        );

        $this->api->setClient($this->soap);
    }

    public function getBalanceProvider()
    {
        $expectedData1 = new \stdClass();
        $expectedData1->balanceresult = new \stdClass();
        $expectedData1->balanceresult->balance = 100.5;

        $expectedData2 = new \stdClass();
        $expectedData2->balanceresult = new \stdClass();
        $expectedData2->balanceresult->balance = 30;

        return [
            [new \stdClass(), 0],
            [$expectedData1, 100.5],
            [$expectedData2, 30],
        ];
    }

    public function getSendSmsProvider()
    {
        $expectedData1 = new \stdClass();
        $expectedData1->sendresult = new \stdClass();
        $expectedData1->sendresult->id = 1;
        $expectedData1->sendresult->error = 'has error';

        $expectedData2 = new \stdClass();
        $expectedData2->sendresult = new \stdClass();
        $expectedData2->sendresult->id = null;

        $expectedData3 = new \stdClass();
        $expectedData3->sendresult = new \stdClass();
        $expectedData3->sendresult->id = 2;

        return [
            [new \stdClass(), false],
            [$expectedData1, false],
            [$expectedData2, false],
            [$expectedData3, true],
        ];
    }

    public function getSendSmsFromProvider()
    {
        $expectedData1 = new \stdClass();
        $expectedData1->sendresult = new \stdClass();
        $expectedData1->sendresult->id = 1;
        $expectedData1->sendresult->error = 'has error';

        $expectedData2 = new \stdClass();
        $expectedData2->sendresult = new \stdClass();
        $expectedData2->sendresult->id = null;

        $expectedData3 = new \stdClass();
        $expectedData3->sendresult = new \stdClass();
        $expectedData3->sendresult->id = 2;

        return [
            [new \stdClass(), 'step1', false],
            [$expectedData1, 'step2', false],
            [$expectedData2, 'step3', false],
            [$expectedData3, 'step4', true],
        ];
    }

    /**
     * @covers SmsCSoapApi::getBalance()
     * @dataProvider getBalanceProvider
     *
     * @param \stdClass $data Данные, которые будет возвращать SOAP
     * @param float $balance Сумма на балансе
     */
    public function testGetBalance($data, $balance)
    {
        $this->soap
            ->method('__soapCall')
            ->with('get_balance', [
                [
                    'login' => $this->api->login,
                    'psw' => $this->api->password,
                ]
            ])
            ->will($this->returnValue($data));

        $this->assertEquals($balance, $this->api->getBalance());
    }

    /**
     * @covers SmsCSoapApi::sendSms()
     * @dataProvider getSendSmsProvider
     *
     * @param \stdClass $data Данные, которые будет возвращать SOAP
     * @param boolean $isSent Статус отправки
     */
    public function testSendSms($data, $isSent)
    {
        $this->soap
            ->method('__soapCall')
            ->with('send_sms', [
                [
                    'login' => $this->api->login,
                    'psw' => $this->api->password,
                    'phones' => '71111111111',
                    'mes' => 'testing',
                    'sender' => $this->api->from,
                ]
            ])
            ->will($this->returnValue($data));

        $result = $this->api->sendSms('71111111111', 'testing');

        $this->assertInstanceOf(SendSmsResult::class, $result);
        $this->assertEquals($isSent, $result->isSent);
        $this->assertEquals('testing', $result->text);
        $this->assertEquals('71111111111', $result->to);
        $this->assertEquals('testing', $result->from);

        $this->assertEquals($data, $result->providerData);
        $this->assertEquals($data, $result->rawProviderData);

        if (!empty($data->sendresult->error)) {
            $this->assertEquals($data->sendresult->error, $result->errorCode);
            $this->assertEquals($data->sendresult->error, $result->errorMessage);
        }

        if ($isSent) {
            $this->assertNotEmpty($result->providerData->sendresult->id);
            $this->assertNotEmpty($result->rawProviderData->sendresult->id);
        }
    }

    /**
     * @covers SmsCSoapApi::sendSmsFrom()
     * @dataProvider getSendSmsFromProvider
     *
     * @param \stdClass $data Данные, которые должен вернуть SOAP
     * @param string $from Имя отправителя
     * @param boolean $isSent Статус отправки
     */
    public function testSendSmsFrom($data, $from, $isSent)
    {
        $this->soap
            ->method('__soapCall')
            ->with('send_sms', [
                [
                    'login' => $this->api->login,
                    'psw' => $this->api->password,
                    'phones' => '71111111111',
                    'mes' => 'testing',
                    'sender' => $from,
                ]
            ])
            ->will($this->returnValue($data));

        $result = $this->api->sendSmsFrom($from, '71111111111', 'testing');

        $this->assertInstanceOf(SendSmsResult::class, $result);
        $this->assertEquals($isSent, $result->isSent);
        $this->assertEquals('testing', $result->text);
        $this->assertEquals('71111111111', $result->to);
        $this->assertEquals($from, $result->from);

        $this->assertEquals($data, $result->providerData);
        $this->assertEquals($data, $result->rawProviderData);

        if (!empty($data->sendresult->error)) {
            $this->assertEquals($data->sendresult->error, $result->errorCode);
            $this->assertEquals($data->sendresult->error, $result->errorMessage);
        }

        if ($isSent) {
            $this->assertNotEmpty($result->providerData->sendresult->id);
            $this->assertNotEmpty($result->rawProviderData->sendresult->id);
        }
    }
}
