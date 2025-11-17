<?php

use Bitrix\Main\Localization\Loc;

IncludeModuleLangFile(__FILE__);


class ruraiffeisen_fpsb2b extends CModule
{
  var $MODULE_ID;
  var $MODULE_VERSION;
  var $MODULE_VERSION_DATE;
  var $MODULE_NAME;
  var $MODULE_DESCRIPTION;
  var $MODULE_PATH;

  var $PARTNER_NAME;
  var $PARTNER_URI;

  function __construct()
  {

    $path = str_replace("\\", "/", __DIR__);
    $path = substr($path, 0, strlen($path) - strlen("/install"));

    include($path . "/install/version.php");

    $this->MODULE_ID = "ruraiffeisen.fpsb2b";
    $this->MODULE_VERSION = $arModuleVersion["VERSION"];
    $this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];

    $this->MODULE_NAME = Loc::getMessage("RAIFFEISEN_B2B_PAYMENT_MODULE_NAME");
    $this->MODULE_DESCRIPTION = Loc::getMessage("RAIFFEISEN_B2B_PAYMENT_MODULE_DESCRIPTION");
    $this->MODULE_PATH = $path;

    $this->PARTNER_NAME = Loc::getMessage("RAIFFEISEN_B2B_PAYMENT_PARTNER_NAME");
    $this->PARTNER_URI = Loc::getMessage("RAIFFEISEN_B2B_PAYMENT_PARTNER_URI");
  }

  function InstallFiles($arParams = array())
  {
    CopyDirFiles(
      $this->MODULE_PATH . "/lib/payment/" . str_replace(".", "_", $this->MODULE_ID), // from
      $_SERVER['DOCUMENT_ROOT'] . "/local/php_interface/include/sale_payment/" . str_replace(".", "_", $this->MODULE_ID) . "/", // to
      true, // rewrite
      true // recursive
    );
    CopyDirFiles(
      $this->MODULE_PATH . "/lib/payment/images",
      $_SERVER["DOCUMENT_ROOT"] . "/bitrix/images/sale/sale_payments/",
      true, // rewrite
      true // recursive
    );
  }

  function DoInstall()
  {
    $this->InstallFiles();
    RegisterModule($this->MODULE_ID);
  }

  function DoUninstall()
  {
    DeleteDirFilesEx($_SERVER['DOCUMENT_ROOT'] . "/local/php_interface/include/sale_payment/" . str_replace(".", "_", $this->MODULE_ID));
    DeleteDirFilesEx($_SERVER["DOCUMENT_ROOT"] . "/bitrix/images/sale/sale_payments/" . str_replace(".", "_", $this->MODULE_ID) . ".png");
    UnRegisterModule($this->MODULE_ID);
    return true;
  }
}
