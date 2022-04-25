<?php

namespace Epicor\AccessRight\Controller\Adminhtml\Roles;

class Condition extends \Epicor\AccessRight\Controller\Adminhtml\Roles
{
    /**
     * New condition html action
     *
     * @return void
     */
    public function execute()
    {
        $role = $this->loadEntity();
        /* @var $role \Epicor\AccessRight\Model\RoleModel */
        $viewType = $this->getRequest()->getParam('view_type');
        $conditionModel = null;

        // EPR TAB
        if($viewType == "erpaccounts") {
            $conditionModel = $role->getErpAccountModel();
            /* @var $conditionModel \Epicor\AccessRight\Model\RoleModel\Erp\Account */
        } elseif($viewType == "customers") { //Customer TAB
            $conditionModel = $role->getCustomerModel();
            /* @var $conditionModel \Epicor\AccessRight\Model\RoleModel\Customer */
        }

        $id = $this->getRequest()->getParam('id');
        $typeArr = explode('|', str_replace('-', '/', $this->getRequest()->getParam('type')));
        $type = $typeArr[0];

        $model = $this->_objectManager->create(
            $type
        )->setId(
            $id
        )->setType(
            $type
        )->setRule(
            $conditionModel
        )->setPrefix(
            'conditions'
        );
        if (!empty($typeArr[1])) {
            $model->setAttribute($typeArr[1]);
        }

        if ($model instanceof \Magento\Rule\Model\Condition\AbstractCondition) {
            $model->setJsFormObject($this->getRequest()->getParam('form'));
            $html = $model->asHtmlRecursive();
        } else {
            $html = '';
        }
        $this->getResponse()->setBody($html);
    }
}