<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Block\Adminhtml;


class Importexport extends \Magento\Backend\Block\Widget
{

    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        array $data = []
    )
    {
        parent::__construct(
            $context,
            $data
        );

        $this->setTemplate('epicor_common/comm_settings_backup/importExport.phtml');
    }

    public function getHeaderText()
    {
        return __('Import / Export Comm Settings');
    }

}
