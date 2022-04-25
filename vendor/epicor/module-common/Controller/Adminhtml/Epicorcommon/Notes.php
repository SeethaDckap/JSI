<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Controller\Adminhtml\Epicorcommon;


/**
 * Common ImportExport controller
 *
 * This controls the import and export function in the admin
 * @category   Epicor
 * @package    Epicor_Common
 * @author     Epicor Websales Team
 * 
 * when adding a table to  the array, the key values indicate what will be part of the addFieldToFilter parm
 * the Id value is the value of the table id (usually id or entity_id, but can be different)
 * 
 * 
 */
abstract class Notes extends \Epicor\Comm\Controller\Adminhtml\Generic
{

    protected $_baseUrl = 'http://update.epicorcommerce.com/notes/';
}
