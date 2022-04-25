<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Controller\Adminhtml\Epicorcomm\Mapping\Erpattributes;

class ExportXml extends \Epicor\Comm\Controller\Adminhtml\Epicorcomm\Mapping\Erpattributes
{

/*
     * called by admin/epicorcomm_mapping_erpattributes/exportXml    
     */

    public function __construct(

    ) {
    }
    public function execute()
    {
        $fileName = 'erpattributes.xml';
        $content = $this->getLayout()->createBlock('epicor_comm/adminhtml_mapping_erpattributes_grid')
            ->getExcelFile();

        $this->_prepareDownloadResponse($fileName, $content);
    }

    }
