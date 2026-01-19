<?php
namespace Gentux\Healthz\Checks\General;

use Gentux\Healthz\Exceptions\HealthFailureException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;
use Gentux\Healthz\HealthCheck;
use Psr\Http\Message\ResponseInterface;

/**
 * Health check for HTTP endpoints
 *
 * @package Gentux\Healthz
 */
class HttpHealthCheck extends HealthCheck
{

    /** @var string */
    protected $title = 'HTTP';

    /** @var Request */
    protected $request;

    /** @var int */
    protected $expectedStatusCode;

    /** @var array - will be passed on client->send */
    protected $guzzleOptions;

    /** @var Client */
    protected $guzzle;

    public function __construct(Request $request, $expectedStatusCode = 200, array $guzzleOptions = [], ?Client $guzzle = null)
    {
        $this->request = $request;
        $this->expectedStatusCode = $expectedStatusCode;
        $this->guzzleOptions = $guzzleOptions;
        $this->guzzle = $guzzle ?: new Client($this->guzzleOptions);
    }

    /**
     * Send the request
     *
     * @return ResponseInterface
     *
     * @throws HealthFailureException|RequestException
     */
    public function run(): ResponseInterface
    {
        try {
            $response = $this->guzzle()->send(
                $this->request(),
                $this->guzzleOptions()
            );
        } catch (RequestException $e) {
            if (!$response = $e->getResponse()) {
                throw $e;
            }
        }

        if ($response->getStatusCode() !== $this->expectedStatusCode()) {
            $message = "Status code {$response->getStatusCode()} does not match expected {$this->expectedStatusCode()}";
            throw new HealthFailureException($message);
        }

        return $response;
    }

    /**
     * Get request object to send
     *
     * @return Request
     */
    public function request()
    {
        return $this->request;
    }

    /**
     * Set request object to send
     *
     * @param Request $request
     *
     * @return $this
     */
    public function setRequest(Request $request)
    {
        $this->request = $request;

        return $this;
    }

    /**
     * Get the expected status code for the request
     *
     * @return int
     */
    public function expectedStatusCode()
    {
        return $this->expectedStatusCode;
    }

    /**
     * Set the expected status code from the request
     *
     * @param $statusCode
     *
     * @return $this
     */
    public function setExpectedStatusCode($statusCode)
    {
        $this->expectedStatusCode = $statusCode;

        return $this;
    }

    /**
     * Get Guzzle client options
     *
     * @return array
     */
    public function guzzleOptions()
    {
        return $this->guzzleOptions;
    }

    /**
     * Set options for Guzzle client
     *
     * These will be passed as the request options on client->send
     * @see http://docs.guzzlephp.org/en/latest/request-options.html
     *
     * @param array $guzzleOptions
     *
     * @return $this
     */
    public function setGuzzleOptions(array $guzzleOptions)
    {
        $this->guzzleOptions = $guzzleOptions;

        return $this;
    }

    /**
     * Get Guzzle client instance
     *
     * @return \GuzzleHttp\Client
     */
    public function guzzle()
    {
        return $this->guzzle;
    }

    /**
     * If no description is set, we will use the request URL
     *
     * @return string
     */
    public function description(): ?string
    {
        $description = $this->description;

        if (!$description) {
            $description = (string) $this->request()->getUri();
        }

        return $description;
    }
}
