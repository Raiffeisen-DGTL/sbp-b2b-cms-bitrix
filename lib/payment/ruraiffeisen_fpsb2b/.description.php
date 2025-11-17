<?php

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Sale\PaySystem;

Loc::loadMessages(__DIR__);

$isAvailable = PaySystem\Manager::HANDLER_AVAILABLE_TRUE;

$licensePrefix = Loader::includeModule('bitrix24') ? \CBitrix24::getLicensePrefix() : '';
$portalZone = Loader::includeModule('intranet') ? CIntranetUtils::getPortalZone() : '';

if (Loader::includeModule('bitrix24')) {
  if ($licensePrefix !== 'ru') {
    $isAvailable = PaySystem\Manager::HANDLER_AVAILABLE_FALSE;
  }
} elseif (Loader::includeModule('intranet') && $portalZone !== 'ru') {
  $isAvailable = PaySystem\Manager::HANDLER_AVAILABLE_FALSE;
}

$description = array();

$data = [
  'NAME' => Loc::getMessage('RAIFFEISEN_B2B_PAYMENT_MODULE_NAME'),
  'IS_AVAILABLE' => $isAvailable,
  'CODES' => array(
    'B2B_SBP_TEST_MODE' => array(
      'NAME' => Loc::getMessage('RAIFFEISEN_B2B_PAYMENT_MODULE_CODES_TEST_MODE_NAME'),
      'DESCRIPTION' => Loc::getMessage('RAIFFEISEN_B2B_PAYMENT_MODULE_CODES_TEST_MODE_DESCRIPTION'),
      'SORT' => 100,
      'INPUT' => ['TYPE' => 'Y/N'],
      'GROUP' => 'GENERAL_SETTINGS',
      'DEFAULT' => [
        'PROVIDER_VALUE' => 'N',
        'PROVIDER_KEY' => 'Y/N'
      ]
    ),
    'B2B_SBP_SECRET_KEY' => array(
      'NAME' => Loc::getMessage('RAIFFEISEN_B2B_PAYMENT_MODULE_CODES_SECRET_KEY_NAME'),
      'SORT' => 200,
      'INPUT' => ['TYPE' => 'STRING'],
      'GROUP' => 'GENERAL_SETTINGS',
    ),
    'B2B_SBP_ORDER_ACCOUNT' => array(
      'NAME' => Loc::getMessage('RAIFFEISEN_B2B_PAYMENT_MODULE_CODES_ORDER_ACCOUNT_NAME'),
      'SORT' => 300,
      'INPUT' => ['TYPE' => 'STRING'],
      'GROUP' => 'SELLER_COMPANY',
    ),
    'B2B_SBP_ORDER_RECEIVER_PAYMENT_PURPOSE_PREFIX' => array(
      'NAME' => Loc::getMessage('RAIFFEISEN_B2B_PAYMENT_MODULE_CODES_RECEIVER_PAYMENT_PURPOSE_PREFIX_NAME'),
      'DESCRIPTION' => Loc::getMessage('RAIFFEISEN_B2B_PAYMENT_MODULE_CODES_RECEIVER_PAYMENT_PURPOSE_PREFIX_DESCRIPTION'),
      'SORT' => 400,
      'GROUP' => 'SELLER_COMPANY',
      'DEFAULT' => [
        'PROVIDER_VALUE' => Loc::getMessage('RAIFFEISEN_B2B_PAYMENT_MODULE_CODES_RECEIVER_PAYMENT_PURPOSE_PREFIX_PROVIDER_VALUE'),
        'PROVIDER_KEY' => 'VALUE'
      ]
    ),
    'B2B_SBP_ORDER_SENDER_PAYMENT_PURPOSE_PREFIX' => array(
      'NAME' => Loc::getMessage('RAIFFEISEN_B2B_PAYMENT_MODULE_CODES_SENDER_PAYMENT_PURPOSE_PREFIX_NAME'),
      'DESCRIPTION' => Loc::getMessage('RAIFFEISEN_B2B_PAYMENT_MODULE_CODES_SENDER_PAYMENT_PURPOSE_PREFIX_DESCRIPTION'),
      'SORT' => 500,
      'GROUP' => 'SELLER_COMPANY',
      'DEFAULT' => [
        'PROVIDER_VALUE' => Loc::getMessage('RAIFFEISEN_B2B_PAYMENT_MODULE_CODES_SENDER_PAYMENT_PURPOSE_PREFIX_PROVIDER_VALUE'),
        'PROVIDER_KEY' => 'VALUE'
      ]
    ),
    'B2B_SBP_ORDER_REDIRECT_URL_PREFIX' => array(
      'NAME' => Loc::getMessage('RAIFFEISEN_B2B_PAYMENT_MODULE_CODES_REDIRECT_URL_PREFIX_NAME'),
      'DESCRIPTION' => Loc::getMessage('RAIFFEISEN_B2B_PAYMENT_MODULE_CODES_REDIRECT_URL_PREFIX_DESCRIPTION'),
      'SORT' => 600,
      'GROUP' => 'SELLER_COMPANY',
      'DEFAULT' => [
        'PROVIDER_VALUE' => 'https://' . $_SERVER['SERVER_NAME'] . '/site_hr/personal/order/',
        'PROVIDER_KEY' => 'VALUE'
      ]
    ),
    'B2B_SBP_ORDER_NAME_PREFIX' => array(
      'NAME' => Loc::getMessage('RAIFFEISEN_B2B_PAYMENT_MODULE_CODES_NAME_PREFIX_NAME'),
      'DESCRIPTION' => Loc::getMessage('RAIFFEISEN_B2B_PAYMENT_MODULE_CODES_NAME_PREFIX_DESCRIPTION'),
      'SORT' => 700,
      'GROUP' => 'SELLER_COMPANY',
      'DEFAULT' => [
        'PROVIDER_VALUE' => Loc::getMessage('RAIFFEISEN_B2B_PAYMENT_MODULE_CODES_NAME_PREFIX_DEFAULT_PROVIDER_VALUE'),
        'PROVIDER_KEY' => 'VALUE'
      ]
    ),
    'B2B_SBP_ORDER_EXPIRATION_TTL' => array(
      'NAME' => Loc::getMessage('RAIFFEISEN_B2B_PAYMENT_MODULE_CODES_EXPIRATION_TTL_NAME'),
      'DESCRIPTION' => Loc::getMessage('RAIFFEISEN_B2B_PAYMENT_MODULE_CODES_EXPIRATION_TTL_DESCRIPTION'),
      'SORT' => 800,
      'GROUP' => 'SELLER_COMPANY',
      'DEFAULT' => [
        'PROVIDER_VALUE' => '+60m',
        'PROVIDER_KEY' => 'VALUE'
      ]
    ),
    'B2B_SBP_CALLBACK_URL' => array(
      'NAME' => Loc::getMessage('RAIFFEISEN_B2B_PAYMENT_MODULE_CODES_CALLBACK_URL_NAME'),
      'SORT' => 900,
      'GROUP' => 'SELLER_COMPANY',
      'DEFAULT' => [
        'PROVIDER_VALUE' => 'https://' . $_SERVER['SERVER_NAME'] . '/local/php_interface/include/sale_payment/ruraiffeisen_fps_b2b/callback.php',
        'PROVIDER_KEY' => 'VALUE'
      ],
      'DISABLED' => true,
    ),
  )
];
