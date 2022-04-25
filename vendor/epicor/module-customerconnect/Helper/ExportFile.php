<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Customerconnect\Helper;

class ExportFile
{
    public static function isExportAction($actionName, $module): bool
    {
        return ($actionName === 'exportPaymentsCsv' && $module === 'customerconnect')
            || ($actionName === 'exportPaymentsXml' && $module === 'customerconnect')
            || ($actionName === 'exportInvoicesCsv' && $module === 'customerconnect')
            || ($actionName === 'exportInvoicesXml' && $module === 'customerconnect')
            || ($actionName === 'exportOrdersCsv' && $module === 'customerconnect')
            || ($actionName === 'exportOrdersXml' && $module === 'customerconnect')
            || ($actionName === 'exportShipmentsCsv' && $module === 'customerconnect')
            || ($actionName === 'exportShipmentsXml' && $module === 'customerconnect')
            || ($actionName === 'exportRmasCsv' && $module === 'customerconnect')
            || ($actionName === 'exportRmasXml' && $module === 'customerconnect');
    }
}