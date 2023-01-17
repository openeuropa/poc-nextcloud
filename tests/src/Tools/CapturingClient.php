<?php

declare(strict_types = 1);

namespace Drupal\Tests\poc_nextcloud\Tools;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\Assert;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Http client that can record and replay responses.
 *
 * The client has two modes:
 * - If a decorated client is provided, it will forward requests to the
 *   decorated client, and record the request and response.
 * - If a decorated client is not provided, it will assert that the request data
 *   is as previously recorded, and return the pre-recorded response data.
 *
 * Request and response data are stored in an array format suitable for yml.
 */
class CapturingClient implements ClientInterface {

  /**
   * Index within pre-recorded traffic.
   *
   * @var int
   */
  private int $index = 0;

  /**
   * Constructor.
   *
   * @param \GuzzleHttp\ClientInterface|null $decorated
   *   Decorated client.
   * @param array[] $traffic
   *   Pre-recorded traffic.
   *
   * @psalm-param list<array{request: array, response: array}> $traffic
   */
  public function __construct(
    private ?ClientInterface $decorated,
    private array &$traffic,
  ) {
    if ($this->decorated) {
      $this->traffic = [];
    }
  }

  /**
   * Creates a new instance based on env variables.
   *
   * @param array[] $traffic
   *   Pre-recorded traffic.
   *
   * @psalm-param list<array{request: array, response: array}> $traffic
   *
   * @return self
   *   New instance.
   */
  public static function create(array &$traffic): self {
    $client = getenv('REAL_CLIENT') ? new Client() : NULL;
    return new self($client, $traffic);
  }

  /**
   * {@inheritdoc}
   */
  public function send(RequestInterface $request, array $options = []) {
    throw new \RuntimeException('Not implemented');
  }

  /**
   * {@inheritdoc}
   */
  public function sendAsync(RequestInterface $request, array $options = []) {
    throw new \RuntimeException('Not implemented');
  }

  /**
   * {@inheritdoc}
   */
  public function request($method, $uri, array $options = []) {
    $request_data = $this->buildRequestData($method, $uri, $options);
    if (!$this->decorated) {
      // Use pre-recorded request and response.
      $record = $this->traffic[$this->index] ?? NULL;
      if ($record === NULL) {
        Assert::fail('End of pre-recorded traffic reached.');
      }
      Assert::assertSame($record['request'], $request_data);
      $response = $this->restoreRecordedResponse($record['response']);
    }
    else {
      // Update pre-recorded request and response.
      $response = $this->decorated->request($method, $uri, $options);
      $this->traffic[$this->index] = [
        'request' => $request_data,
        'response' => $this->buildResponseData($response),
      ];
    }
    ++$this->index;
    return $response;
  }

  /**
   * Converts request data into an array suitable for yml.
   *
   * @param string $method
   *   E.g. 'GET' or 'POST'.
   * @param string $uri
   *   Request url.
   * @param array $options
   *   Request options.
   *
   * @return array
   *   Data suitable for yml.
   */
  private function buildRequestData(string $method, string $uri, array $options): array {
    unset($options['auth'], $options['debug'], $options['headers']);
    return ['method' => $method, 'uri' => $uri] + $options;
  }

  /**
   * Converts a response object into an array suitable for yml.
   *
   * @param \Psr\Http\Message\ResponseInterface $response
   *   Response object.
   *
   * @return array
   *   Data suitable for yml.
   */
  private function buildResponseData(ResponseInterface $response): array {
    $response_data = [
      'status' => $response->getStatusCode(),
      'body' => (string) $response->getBody(),
    ];
    try {
      $response_data['data'] = json_decode($response_data['body'], TRUE, JSON_THROW_ON_ERROR);
      unset($response_data['body']);
    }
    catch (\Throwable) {
      // Not valid json.
      // Keep the $response_data['body'], don't set $response_data['data'].
    }
    return $response_data;
  }

  /**
   * Restores a response object based on recorded response data.
   *
   * @param array $response_data
   *   Response data that was previously recorded.
   *
   * @return \GuzzleHttp\Psr7\Response
   *   New response object.
   */
  private function restoreRecordedResponse(array $response_data): Response {
    if (isset($response_data['data'])) {
      $response_data['body'] = json_encode($response_data['data']);
    }
    return new Response(
      $response_data['status'] ?? 200,
      // Headers are irrelevant.
      [],
      $response_data['body'] ?? NULL,
    );
  }

  /**
   * {@inheritdoc}
   */
  public function requestAsync($method, $uri, array $options = []) {
    throw new \RuntimeException('Not implemented');
  }

  /**
   * {@inheritdoc}
   */
  public function getConfig($option = NULL) {
    throw new \RuntimeException('Not implemented');
  }

}