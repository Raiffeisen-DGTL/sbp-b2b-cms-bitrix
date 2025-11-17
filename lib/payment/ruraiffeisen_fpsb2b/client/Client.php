<?php
/**
 * Ecommerce payment API SDK
 *
 * @package   Raiffeisen\Fps B2B
 * @copyright 2022 (c) Raiffeisenbank JSC
 * @license   MIT https://raw.githubusercontent.com/Raiffeisen-DGTL/ecom-sdk-php/master/LICENSE
 */

namespace Raiffeisen\FpsB2B;

use Exception;
use Bitrix\Main\Diag\Debug;
use Bitrix\Sale\Payment;
use Bitrix\Sale\PaySystem\Manager;
use Bitrix\Sale\BusinessValue;

if (false === defined('CLIENT_NAME')) {
  //phpcs:disable Squiz.Commenting -- Because contingent constant definition.
  /**
   * The client name fingerprint.
   *
   * @const string
   */
  define('CLIENT_NAME', 'php_sdk');
  //phpcs:enable Squiz.Commenting
}

if (false === defined('CLIENT_VERSION')) {
  //phpcs:disable Squiz.Commenting -- Because contingent constant definition.
  /**
   * The client version fingerprint.
   *
   * @const string
   */
  define(
    'CLIENT_VERSION',
    @json_decode(
      file_get_contents(dirname(__DIR__) . DIRECTORY_SEPARATOR . 'composer.json'),
      true
    )['version']
  );
  //phpcs:enable Squiz.Commenting
}

/**
 * Client for FPS B2B API.
 *
 * @see https://pay.raif.ru/doc/sbp_b2b.html API Documentation.
 *
 * @property string $secretKey  The secret key, is set-only.
 * @property string $host       The API URL host.
 * @property resource $curl       The request, is get-only.
 */
class Client
{
  /**
   * The default separator.
   *
   * @const string
   */
  const VALUE_SEPARATOR = '|';

  /**
   * The default hash algorithm.
   *
   * @const string
   */
  const DEFAULT_ALGORITHM = 'sha256';

  /**
   * The API datetime format.
   *
   * @const string
   */
  const DATETIME_FORMAT = 'Y-m-d\TH:i:sP';

  /**
   * The API number decimal separator.
   *
   * @const string
   */
  const NUMBER_SEPARATOR = '.';

  /**
   * The API get method.
   *
   * @const string
   */
  const GET = 'GET';

  /**
   * The API post method.
   *
   * @const string
   */
  const POST = 'POST';

  /**
   * The API put method.
   *
   * @const string
   */
  const PUT = 'PUT';

  /**
   * The API delete method.
   *
   * @const string
   */
  const DELETE = 'DELETE';

  /**
   * The production API host.
   *
   * @const string
   */
  const HOST_PROD = 'https://pay.raif.ru';

  /**
   * The test API host.
   *
   * @const string
   */
  const HOST_TEST = 'https://pay-test.raif.ru';

  /**
   * The default URL to order.
   *
   * @const string
   */
  const ORDER_URI = '/api/b2b/sbp/v1/orders';

  /**
   * The secret key.
   *
   * @var string
   */
  protected $secretKey;

  /**
   * The API host.
   *
   * @var string
   */
  protected $host;

  /**
   * The request.
   *
   * @var resource
   */
  protected $internalCurl;


  /**
   * Client constructor.
   *
   * @param string $secretKey The secret key.
   * @param array $options The dictionary of request options.
   */
  public function __construct(string $secretKey, Payment $payment, array $options = [])
  {

    $paySystem = Manager::getObjectById($payment->getPaymentSystemId());
    $consumerName = $paySystem->getConsumerName();

    $this->secretKey = $secretKey;
    $this->host = BusinessValue::getMapping('B2B_SBP_TEST_MODE', $consumerName)['PROVIDER_VALUE'] === 'Y' ? self::HOST_TEST : self::HOST_PROD;
    $this->internalCurl = curl_init();
    curl_setopt_array(
      $this->internalCurl,
      ([
          CURLOPT_USERAGENT => CLIENT_NAME . '-' . CLIENT_VERSION,
        ] + $options)
    );

  }

  /**
   * Get orderId
   *
   * @param Payment $payment
   * @return string
   */
  private function __getOrderId(Payment $payment): string
  {
    return $payment->getOrder()->getField('ACCOUNT_NUMBER');
  }

  /**
   * Get order
   *
   * @param Payment $payment
   * @return mixed
   * @throws Exception
   */
  public function getOrder(Payment $payment)
  {

    $orderId = $this->__getOrderId($payment);

    //Debug::dumpToFile($orderId, "orderId", '/raiffeisenpay_logs.log');

    return $this->sendRequest(self::ORDER_URI . "/" . $orderId, self::GET);
  }


