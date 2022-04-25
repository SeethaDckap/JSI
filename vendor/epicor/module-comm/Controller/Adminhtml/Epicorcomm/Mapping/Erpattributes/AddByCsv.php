<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Controller\Adminhtml\Epicorcomm\Mapping\Erpattributes;

class AddByCsv extends \Epicor\Comm\Controller\Adminhtml\Epicorcomm\Mapping\Erpattributes
{

/*
     * called by admin/epicorcomm_mapping_erpattributes/addByCsv 
     * Blocks defined in adminhtml_epicorcomm_mapping_erpattributes_addbycsv
     */

    public function __construct(

    ) {
    }
    public function execute()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    }
