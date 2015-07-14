<?php namespace App\Services;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception as GuzzleException;
use GuzzleHttp\Psr7\Request as GuzzleRequest;
use GuzzleHttp\Psr7\Response as GuzzleResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use League\Url\Url;

class PassThrough
{
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
        try {
            $url = Url::createFromUrl($request->fullUrl());

            $host = static::getHostFromUrl($url);

            $client = new Client([
                'base_uri' => $url->getScheme().'://'.$host,
            ]);

            $proxyRequest = new GuzzleRequest($request->method(), $request->path());

            $headers = $request->header();
            array_walk($headers, function ($value, $key) use ($proxyRequest) {
                $proxyRequest->withHeader($key, $value);
            });

            $stream = \GuzzleHttp\Psr7\stream_for(json_encode($request->json()->all()));
            $response = $client->send($proxyRequest, [
                'timeout' => 2,
                'body' => $stream,
                'query' => $request->query(),
                'form_params' => $request->input(),
            ]);

            return static::createLocalResponse($response);
        } catch (Exception $e) {
            if (get_class($e) == GuzzleException\ClientException::class) {
                return static::createLocalResponse($e->getResponse());
            }
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
        $response = new Response(
            $guzzleResponse->getBody(),
            $guzzleResponse->getStatusCode()
        );

        $headers = $guzzleResponse->getHeaders();
        array_walk($headers, function ($values, $name) use ($response) {
            $response->header($name, implode(', ', $values), true);
        });

        return $response;
    }
}
