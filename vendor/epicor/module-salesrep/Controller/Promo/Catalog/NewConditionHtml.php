<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\SalesRep\Controller\Promo\Catalog;

class NewConditionHtml extends \Magento\Framework\App\Action\Action
{

    /**
     * @var \Magento\Catalog\Model\CategoryFactory
     */
    protected $catalogCategoryFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\CatalogRule\Model\RuleFactory
     */
    protected $catalogRuleRuleFactory;

    /**
     * @var \Epicor\SalesRep\Helper\RuleReader
     */
    protected $ruleReader;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Catalog\Model\CategoryFactory $catalogCategoryFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Registry $registry,
        \Magento\CatalogRule\Model\RuleFactory $catalogRuleRuleFactory,
        \Epicor\SalesRep\Helper\RuleReader $ruleReader
    ) {
        $this->catalogCategoryFactory = $catalogCategoryFactory;
        $this->storeManager = $storeManager;
        $this->registry = $registry;
        $this->catalogRuleRuleFactory = $catalogRuleRuleFactory;
        $this->ruleReader = $ruleReader;

        parent::__construct(
            $context
        );
    }



    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        $formName = $this->getRequest()->getParam('form_namespace');

        $typeArr = explode('|', str_replace('-', '/', $this->getRequest()->getParam('type')));
        $type = $typeArr[0];

        $model =  $this->ruleReader->getRule($type)
            ->setId($id)
            ->setType($type)
            ->setRule($this->catalogRuleRuleFactory->create())
            ->setPrefix('conditions');

        if (!empty($typeArr[1])) {
            $model->setAttribute($typeArr[1]);
        }

        if ($model instanceof \Magento\Rule\Model\Condition\AbstractCondition) {
            $model->setJsFormObject($this->getRequest()->getParam('form'));
            $model->setFormName($formName);
            $html = $model->asHtmlRecursive();
        } else {
            $html = '';
        }
        $this->getResponse()->setBody($html);
    }

}
