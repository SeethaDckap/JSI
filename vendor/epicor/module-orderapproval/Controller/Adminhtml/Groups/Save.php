<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\OrderApproval\Controller\Adminhtml\Groups;

use Epicor\Comm\Controller\Adminhtml\Context;
use Magento\Backend\Model\Auth\Session;
use Epicor\OrderApproval\Api\GroupsRepositoryInterface;
use Epicor\OrderApproval\Model\GroupsFactory;
use Epicor\OrderApproval\Model\ResourceModel\Groups;
use Epicor\OrderApproval\Model\Groups\Validate;
use Magento\Framework\Api\DataObjectHelper;
use Epicor\OrderApproval\Model\ErpManagementFactory as ErpManagementFactory;
use Epicor\OrderApproval\Model\CustomerManagementFactory as CustomerManagementFactory;
use Epicor\OrderApproval\Model\BudgetManagementFactory as BudgetManagementFactory;
use Epicor\OrderApproval\Model\RulesFactory;
use Epicor\Comm\Model\Serialize\Serializer\Json as Serializer;
use Epicor\OrderApproval\Model\HierarchyManagementFactory as HierarchyManagementFactory;
class Save extends \Epicor\OrderApproval\Controller\Adminhtml\Groups
{

    /**
     * @var Session
     */
    protected $backendSession;

    /**
     * @var Groups
     */
    protected $groupRepository;

    /**
     * @var Validate
     */
    private $validation;

    /**
     * @var DataObjectHelper
     */
    private $dataObjectHelper;

    /**
     * @var RulesFactory
     */
    private $rulesFactory;

    /**
     * @var Serializer
     * @since 100.2.0
     */
    protected $serializer;

    /**
     * Save constructor.
     *
     * @param Context                    $context
     * @param Session                    $backendSession
     * @param GroupsFactory              $groupsFactory
     * @param GroupsRepositoryInterface  $groupRepository
     * @param Validate                   $validation
     * @param DataObjectHelper           $dataObjectHelper
     * @param ErpManagementFactory       $erpManagement
     * @param CustomerManagementFactory  $CustomerManagementFactory
     * @param HierarchyManagementFactory $hierarchyManagementFactory
     * @param BudgetManagementFactory    $budgetManagementFactory,
     * @param RulesFactory               $rulesFactory
     * @param Serializer                 $serializer
     */
    public function __construct(
        Context $context,
        Session $backendSession,
        GroupsFactory $groupsFactory,
        GroupsRepositoryInterface $groupRepository,
        Validate $validation,
        DataObjectHelper $dataObjectHelper,
        ErpManagementFactory $erpManagement,
        CustomerManagementFactory $CustomerManagementFactory,
        HierarchyManagementFactory $hierarchyManagementFactory,
        BudgetManagementFactory $budgetManagementFactory,
        RulesFactory $rulesFactory,
        Serializer $serializer
    ) {
        $this->backendSession = $backendSession;
        $this->groupRepository = $groupRepository;
        $this->validation = $validation;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->rulesFactory = $rulesFactory;
        $this->serializer = $serializer;
        parent::__construct(
            $context,
            $backendSession,
            $groupsFactory,
            $groupRepository,
            $erpManagement,
            $CustomerManagementFactory,
            $hierarchyManagementFactory,
            $budgetManagementFactory,
            $rulesFactory
        );
    }

    /**
     * Groups save action
     *
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     * @throws \Exception
     */
    public function execute()
    {
        if ($this->getRequest()->getPost("group")) {
            $data = $this->getRequest()->getParams();
            $groupId = isset($data["group"]["group_id"])
                ? $data["group"]["group_id"] : null;
            $group = $this->loadEntity($groupId);
            /* @var $group \Epicor\OrderApproval\Model\Groups */

            //RULE Process
            if(isset($data["group"])) {
                $data["group"]['rules'] = $this->processRule($data);
            }

            //Set Post Data
            $this->dataObjectHelper->populateWithArray($group, $data["group"],
                Epicor\OrderApproval\Api\Data\GroupsInterface::class);

            //validate
            $valid = $this->validation->isValid($group, $data);
            $session = $this->backendSession;
            if ($valid === true) {
                //Save Group
                $this->groupRepository->save($group);
                $this->processERPAccountsSave($group, $data);
                $this->processCustomersSave($group, $data);
                $this->processHierarchySave($group, $data);
                $this->processBudgetSave($group, $data);
                $this->messageManager->addSuccessMessage(
                    __('Group Saved Successfully')
                );

                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect(
                        '*/*/edit', array('group_id' => $group->getId())
                    );
                } else {
                    $this->_redirect('*/*/');
                }
            } else {
                $this->messageManager->addErrorMessage(
                    __('The Following Error(s) occurred on Save:')
                );
                foreach ($valid as $error) {
                    $this->messageManager->addErrorMessage($error);
                }
                $session->setFormData($data);
                $this->_redirect(
                    '*/*/edit', array('group_id' => $group->getId())
                );
            }
        } else {
            $this->_redirect('*/*/');
        }
    }

    private function processRule($data)
    {
        if (isset($data['group']) && isset($data['group']['conditions'])) {
            $rule = [];
            $rule['conditions'] = $data['group']['conditions'];
            $ruleModel = $this->rulesFactory->create();
            $ruleModel->loadPost($rule);
            $ruleCondition = $ruleModel->getConditions()->asArray();

            return $this->serializer->serialize($ruleCondition);
        }

        if (isset($data['group']) && isset($data['group']['rules'])) {
            return $data['group']['rules'];
        }

        $ruleModel = $this->rulesFactory->create();
        $ruleModel->loadPost([]);
        $ruleCondition = $ruleModel->getConditions()->asArray();
        return $this->serializer->serialize($ruleCondition);
    }

}
