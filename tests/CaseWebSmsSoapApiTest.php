<?php

namespace tests;

use kalyabin\sms\components\SendSmsResult;
use PHPUnit\Framework\TestCase;
use kalyabin\sms\components\WebSmsSoapApi;

/**
 * Тестирование класса WebSmsSoapApi
 *
 * @see WebSmsSoapApi
 *
 * @package tests
 */
class CaseWebSmsSoapApiTest extends TestCase
{
    /**
     * @var WebSmsSoapApi
     */
    protected $api;

    /**
     * @var \SoapClient|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $soap;

    protected function setUp()
    {
        $this->api = new WebSmsSoapApi([
            'wsdl' => 'http://smpp3.websms.ru:8183/soap?WSDL',
            'login' => 'login',
            'password' => 'password',
            'from' => 'testing',
            'useHttpAuthorization' => false,
        ]);

        $this->soap = $this->getMockFromWsdl(
            __DIR__ . '/websms.wsdl.xml', 'WebsmsSoapServService'
        );

        $this->api->setClient($this->soap);
    }

    public function getBalanceProvider()
    {
        $expectedData1 = new \stdClass();
        $expectedData1->return = '<result><balance>100.5</balance><userid>111</userid></result>';

        $expectedData2 = new \stdClass();
        $expectedData2->return = '<result><balance>30</balance><userid>111</userid></result>';

        return [
            [new \stdClass(), 0],
            [$expectedData1, 100.5],
            [$expectedData2, 30],
        ];
    }

    public function getSendSmsProvider()
    {
        $expectedData1 = new \stdClass();
        $expectedData1->return = '<result><record><state>Accepted</state></record></result>';

        $expectedData2 = new \stdClass();
        $expectedData2->return = '<result><record><state>Success</state></record></result>';

        return [
            [new \stdClass(), false],
            [$expectedData1, true],
            [$expectedData2, true]
        ];
    }

    /**
     * @covers WebSmsSoapApi::getBalance()
     * @dataProvider getBalanceProvider
     *
     * @param \stdClass $data Данные, которые будет возвращать SOAP
     * @param float $balance Сумма на балансе
     */
    public function testGetBalance($data, $balance)
    {
        $this->soap
            ->method('__soapCall')
            ->with('getBalance', [
                [
                    'login' => $this->api->login,
                    'pass' => $this->api->password,
                ]
            ])
            ->will($this->returnValue($data));

        $this->assertEquals($balance, $this->api->getBalance());
    }

    /**
     * @covers WebSmsSoapApi::sendSms()
     * @dataProvider getSendSmsProvider
     *
     * @param \stdClass $data Данные, которые будет возвращать SOAP
     * @param boolean $isSent Статус отправки
     */
    public function testSendSms($data, $isSent)
    {
        $this->soap
            ->method('__soapCall')
            ->with('sendSMS', [
                [
                    'login' => $this->api->login,
                    'pass' => $this->api->password,
                    'fromPhone' => $this->api->from,
                    'messText' => 'testing',
                    'toPhone' => '71111111111',
                    'userMessId' => 0,
                    'packageId' => 0,
                    'GMT' => 0,
                    'sendDate' => gmdate('d.m.Y H:i:s'),
                    'test' => 0,
                ]
            ])
            ->will($this->returnValue($data));

        $result = $this->api->sendSms('71111111111', 'testing');

        $this->assertInstanceOf(SendSmsResult::class, $result);
        $this->assertEquals($isSent, $result->isSent);
        $this->assertEquals('testing', $result->text);
        $this->assertEquals('71111111111', $result->to);
        $this->assertEquals('testing', $result->from);

        if (empty($data->return)) {
            $this->assertNull($result->providerData);
            $this->assertNull($result->rawProviderData);
        } else {
            $this->assertInstanceOf(\SimpleXMLElement::class, $result->providerData);
            $this->assertEquals($data->return, $result->rawProviderData);
        }

        $this->assertNull($result->errorCode);
        $this->assertNull($result->errorMessage);
    }
}
