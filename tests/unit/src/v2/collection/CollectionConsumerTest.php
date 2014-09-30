<?php

/*
 * @author Etienne Lamoureux <etienne.lamoureux@crystalgorithm.com>
 * @copyright 2014 Etienne Lamoureux
 * @license http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 */
namespace Crystalgorithm\DurmandScriptorium\v2\converter;

use Crystalgorithm\DurmandScriptorium\utils\BatchRequestManager;
use Crystalgorithm\DurmandScriptorium\utils\Constants;
use Crystalgorithm\DurmandScriptorium\v2\collection\CollectionConsumer;
use Crystalgorithm\DurmandScriptorium\v2\collection\CollectionRequestFactory;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Message\Request;
use GuzzleHttp\Message\Response;
use Mockery;
use PHPUnit_Framework_TestCase;

class CollectionConsumerTest extends PHPUnit_Framework_TestCase
{

    const VALID_ID = 100;
    const NB_PAGE = 2;

    /**
     * @var ConverterConsumer
     */
    protected $consumer;

    /**
     * @var Client mock
     */
    protected $client;

    /**
     * @var CollectionRequestFactory mock
     */
    protected $requestFactory;

    /**
     * @var BatchRequestManager mock
     */
    protected $batchRequestManager;

    /**
     * @var Request mock
     */
    protected $request;

    /**
     * @var Response mock
     */
    protected $response;

    /**
     * @var ClientException mock
     */
    protected $clientException;

    protected function setUp()
    {
	$this->client = Mockery::mock('\GuzzleHttp\Client');
	$this->requestFactory = Mockery::mock('\Crystalgorithm\DurmandScriptorium\v2\collection\CollectionRequestFactory');
	$this->batchRequestManager = Mockery::mock('\Crystalgorithm\DurmandScriptorium\utils\BatchRequestManager');
	$this->request = Mockery::mock('\GuzzleHttp\Message\Request');
	$this->response = Mockery::mock('\GuzzleHttp\Message\Response');
	$this->clientException = Mockery::mock('\GuzzleHttp\Exception\ClientException');

	$this->consumer = new CollectionConsumer($this->client, $this->requestFactory, $this->batchRequestManager);
    }

    protected function tearDown()
    {
	Mockery::close();
    }

    public function testGivenIdThenGet()
    {
	$this->requestFactory->shouldReceive('idRequest')->with(self::VALID_ID)->once()->andReturn($this->request);
	$this->client->shouldReceive('send')->with($this->request)->once()->andReturn($this->response);
	$this->response->shouldReceive('json')->once();

	$this->consumer->get(self::VALID_ID);
    }

    public function testGivenIdsThenGet()
    {
	$ids = [self::VALID_ID, self::VALID_ID];
	$this->requestFactory->shouldReceive('idsRequest')->with($ids)->once()->andReturn($this->request);
	$this->client->shouldReceive('send')->with($this->request)->once()->andReturn($this->response);
	$this->response->shouldReceive('json')->once();

	$this->consumer->get($ids);
    }

    public function testGivenEmptyIdsThenNeverCall()
    {
	$emptyArray = [];
	$this->requestFactory->shouldReceive('idsRequest')->never();
	$this->client->shouldReceive('send')->never();

	$data = $this->consumer->get($emptyArray);

	$this->assertEquals($emptyArray, $data);
    }

    public function testWhenRequestAllIdsThenGetAllIds()
    {
	$this->requestFactory->shouldReceive('baseRequest')->once()->andReturn($this->request);
	$this->client->shouldReceive('send')->with($this->request)->once()->andReturn($this->response);
	$this->response->shouldReceive('json')->once();

	$this->consumer->getAll();
    }

    public function testWhenRequestAllDetailsThenGetAllDetails()
    {
	$this->requestFactory->shouldReceive('baseRequest')->andReturn($this->request);
	$this->requestFactory->shouldReceive('pageRequest')->atLeast(1)->andReturn($this->request);
	$this->response->shouldReceive('getHeader')->with(Constants::TOTAL_PAGE_HEADER)->andReturn(self::NB_PAGE);
	$this->client->shouldReceive('send')->with($this->request)->atLeast(1)->andReturn($this->response);
	$this->response->shouldReceive('json')->atLeast(1);

	$this->consumer->getAll();
    }

}