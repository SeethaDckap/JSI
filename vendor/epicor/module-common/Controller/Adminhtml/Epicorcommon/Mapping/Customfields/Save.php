<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Controller\Adminhtml\Epicorcommon\Mapping\Customfields;

class Save extends \Epicor\Common\Controller\Adminhtml\Epicorcommon\Mapping\Customfields
{

    /**
     * @var \Epicor\Comm\Model\Erp\Mapping\CurrencyFactory
     */
    protected $CustomfieldsFactory;

    protected $configcollectionFactory;

    protected $globalConfig;

    protected $WriterInterface;

    protected $cacheTypeList;

    public function __construct(
        \Epicor\Comm\Controller\Adminhtml\Context $context,
        \Magento\Backend\Model\Auth\Session $backendAuthSession,
        \Magento\Config\Model\ResourceModel\Config\Data\CollectionFactory $configcollectionFactory,
        \Epicor\Comm\Model\GlobalConfig\Config $globalConfig,
        \Magento\Framework\App\Config\Storage\WriterInterface $WriterInterface,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Epicor\Supplierconnect\Model\Erp\Mapping\CustomfieldsFactory $CustomfieldsFactory)
    {
        $this->CustomfieldsFactory = $CustomfieldsFactory;
        $this->configcollectionFactory = $configcollectionFactory;
        $this->globalConfig = $globalConfig;
        $this->WriterInterface = $WriterInterface;
        $this->cacheTypeList = $cacheTypeList;
        parent::__construct($context, $backendAuthSession);
    }

    public function execute()
    {
        if ($data = $this->getRequest()->getPostValue()) {
            $model = $this->CustomfieldsFactory->create();
            $id = $this->getRequest()->getParam('id');
            $deleteEntry = false;
            $editEntry = false;
            if ($id) {
                $model->load($id);
                $oldMessage = $model->getMessage();
                $oldMessageSection = $model->getMessageSection();
                $oldCustomFields = $model->getCustomFields();
                if(($oldMessage !=$data['message']) || ($oldMessageSection !=$data['message_section'])) {
                    $deleteEntry = true;
                }
                if($oldCustomFields !=$data['custom_fields']) {
                    $editEntry = true;
                }
            }
            $model->setData($data);

            $this->_session->setFormData($data);
            try {
                if ($id) {
                    $model->setId($id);
                }
                $model->save();

                if($deleteEntry) {
                    $checkOptions = $this->globalOptions($oldMessage,$oldMessageSection,$oldCustomFields);
                    if($checkOptions) {
                        $this->removeConfigurationInGrid($checkOptions,$oldCustomFields);
                    }
                }

                if($editEntry) {
                    $checkOptions = $this->globalOptions($oldMessage,$oldMessageSection,$oldCustomFields);
                    if($checkOptions) {
                        $this->editConfigurationInGrid($checkOptions,$oldCustomFields,$data['custom_fields']);
                    }
                }

                if (!$model->getId()) {
                    throw new \Magento\Framework\Exception\LocalizedException(__('Error saving mapping'));
                }

                $this->messageManager->addSuccessMessage(__('Mapping was successfully saved.'));
                $this->_session->setFormData(false);
                $preselecteds = $this->_request->getParam('preselectedFields');
                $msec = $this->_request->getParam('msec');
                // The following line decides if it is a "save" or "save and continue"
                $params = array();
                if ($this->getRequest()->getParam('back')) {
                    $params = array('id' => $model->getId());
                    if($preselecteds) {
                        $params = array('id' => $model->getId(),'preselectedFields'=>$preselecteds,'msec'=>$msec);
                    }
                    $this->_redirect('*/*/edit', $params);
                } else {
                    if($preselecteds) {
                        $params = array('preselectedFields'=>$preselecteds,'msec'=>$msec);
                    }
                    $this->_redirect('*/*/',$params);
                }
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                if ($model && $model->getId()) {
                    $this->_redirect('*/*/edit', array('id' => $model->getId()));
                } else {
                    $this->_redirect('*/*/');
                }
            }

            return;
        }
        $this->messageManager->addErrorMessage(__('No data found to save'));
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

    public function editConfigurationInGrid($path,$customFields,$newFields)
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
                            $array[$key]['index'] = $newFields;
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