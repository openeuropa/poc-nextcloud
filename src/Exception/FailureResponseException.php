<?php

declare(strict_types = 1);

namespace Drupal\poc_nextcloud\Exception;

/**
 * The API returned a well-formed failure response.
 */
class FailureResponseException extends NextcloudApiException {

  /**
   * Status code from $response['ocs']['meta']['statuscode'].
   *
   * @var int
   */
  private int $responseSatusCode;

  /**
   * Message from $response['ocs']['meta']['message'].
   *
   * @var string
   */
  private string $responseMessage;

  /**
   * Constructor.
   *
   * @param int $statuscode
   *   Status code from $response['ocs']['meta']['statuscode'].
   * @param string $message
   *   Message from $response['ocs']['meta']['message'].
   */
  public function __construct(int $statuscode, string $message) {
    parent::__construct("$statuscode: $message");
    $this->responseSatusCode = $statuscode;
    $this->responseMessage = $message;
  }

  /**
   * Gets the response status code.
   *
   * @return int
   *   Status code from $response['ocs']['meta']['statuscode'].
   */
  public function getResponseStatusCode(): int {
    return $this->responseSatusCode;
  }

  /**
   * Gets the response message.
   *
   * @return string
   *   Message from $response['ocs']['meta']['message'].
   */
  public function getResponseMessage(): string {
    return $this->responseMessage;
  }

}