  /**
   * Create new order
   *
   * @param Payment $payment
   * @return mixed
   * @throws Exception
   */
  public function createOrder(Payment $payment)
  {
    $paySystem = Manager::getObjectById($payment->getPaymentSystemId());
    $consumerName = $paySystem->getConsumerName();

    $data = [
      'id' => $this->__getOrderId($payment),
      'name' => BusinessValue::getMapping('B2B_SBP_ORDER_NAME_PREFIX', $consumerName)['PROVIDER_VALUE'] . $this->__getOrderId($payment),
      'account' => BusinessValue::getMapping('B2B_SBP_ORDER_ACCOUNT', $consumerName)['PROVIDER_VALUE'],
      'amount' => $payment->getOrder()->getPrice(),
      'totalTaxAmount' => $payment->getOrder()->getVatSum(),
      'expirationDate' => BusinessValue::getMapping('B2B_SBP_ORDER_EXPIRATION_TTL', $consumerName)['PROVIDER_VALUE'],
      'receiverPaymentPurpose' => BusinessValue::getMapping('B2B_SBP_ORDER_RECEIVER_PAYMENT_PURPOSE_PREFIX', $consumerName)['PROVIDER_VALUE'] . $this->__getOrderId($payment),
      'senderPaymentPurpose' => BusinessValue::getMapping('B2B_SBP_ORDER_SENDER_PAYMENT_PURPOSE', $consumerName)['PROVIDER_VALUE'] . $this->__getOrderId($payment),
      'redirectUrl' => BusinessValue::getMapping('B2B_SBP_ORDER_REDIRECT_URL_PREFIX', $consumerName)['PROVIDER_VALUE'] . $this->__getOrderId($payment),
      'extra' => [
        "apiClient" => "BITRIX",
        "paymentId" => (string) $payment->getId(),
        "userId" => (string) $payment->getOrder()->getUserId(),
        "email" => $payment->getOrder()->getPropertyCollection()->getUserEmail()->getValue(),
      ],
    ];

    return $this->sendRequest(self::ORDER_URI, self::POST, $data);
  }

  /**
   * Send request
   *
   * @param string $url
   * @param array $headers
   * @param array $data
   * @return string
   * @throws Exception
   */
  /**
   * Build request.
   *
   * @param string $url    The url.
   * @param string $method The method.
   * @param array  $body   The body.
   *
   * @return mixed Return response.
   *
   * @throws Exception Throw on unsupported $method use.
   * @throws ClientException Throw on API return invalid response.
   */
  protected function sendRequest(string $url, string $method, array $body=[])
  {
    $curl    = curl_copy_handle($this->internalCurl);
    $headers = [
      'Accept: application/json',
      'Authorization: Bearer '.$this->secretKey,
    ];
    if (true !== empty($body) && self::GET !== $method) {
      $body    = json_encode($body, JSON_UNESCAPED_UNICODE);
      $headers = array_merge(
        $headers,
        [
          'Content-Type: application/json;charset=UTF-8',
          'Content-Length: '.strlen($body),
        ]
      );
    }

    //Debug::dumpToFile($method, "method", '/raiffeisenpay_logs.log');
    //Debug::dumpToFile($url, "url", '/raiffeisenpay_logs.log');
    //Debug::dumpToFile($headers, "headers", '/raiffeisenpay_logs.log');
    //Debug::dumpToFile($body, "body", '/raiffeisenpay_logs.log');
    //Debug::dumpToFile(json_encode($body, JSON_UNESCAPED_UNICODE), "body-json", '/raiffeisenpay_logs.log');

    curl_setopt_array(
      $curl,
      [
        CURLOPT_URL            => $this->host.$url,
        CURLOPT_HTTPHEADER     => $headers,
        CURLOPT_CUSTOMREQUEST  => $method,
        CURLOPT_POSTFIELDS     => $body,
        CURLOPT_RETURNTRANSFER => 1,
      ]
    );

    $response = curl_exec($curl);

    //Debug::dumpToFile($response, "response", '/raiffeisenpay_logs.log');

    if ($response === false) {
      $error = curl_error($curl);
      curl_close($curl);
      throw new ClientException($curl, "Ошибка cURL: $error");
    }

    $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    $httpHeaders = curl_getinfo($curl, CURLINFO_HEADER_OUT);
    $effectiveUrl = curl_getinfo($curl, CURLINFO_EFFECTIVE_URL);

    //Debug::dumpToFile($httpCode, "httpCode", '/raiffeisenpay_logs.log');

    curl_close($curl);

    if ($httpCode >= 400) {
      throw new ClientException($curl, "Request failed [httpCode=$httpCode, url=$effectiveUrl, header=$httpHeaders, response=$response]");
    }

    $result = json_decode($response, true);

    //Debug::dumpToFile($result, "result", '/raiffeisenpay_logs.log');

    if (json_last_error() !== JSON_ERROR_NONE) {
      throw new ClientException($curl, "Ошибка декодирования JSON [response= $response]");
    }

    if (isset($result['error'])) {
      throw new ClientException($curl, "Ошибка по API [result=$result]");
    }

    return $result;
  }

}
