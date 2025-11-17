<?php

namespace Sale\Handlers\PaySystem;


include_once(dirname(__FILE__) . "/client/Client.php");
include_once(dirname(__FILE__) . "/client/ClientException.php");

use Bitrix\Main\Request;
use Bitrix\Main\Diag\Debug;
use Bitrix\Sale;
use Bitrix\Sale\Payment;
use Bitrix\Sale\PaySystem;
use Bitrix\Sale\PaySystem\Service;
use Bitrix\Sale\PaySystem\ServiceResult;
use Exception;
use Raiffeisen\FpsB2B;
use Raiffeisen\FpsB2B\ClientException;


class ruraiffeisen_fpsb2bHandler extends PaySystem\ServiceHandler
{

  /**
   * @param Payment $payment
   * @param Request|null $request
   * @return ServiceResult
   */
  public function initiatePay(Payment $payment, Request $request = null): ServiceResult
  {
    $secretKey = $this->getBusinessValue($payment, "B2B_SBP_SECRET_KEY");

    $client = new FpsB2B\Client($secretKey, $payment);

    //Debug::dumpToFile($payment, "payment", '/raiffeisenpay_logs.log');

    $order = null;
    try {
      $order = $client->getOrder($payment);
    } catch (ClientException $e) {
      if ($e->getHttpCode() === 404) {
        $order = $client->createOrder($payment);
      }
    }

    $params = array(
      "QR_URL" => $order['qr']['payload'],
      "IMAGE" => $order["qr"]["imageUrl"],
      "ORDER" => $order
    );
    $this->setExtraParams($params);
    //Debug::dumpToFile($this->getExtraParams(), "extra-params", '/raiffeisenpay_logs.log');

    return $this->showTemplate($payment, 'template');
  }

  /**
   * @return array
   */
  public function getCurrencyList(): array
  {
    return array('RUB');
  }

  /**
   * Identifies paysystem by GET parameter.
   *
   * @return array
   */
  public static function getIndicativeFields()
  {
    return ['data'];
  }

  /**
   * @param Request $request
   * @param $paySystemId
   * @return bool
   */
  protected static function isMyResponseExtended(Request $request, $paySystemId)
  {
    $orderId = $request->get('data')['id'];
    $order = Sale\Order::loadByAccountNumber($orderId);
    return $order->getField('PAY_SYSTEM_ID') == $paySystemId;
  }

  /**
   * @param Service $payment
   * @param Request $request
   * @return ServiceResult
   */
  public function processCallback(Service $payment, Request $request): ServiceResult
  {
    $result = new ServiceResult();

    //Debug::dumpToFile($payment, "processRequestCustom", '/raiffeisenpay_logs.log');
    $status = $request->get("data")["status"];

    if ($status == 'PAID') {
      $result = $this->processSuccessCallback($payment, $request);
    }

    return $result;
  }

  private function processSuccessCallback(Service $payment, Request $request): ServiceResult
  {
    $data = $request->get('data');
    $result = new ServiceResult();

    $orderId = $data["id"];

    $order = Sale\Order::loadByAccountNumber($orderId);
    //Debug::dumpToFile($order, "order", '/raiffeisenpay_logs.log');
    $paymentCollection = $order->getPaymentCollection();

    foreach ($paymentCollection as $_payment_) {

      if ( $_payment_->getPaymentSystemId() == $payment->getField('PAY_SYSTEM_ID') && $_payment_->getSum() == $data['amount']) {
        try {
          $_payment_->setPaid("Y");

          $order->setField('STATUS_ID', 'P');
          $order->save();
        } catch (Exception $e) {
          //Debug::dumpToFile($e, "Callback Exception", '/raiffeisenpay_logs.log');
        }
      }
    }

    if ($request->get('back')) {
      $data['BACK_URL'] = urldecode($request->get('back'));
    }

    if (isset($data)) {
      $result->setData($data);
    }

    return $result;
  }

  public function processRequest(Payment $payment, Request $request): ServiceResult
  {
    $result = new ServiceResult();
    return $result;
  }

  public function getPaymentIdFromRequest(Request $request)
  {
    $pid = $request->get('id');
    if ($pid) {
      return $pid;
    }
    $body = file_get_contents('php://input');
    if ($body) {
      $reqData = Json::decode($body);
    }
    // Log notifies from ...
    $this->log('NOTIFY', ['pid' => $pid, 'reqData' => $body]);
    if (isset($reqData) && isset($reqData['payment']['id'])) {
      $pid = $reqData['payment']['id'];
      if (!$pid) {
        http_response_code(404);
        die();
      }

      return $pid;
    }

    http_response_code(404);
    die();
  }
}
