<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Elasticsearch\Controller\Adminhtml\Product\Rule;

use Magento\Backend\App\Action;
use Magento\Rule\Model\Condition\AbstractCondition;

/**
 * Catalog search rule contribution controller action used to generate new children rules.
 *
 */
class Conditions extends Action
{
    /**
     * @var \Epicor\Elasticsearch\Model\RuleFactory
     */
    protected $ruleFactory;

    /**
     * @var array
     */
    private $acls = [];

    /**
     * Constructor.
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Epicor\Elasticsearch\Model\RuleFactory $ruleFactory
     * @param array $acls
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Epicor\Elasticsearch\Model\RuleFactory $ruleFactory,
        $acls = []
    ) {
        $this->ruleFactory = $ruleFactory;
        $this->acls        = $acls;
        parent::__construct($context);
    }

    /**
     * {@inheritDoc}
     */
    public function execute()
    {
        $conditionId = $this->getRequest()->getParam('id');
        $typeData = explode('|', str_replace('-', '/', $this->getRequest()->getParam('type')));
        $className = $typeData[0];
        $rule = $this->ruleFactory->create();
        $model = $this->_objectManager->create($className)
            ->setId($conditionId)
            ->setType($className)
            ->setRule($rule)
            ->setPrefix('conditions');
        $model->setElementName($this->getRequest()->getParam('element_name'));
        if (!empty($typeData[1])) {
            $model->setAttribute($typeData[1]);
        }
        $result = '';
        if ($model instanceof AbstractCondition) {
            $model->setJsFormObject($this->getRequest()->getParam('form'));
            $model->setData('url_params', $this->getRequest()->getParams());
            $result = $model->asHtmlRecursive();
        }
        $this->getResponse()->setBody($result);
    }
}
