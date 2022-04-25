<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Lists\Controller\Adminhtml\Epicorlists\Analyse;

class Analyse extends \Epicor\Lists\Controller\Adminhtml\Epicorlists\Analyse
{

    /**
     * @var \Epicor\Lists\Model\ResourceModel\ListModel\CollectionFactory
     */
    protected $listsResourceListModelCollectionFactory;

    /**
     * @var \Epicor\Lists\Model\ListModel\Filter\ErpaccountFactory
     */
    protected $listsListModelFilterErpaccountFactory;

    /**
     * @var \Epicor\Lists\Model\ListModel\Filter\WebsiteFactory
     */
    protected $listsListModelFilterWebsiteFactory;

    /**
     * @var \Epicor\Lists\Model\ListModel\Filter\StoreFactory
     */
    protected $listsListModelFilterStoreFactory;

    /**
     * @var \Epicor\Lists\Model\ListModel\Filter\CustomerFactory
     */
    protected $listsListModelFilterCustomerFactory;

    /**
     * @var \Epicor\Lists\Model\ListModel\Filter\ErpaccounttypeFactory
     */
    protected $listsListModelFilterErpaccounttypeFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Framework\DataObjectFactory
     */
    protected $dataObjectFactory;

    /**
     * @var \Magento\Framework\App\ViewInterface
     */
    protected $_view;

    public function __construct(
        \Epicor\Lists\Controller\Adminhtml\Context $context,
        \Magento\Backend\Model\Auth\Session $backendAuthSession,
        \Epicor\Lists\Model\ResourceModel\ListModel\CollectionFactory $listsResourceListModelCollectionFactory,
        \Epicor\Lists\Model\ListModel\Filter\ErpaccountFactory $listsListModelFilterErpaccountFactory,
        \Epicor\Lists\Model\ListModel\Filter\WebsiteFactory $listsListModelFilterWebsiteFactory,
        \Epicor\Lists\Model\ListModel\Filter\StoreFactory $listsListModelFilterStoreFactory,
        \Epicor\Lists\Model\ListModel\Filter\CustomerFactory $listsListModelFilterCustomerFactory,
        \Epicor\Lists\Model\ListModel\Filter\ErpaccounttypeFactory $listsListModelFilterErpaccounttypeFactory,
        \Magento\Framework\DataObjectFactory $dataObjectFactory
    ) {
        $this->dataObjectFactory = $dataObjectFactory;
        $this->listsResourceListModelCollectionFactory = $listsResourceListModelCollectionFactory;
        $this->listsListModelFilterErpaccountFactory = $listsListModelFilterErpaccountFactory;
        $this->listsListModelFilterWebsiteFactory = $listsListModelFilterWebsiteFactory;
        $this->listsListModelFilterStoreFactory = $listsListModelFilterStoreFactory;
        $this->listsListModelFilterCustomerFactory = $listsListModelFilterCustomerFactory;
        $this->listsListModelFilterErpaccounttypeFactory = $listsListModelFilterErpaccounttypeFactory;
        $this->registry = $context->getRegistry();
        $this->_view = $context->getView();
        parent::__construct($context, $backendAuthSession);
    }
    public function execute()
    {
        if ($data = $this->getRequest()->getPost()) {

            $data = $this->dataObjectFactory->create($data->toArray());

            $collection = $this->listsResourceListModelCollectionFactory->create();
            /* @var $collection Epicor_Lists_Model_Resource_List_Collection */

            $collection->filterActive();

            if ($data->getEccErpaccountId()) {
                $filter = $this->listsListModelFilterErpaccountFactory->create();
                $filter->setErpAccountId($data->getEccErpaccountId());
                $filter->filter($collection);
            }

            if ($data->getStoreId()) {
                $mixed = explode('_', $data->getStoreId());
                if ($mixed[0] == 'website') {
                    $filter = $this->listsListModelFilterWebsiteFactory->create();
                    $filter->setWebsiteId($mixed[1]);
                    $filter->filter($collection);
                } elseif ($mixed[0] == 'store') {
                    $filter = $this->listsListModelFilterStoreFactory->create();
                    $filter->setStoreGroupId($mixed[1]);
                    $filter->filter($collection);
                }
            }

            if ($data->getCustomerId()) {
                $filter = $this->listsListModelFilterCustomerFactory->create();
                $filter->setCustomerId($data->getCustomerId());
                $filter->filter($collection);
            }

            if ($data->getCustomerType() && in_array($data->getCustomerType(), array('B', 'C'))) {
                $filter = $this->listsListModelFilterErpaccounttypeFactory->create();
                $filter->setTypeFilter($data->getCustomerType());
                $filter->filter($collection);
            }

            $collection->addOrder('priority', 'DESC')->addOrder('created_date', 'DESC');

            $collection->groupById();

            $listIds = array();
            foreach ($collection->getItems() as $item) {
                if (!isset($listIds[$item->getPriority()])) {
                    $listIds[$item->getPriority()] = $item->getId();
                }
            }

            $this->registry->register('epicor_lists_analyse_ids', $listIds);

            $this->_view->loadLayout();
            $this->_view->renderLayout();
        } else {
            $this->_redirect('*/*/index');
        }
    }

    }
