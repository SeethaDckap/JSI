<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Model\Eav\Attribute\Data;


class Erpaccounttype extends \Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend
{

    /**
     * @var \Epicor\Common\Helper\Account\Selector
     */
    protected $commonAccountSelectorHelper;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    public function __construct(
        \Epicor\Common\Helper\Account\Selector $commonAccountSelectorHelper,
        \Magento\Framework\App\Request\Http $request
    ) {
        $this->commonAccountSelectorHelper = $commonAccountSelectorHelper;
        $this->request = $request;
    }
    /**
     * 
     * @param \Epicor\Comm\Model\Customer $object
     */
    public function beforeSave($object)
    {
        parent::beforeSave($object);

        $attrCode = $this->getAttribute()->getAttributeCode();
        $helper = $this->commonAccountSelectorHelper;
        /* @var $helper Epicor_Common_Helper_Account_Selector */

        if (!$object->hasData($attrCode)) {
            $sortedTypes = $helper->getAccountTypesByPriority();
            $accountType = '';
            foreach ($sortedTypes as $type) {
                if (isset($type['field'])) {
                    $accountId = $object->getData($type['field']);
                    if (!empty($accountId)) {
                        $accountType = $type['value'];
                        break;
                    }
                } else if ($type['priority'] == 0) {
                    $accountType = $type['value'];
                }
            }

            $object->setData($attrCode, $accountType);
        }

        $requestData = $this->request->getParam('account');
        $oldType = $object->getOrigData($attrCode);
        $newType = $object->getData($attrCode);

        if ($oldType != $newType) {
            $accountTypes = $helper->getAccountTypes();

            foreach ($accountTypes as $type => $info) {
                if ($type != $newType && isset($info['field'])) {
                    $object->setData($info['field'], false);
                }
            }
        }

        if ($requestData) {
            $accountTypes = $helper->getAccountTypes();

            $typeInfo = isset($accountTypes[$newType]) ? $accountTypes[$newType] : array();
            $fieldName = isset($typeInfo['field']) ? $typeInfo['field'] : false;
            if ($fieldName && isset($requestData[$fieldName])) {
                $object->setData($fieldName, $requestData[$fieldName]);
            }
        }
    }

}
