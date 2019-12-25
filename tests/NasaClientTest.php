<?php

namespace Tests;
//importing libraries
use Mockery\Mockery;
use App\Nasa\Client as NasaClient;
use GuzzleHttp\Client as HttpClient;
use App\Jobs\FetchDataFromNasaJob;
use Psr\Http\Message\ResponseInterface;

//tests getting NASA data
class NasaClientTest extends TestCase
{
    //main function
    public function TestGetData()
    {
        $http_client = Mockery::mock(HttpClient::class);

        $http_client->shouldReceive('request')
                    ->once()
                    ->andReturn(Mockery::mock(ResponseInterface::class));

        $nasa_client = new NasaClient($http_client);
        $data = $nasa_client->fetch()->data;

        $this->assertInstanceOf('Illuminate\Support\Collection', $data);
        $this->assertCount(1, $data);
    }
}
