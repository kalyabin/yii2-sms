<?php

namespace tests;

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

    /**
     * @covers WebSmsSoapApi::getBalance()
     */
    public function testGetBalanceWithNull()
    {
        $this->soap
            ->expects($this->at(0))
            ->method('__soapCall')
            ->with('getBalance', [
                [
                    'login' => 'login',
                    'pass' => 'password',
                ]
            ])
            ->willReturn(new \stdClass());

        $this->assertEquals(0, $this->api->getBalance());
    }

    /**
     * @covers WebSmsSoapApi::getBalance()
     */
    public function testGetBalanceWithData()
    {
        $expectedResult = new \stdClass();
        $expectedResult->return = '<result><balance>100.5</balance><userid>111</userid></result>';

        $this->soap
            ->method('__soapCall')
            ->with('getBalance', [
                [
                    'login' => 'login',
                    'pass' => 'password',
                ]
            ])
            ->willReturn($expectedResult);

        $this->assertEquals(100.5, $this->api->getBalance());
    }
}
