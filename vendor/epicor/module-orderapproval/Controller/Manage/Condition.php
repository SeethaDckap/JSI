<?php
namespace Epicor\OrderApproval\Controller\Manage;

use Epicor\OrderApproval\Model\Rules\FrontEnd\ConditionFactory;
use Epicor\OrderApproval\Model\Rules\FrontEnd\Condition as RuleCondition;
use Magento\Rule\Model\Condition\AbstractCondition;
use Epicor\OrderApproval\Model\Rules\OrderValue as RuleOrderValue;

class Condition extends \Epicor\AccessRight\Controller\Action
{
    /**
     * @var \Epicor\OrderApproval\Model\Rules\FrontEnd\ConditionFactory
     */
    private $conditionFactory;

    /**
     * @var string[]
     */
    private $allowedRules = [
        'Epicor\OrderApproval\Model\Rules\OrderValue'
    ];

    /**
     * @var RuleOrderValue
     */
    private $ruleOrderValue;

    /**
     * Condition constructor.
     * @param \Magento\Framework\App\Action\Context $context
     * @param ConditionFactory $conditionFactory
     * @param RuleOrderValue $ruleOrderValue
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        ConditionFactory $conditionFactory,
        RuleOrderValue $ruleOrderValue
    ) {
        parent::__construct($context);
        $this->conditionFactory = $conditionFactory;
        $this->ruleOrderValue = $ruleOrderValue;
    }

    /**
     * New condition html action
     *
     * @return void
     */
    public function execute()
    {
        $viewType = $this->getRequest()->getParam('view_type');
        $conditionModel = null;
        $html = '';
        if ($viewType == "approval-limit") {
            $html = $this->getConditionModelHtml();
        }


        $this->getResponse()->setBody($html);
    }

    /**
     * @return string
     */
    private function getConditionModelHtml()
    {
        $conditionModel = $this->conditionFactory->create();

        $id = $this->getRequest()->getParam('condition_id');
        $typeArr = explode('|', str_replace('-', '/', $this->getRequest()->getParam('type')));
        $ruleType = $typeArr[0];

        if (!$this->isAllowedRule($ruleType)) {
            $this->messageManager->addErrorMessage('Rule type not supported');
            return false;
        }
        $this->ruleOrderValue
            ->setId($id)
            ->setType($ruleType)
            ->setRule($conditionModel)
            ->setPrefix('conditions');
        if (!empty($typeArr[1])) {
            $this->ruleOrderValue->setAttribute($typeArr[1]);
        }

        $this->setOperatorType();
        $formObject = $this->getRequest()->getParam('form');
        if ($this->ruleOrderValue instanceof RuleOrderValue && $formObject) {
            $this->ruleOrderValue->setJsFormObject($formObject);
            return $this->ruleOrderValue->asHtmlRecursive();
        } else {
            return '';
        }
    }

    /**
     * @param $ruleType
     * @return bool
     */
    private function isAllowedRule($ruleType)
    {
        return in_array($ruleType, $this->allowedRules);
    }

    /**
     * Sets the specific options to be used (equal or less then, less then)
     * @return void
     */
    private function setOperatorType()
    {
        $this->ruleOrderValue->setData(
            'operator_by_input_type',
            [RuleOrderValue::APPROVAL_LIMIT_OPERATOR_TYPE => RuleOrderValue::APPROVAL_LIMIT_OPERATORS]
        );
    }
}
