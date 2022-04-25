<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Controller\Adminhtml\Epicorcommon\Importexport;

class Import extends \Epicor\Common\Controller\Adminhtml\Epicorcommon\Importexport
{

    /**
     * @var \Magento\Framework\Session\Generic
     */
    protected $generic;

    public function __construct(
        \Magento\Framework\Session\Generic $generic
    ) {
        $this->generic = $generic;
    }
    public function execute()
    {

        if ($this->getRequest()->getParam('importfile')) {
            $this->_serializedArray = unserialize(base64_decode($this->getRequest()->getParam('importfile')));
//            $this->_serializedArray = base64_decode(unserialize($this->getRequest()->getParam('importfile')));
        } else {
            $this->_serializedArray = unserialize($this->_serializedArray);
        }
        foreach ($this->_serializedArray as $module => $value) {
            $this->_module = $module;
            foreach ($value as $key2 => $value2) {
                $this->_table = $key2;
                $this->_key = $value2['key'];
                $this->_id = $value2['id'];
                if ($value2['data'] != 'No Data') {                       // don't load if no data
                    $this->_unserializedArray = unserialize($value2['data']);
                    if ($this->_module . "_" . $this->_table == 'core_config_data') {
                        $this->importConfigData();
                    } else {
                        $this->importTableData();                           // all other tables
                    }
                } else {
                    $this->messageManager->addNoticeMessage(__("{$this->_table} has no data to import"));
                }
            }
        }

// if it gets here, all data has been imported successfully
        $this->messageManager->addSuccessMessage(__("All data imported successfully"));
//        $this->_redirectReferer();
        $this->_redirect('*/*/index');
    }
}
