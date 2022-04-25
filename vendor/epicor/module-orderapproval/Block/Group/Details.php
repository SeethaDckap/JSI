<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\OrderApproval\Block\Group;

use Epicor\Lists\Block\Customer\Account\Listing\Details as ListingDetails;
use Epicor\OrderApproval\Model\GroupSave\Customers as GroupCustomers;
use Epicor\OrderApproval\Model\Groups as ApprovalGroups;

class Details extends ListingDetails
{
    /**
     * @var \Epicor\OrderApproval\Model\GroupsRepository
     */
    private $groupsRepository;

    /**
     * @var GroupCustomers
     */
    private $groupCustomers;
    
    /**
     * @var ApprovalGroups
     */
    private $groups;

    /**
     * Details constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Epicor\Lists\Model\ListModelFactory $listsListModelFactory
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\Data\Form\FormKey $formKey
     * @param \Magento\Framework\Locale\ResolverInterface $localeResolver
     * @param \Epicor\Lists\Model\ListModel\Type $listType
     * @param \Epicor\Lists\Model\ListModel\Type\AbstractModel $listsListModelTypeAbstractModel
     * @param \Epicor\OrderApproval\Model\GroupsRepository $groupsRepository
     * @param GroupCustomers $groupCustomers
     * @param ApprovalGroups $groups
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Epicor\Lists\Model\ListModelFactory $listsListModelFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Data\Form\FormKey $formKey,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \Epicor\Lists\Model\ListModel\Type $listType,
        \Epicor\Lists\Model\ListModel\Type\AbstractModel $listsListModelTypeAbstractModel,
        \Epicor\OrderApproval\Model\GroupsRepository $groupsRepository,
        GroupCustomers $groupCustomers,
        ApprovalGroups $groups,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $listsListModelFactory,
            $customerSession,
            $formKey,
            $localeResolver,
            $listType,
            $listsListModelTypeAbstractModel,
            $data
        );

        $this->groupsRepository = $groupsRepository;
        $this->groupCustomers = $groupCustomers;
        $this->groups = $groups;
    }

    /**
     * @return false|string
     */
    public function getTabUrlData()
    {
        return json_encode([
            'customer' => $this->getCustomerTabUrl(),
            'rules' => $this->getRulesTabUrl(),
            'budgets' => $this->getBudgetsTabUrl(),
            'hierarchy' => $this->getHierarchyTabUrl(),
        ]);
    }

    /**
     * @param $type
     * @param $group
     * @return string
     */
    public function getChecked($type, $group)
    {
        switch ($type) {
            case 'multi-level':
                $checked = (boolean) $group->getIsMultiLevel();
                return $this->getCheckedAttribute($checked);
            case 'active-group':
                $checked = (boolean) $group->getIsActive();
                return $this->getCheckedAttribute($checked);
            default:
                return '';
        }
    }

    /**
     * @return string
     */
    private function getCustomerTabUrl()
    {
        $param = $this->_request->getParam('id') ? ['id' => $this->_request->getParam('id')] : [];
        return $this->getUrl('epicor_orderapproval/manage/customers', $param);
    }

    /**
     * @return string
     */
    private function getRulesTabUrl()
    {
        return $this->getUrl('epicor_orderapproval/manage/rules', $this->getGroupIdParam());
    }

    /**
     * @return string
     */
    private function getBudgetsTabUrl()
    {
        return $this->getUrl('epicor_orderapproval/manage/budgets', $this->getGroupIdParam());
    }

    /**
     * @param $checked
     * @return string
     */
    private function getCheckedAttribute($checked)
    {
        return $checked ? 'checked="checked"' : '';
    }

    /**
     * @return string
     */
    public function getSubmitButtonHtml()
    {
        $buttonHtml = '';
        if ($this->isEditableByCustomer()) {
            $buttonHtml = $this->renderSubmitButton();
        }

        return $buttonHtml;
    }

    /**
     * @return string
     */
    private function renderSubmitButton()
    {
        $buttonText = 'Update Group';
        if (!$this->getGroupId()) {
            $buttonText = 'Submit Group';
        }

        return '<button type="button" id="submit-group" value="Submit">' . $buttonText . '</button>';
    }

    /**
     * @return mixed
     */
    public function getGroupId()
    {
        if ($id = $this->getRequest()->getParam('id')) {
            return $id;
        }
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getDisableBudget()
    {
        if ($this->getGroupId() && $this->groupCustomers->isEditableByCustomer()) {
            return '';
        } else {
            return 'disabled="disabled"';
        }
    }

    /**
     * @return string
     */
    public function getDisabled()
    {
        if (!$this->isEditableByCustomer()) {
            return 'disabled="disabled"';
        }
    }

    /**
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function isEditableByCustomer()
    {
        return $this->groupCustomers->isEditableByCustomer();
    }

    /**
     * @return \Epicor\OrderApproval\Api\Data\GroupsInterface|ApprovalGroups
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getGroup()
    {
        if ($groupId = $this->getGroupId()) {
            return $this->groupsRepository->getById($groupId);
        }
        return $this->groups;
    }

    /**
     * @return string
     */
    private function getHierarchyTabUrl()
    {
        $param = $this->_request->getParam('id') ? ['id' => $this->_request->getParam('id')] : [];
        return $this->getUrl('epicor_orderapproval/manage/hierarchy', $param);
    }

    /**
     * @return array
     */
    private function getGroupIdParam()
    {
        return $this->_request->getParam('id') ? ['id' => $this->_request->getParam('id')] : [];
    }
}
