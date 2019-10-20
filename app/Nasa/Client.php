<?php

namespace App\Nasa;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\TransferException;

class Client
{
    const FEED1 = "https://firms.modaps.eosdis.nasa.gov/active_fire/c6/text/MODIS_C6_SouthEast_Asia_24h.csv";
    const FEED2 = "https://firms.modaps.eosdis.nasa.gov/active_fire/viirs/text/VNP14IMGTDL_NRT_SouthEast_Asia_24h.csv";

    protected $client;
    protected $data;

    public function __construct(HttpClient $client)
    {
        $this->http_client = $client;
    }

    public function fetch()
    {
        $feed_one = collect(
            explode("\n", $this->fetchFeedOne())
        )->slice(1);

        $feed_two = collect(
            explode("\n", $this->fetchFeedTwo())
        )->slice(1);

        $this->data = $feed_one->merge($feed_two)->flatten()->mapWithKeys(function ($item, $key) {
            $item = explode(',', $item);

            if (count($item) < 2) return [];

            $item = [
                'latitude' => floatval($item[0]),
                'longitude' => floatval($item[1]),
                'brightness' => floatval($item[2]),
                'scan' => floatval($item[3]),
                'track' => floatval($item[4]),
                'acq_date' => $item[5],
                'acq_time' => $item[6],
                'satellite' => $item[7],
                'confidence' => intval($item[8]),
                'version' => $item[9],
                'bright_t31' => floatval($item[10]),
                'frp' => floatval($item[11]),
                'daynight' => $item[12],
            ];

            $hash = md5(implode('+', [
                $item['latitude'],
                $item['longitude'],
                $item['acq_date'],
                $item['acq_time'],
                $item['satellite'],
            ]));

            return [$hash => $item];
        });

        return $this;
    }

    protected function fetchFeedOne()
    {
        try {
            $response = $this->http_client->request('GET', self::FEED1)
                             ->getBody();
        }
        catch (TransferException $e) {
            $response = null;
        }

        return $response;
    }

    protected function fetchFeedTwo()
    {
        try {
            $response = $this->http_client->request('GET', self::FEED2)
                             ->getBody();
        }
        catch(TransferException $e) {
            $response = null;
        }

        return $response;
    }

    public function __get($key)
    {
        if ($key == "data")
        {
            return $this->data;
        }

        parent::__get($key);
    }
}
