<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Helper;


class Masquerade extends \Magento\Framework\App\Helper\AbstractHelper
{

    protected $_allErpAccountChildren = array();
    protected $_erpChildren = array();

    /**
     * @var \Epicor\Comm\Model\ResourceModel\Customer\Erpaccount\CollectionFactory
     */
    protected $commResourceCustomerErpaccountCollectionFactory;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resourceConnection;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Epicor\Comm\Model\Message\Request\AstFactory
     */
    protected $commMessageRequestAstFactory;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Epicor\Comm\Model\ResourceModel\Customer\Erpaccount\CollectionFactory $commResourceCustomerErpaccountCollectionFactory,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Magento\Customer\Model\Session $customerSession,
        \Epicor\Comm\Model\Message\Request\AstFactory $commMessageRequestAstFactory
    ) {
        $this->commResourceCustomerErpaccountCollectionFactory = $commResourceCustomerErpaccountCollectionFactory;
        $this->resourceConnection = $resourceConnection;
        $this->customerSession = $customerSession;
        $this->commMessageRequestAstFactory = $commMessageRequestAstFactory;
        parent::__construct($context);
    }
    public function getAllErpAccountChildren($erp_account_id, $type = null)
    {

        if (empty($this->_erpChildren)) {
            $this->getChildrenByParentErpAccountId($erp_account_id, $type);

            $erp_accounts = $this->commResourceCustomerErpaccountCollectionFactory->create()
                ->addFieldToFilter('entity_id', array('in' => array_keys($this->_allErpAccountChildren)));

            foreach ($erp_accounts->getItems() as $erp_account) {
                $data = array(
                    'type' => $this->_allErpAccountChildren[$erp_account->getId()]->getType(),
                    'id' => $erp_account->getId(),
                );
                $erp_account->setChildType($data['type']);
                $erp_account->setChildTypeData(base64_encode(serialize($data)));
                $this->_erpChildren[] = $erp_account;
            }
        }

        return $this->_erpChildren;
    }

    private function getChildrenByParentErpAccountId($parent_ids, $types = null)
    {

        if (!is_array($parent_ids)) {
            $parent_ids = array($parent_ids);
        }

        $children = $this->commResourceCustomerErpaccountCollectionFactory->create();
        /* @var $children Epicor_Comm_Model_Erp_Customer_Erpaccount */
        $children->getSelect()
            ->join(array('hierarchy' => $this->resourceConnection->getTableName('epicor_comm/erp_customer_group_hierarchy')), 'main_table.entity_id = hierarchy.parent_id')
            ->group('main_table.entity_id');
        $children->addFieldToFilter('hierarchy.parent_id', array('in' => $parent_ids));

        if (!empty($types)) {
            if (!is_array($types)) {
                $types = array($types);
            }
            $children->addFieldToFilter('hierarchy.type', array('in' => $types));
        }

        $search_parents = array();
        foreach ($children->getItems() as $child) {
            $child_id = $child->getChildId();
            if (!isset($this->_allErpAccountChildren[$child_id])) {
                $this->_allErpAccountChildren[$child_id] = $child;
                if ($child->isMasqueradeAllowed()) {
                    $search_parents[] = $child_id;
                }
            }
        }

        if (!empty($search_parents)) {
            $this->getChildrenByParentErpAccountId($search_parents, $types);
        }
    }

    public static function startMasquerade($erpAccountId)
    {
        $customerSession = $this->customerSession;
        /* @var $customerSession Mage_Customer_Model_Session */
        $customerSession->setMasqueradeAccountId($erpAccountId);

        $ast = $this->commMessageRequestAstFactory->create();
        /* @var $ast Epicor_Comm_Model_Message_Request_Ast */
        $ast->sendMessage();
    }

    public static function stopMasquerade()
    {
        $customerSession = $this->customerSession;
        /* @var $customerSession Mage_Customer_Model_Session */
        $customerSession->setMasqueradeAccountId(null);

        $ast = $this->commMessageRequestAstFactory->create();
        /* @var $ast Epicor_Comm_Model_Message_Request_Ast */
        $ast->sendMessage();
    }

    public static function isMasquerading()
    {
        $customerSession = $this->customerSession;
        /* @var $customerSession Mage_Customer_Model_Session */
        $masquerade = $customerSession->getMasqueradeAccountId();

        return !empty($masquerade);
    }

}
