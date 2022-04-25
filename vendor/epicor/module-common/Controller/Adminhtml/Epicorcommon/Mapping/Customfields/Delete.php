<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Controller\Adminhtml\Epicorcommon\Mapping\Customfields;

class Delete extends \Epicor\Common\Controller\Adminhtml\Epicorcommon\Mapping\Customfields
{

    /**
     * @var \Epicor\Comm\Model\Erp\Mapping\CurrencyFactory
     */
    protected $customfieldsFactory;

    protected $enabledMessages = array('customerconnect_enabled_messages');

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $configcollectionFactory;

    protected $globalConfig;

    protected $WriterInterface;

    protected $cacheTypeList;

    public function __construct(\Epicor\Comm\Controller\Adminhtml\Context $context,
                                \Epicor\Supplierconnect\Model\Erp\Mapping\CustomfieldsFactory $customfieldsFactory,
                                \Magento\Config\Model\ResourceModel\Config\Data\CollectionFactory $configcollectionFactory,
                                \Epicor\Comm\Model\GlobalConfig\Config $globalConfig,
                                \Magento\Framework\App\Config\Storage\WriterInterface $WriterInterface,
                                \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
                                \Magento\Backend\Model\Auth\Session $backendAuthSession)
    {
        $this->customfieldsFactory = $customfieldsFactory;
        $this->configcollectionFactory = $configcollectionFactory;
        $this->globalConfig = $globalConfig;
        $this->WriterInterface = $WriterInterface;
        $this->cacheTypeList = $cacheTypeList;
        parent::__construct($context, $backendAuthSession);
    }

    public function execute()
    {
        if ($id = $this->getRequest()->getParam('id')) {
            try {
                $preselecteds = $this->_request->getParam('preselectedFields');
                $msec         = $this->_request->getParam('msec');
                $urlAppend    = '';
                if ($preselecteds) {
                    $urlAppend = 'preselectedFields/' . $preselecteds . '/msec/' . $msec;
                }
                $model = $this->customfieldsFactory->create();
                $model->load($id);
                $checkOptions = $this->globalOptions($model->getMessage(),$model->getMessageSection(),$model->getCustomFields());
                if($checkOptions) {
                    $this->removeConfigurationInGrid($checkOptions,$model->getCustomFields());
                }
                $model->delete();
                $this->messageManager->addSuccessMessage(__('The custom fields has been deleted.'));
                $this->_redirect('*/*/index/' . $urlAppend);
                return;
            }
            catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                $this->_redirect('*/*/edit', array(
                    'id' => $this->getRequest()->getParam('id')
                ));
                return;
            }
        }
        $this->messageManager->addErrorMessage(__('Unable to find the example to delete.'));
        $this->_redirect('*/*/');
    }



    public function removeConfigurationInGrid($path,$customFields)
    {
        $getDatas = array();
        $erpInfo = $this->configcollectionFactory->create()->addFieldToFilter('path', array('eq' => $path));
        $getDatas = $erpInfo->getData();
        if(count($getDatas) > 0 ) {
            foreach ($getDatas as $valueinfos) {
                if (!empty($valueinfos['value'])) {
                    $exist = true;
                    $array = unserialize($valueinfos['value']);
                    foreach ($array as $key => &$val) {
                        if($array[$key]['index'] ==$customFields) {
                            unset($array[$key]);
                        }
                    }
                    $serializeArray = serialize($array);
                    $this->WriterInterface->save($valueinfos['path'], $serializeArray,$valueinfos['scope'],$valueinfos['scope_id']);
                    $this->cacheTypeList->cleanType('config');
                }
            }

        }
    }


    public function globalOptions($message,$messageSection,$customFields)
    {
        $request = (array) $this->globalConfig->get('mapping_xml_request/messages');
        $_upload = '';
        $lowerVals = strtolower($message);
        foreach ($request as $key => $request) {
            if($lowerVals ==$key) {
                $_upload = $request[$messageSection];
            }
        }
        return $_upload;
    }

    public function decamelize($string) {
        return strtolower(preg_replace(['/([a-z\d])([A-Z])/', '/([^_])([A-Z][a-z])/'], '$1_$2', $string));
    }

}