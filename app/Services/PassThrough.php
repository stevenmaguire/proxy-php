<?php namespace App\Services;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request as GuzzleRequest;
use GuzzleHttp\Psr7\Response as GuzzleResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use League\Url\Url;

class PassThrough
{
    /**
     * Headers to pass to upstream
     *
     * @var array
     */
    protected static $headersToPass = ['Content-Type', 'X-Pagination'];

    /**
     * Parse host information from keyed local host name
     *
     * @param  Request  $request
     *
     * @return void
     */
    protected static function getHostFromUrl(Url $url)
    {
        if (count($url->getHost()) > 2) {
            return preg_replace('/\-/', '.', $url->getHost()[0]);
        }

        return null;
    }

    /**
     * Send request upstream
     *
     * @param  Request  $request
     *
     * @return Response
     * @throws HttpException
     */
    public static function makeRequest(Request $request)
    {
        // params
        // headers

        try {
            $url = Url::createFromUrl($request->fullUrl());

            $host = static::getHostFromUrl($url);

            $client = new Client([
                'base_uri' => $url->getScheme().'://'.$host,
            ]);

            $proxyRequest = new GuzzleRequest($request->method(), $request->path());

            $response = $client->send($proxyRequest, ['timeout' => 2]);

            return static::createLocalResponse($response);
        } catch (Exception $e) {
            abort(404);
        }
    }

    /**
     * Attempt to create local response type from guzzle response
     *
     * @param  GuzzleResponse $guzzleResponse
     *
     * @return Response
     */
    protected static function createLocalResponse(GuzzleResponse $guzzleResponse)
    {
        $response = new Response;
        $response->setContent($guzzleResponse->getBody());

        foreach ($guzzleResponse->getHeaders() as $name => $values) {
            $response->header($name, implode(', ', $values), true);
        }

        return $response;
    }
}
