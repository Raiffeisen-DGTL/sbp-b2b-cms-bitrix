<?php

use Bitrix\Sale\PaySystem;
use Bitrix\Sale\PaySystem\ServiceResult;
use Sale\Handlers\PaySystem\ruraiffeisen_fpsb2bHandler;
use Bitrix\Main\HttpRequest;
use Bitrix\Main\Server;
use Bitrix\Main\Web\Json;

use Bitrix\Main\Diag\Debug;

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

global $APPLICATION;

try {
  if (CModule::IncludeModule("sale")) {

    $request = new HttpRequest(new Server($_SERVER), [], Json::decode(file_get_contents('php://input')), [], []);

    //Debug::dumpToFile($request,  "callback",  '/raiffeisenpay_logs.log');

    $item = PaySystem\Manager::searchByRequest($request);

    if ($item !== false) {
      $service = new PaySystem\Service($item);
      $handler = new ruraiffeisen_fpsb2bHandler(ServiceResult::MONEY_COMING, $service);
      $result = $handler->processCallback($service, $request);
      $data   = $result->getData()['BACK_URL'];
      if (!empty($data) && isset($data['BACK_URL'])) {
        LocalRedirect($data['BACK_URL']);
      }
    } else {
      $debugInfo = http_build_query($request, "", "\n");
      PaySystem\Logger::addDebugInfo('Pay system not found. Request: ' . ($debugInfo ? $debugInfo : "empty"));
    }


    $APPLICATION->FinalActions();
    die();
  }
} catch (Exception  $e) {
  PaySystem\Logger::addDebugInfo('Callback failed: ' . $e);
}
