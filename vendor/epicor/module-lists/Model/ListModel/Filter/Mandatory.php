<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Lists\Model\ListModel\Filter;


/**
 * Model Class for List Filtering
 *
 * @category   Epicor
 * @package    Epicor_Lists
 * @author     Epicor Websales Team
 */
class Mandatory extends \Epicor\Lists\Model\ListModel\Filter\AbstractModel
{

    protected $allowedUrls = array('epicor_lists');
    protected $allowedActions = array('edit', 'products', 'productsgrid');
    protected $allowedController = array('lists');

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * @var \Epicor\Lists\Model\ListModelFactory
     */
    protected $listsListModelFactory;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resourceConnection;

    public function __construct(
        \Epicor\Common\Helper\Context $context,
        \Epicor\Lists\Model\ListModelFactory $listsListModelFactory,
        \Magento\Framework\App\ResourceConnection $resourceConnection
    ) {
        $this->request = $context->getRequest();
        $this->listsListModelFactory = $listsListModelFactory;
        $this->resourceConnection = $resourceConnection;
        $this->design = $context->getDesign();
    }
    /**
     * Adds Mandatory filter to the Collection
     *
     * @param \Epicor\Lists\Model\ResourceModel\ListModel\Collection $collection
     *
     * @return \Epicor_Lists_Model_Resource_ListModel_Collection
     */
    public function filter($collection)
    {
        $getListId = $this->checkPage();
        if ($getListId) {
            $conditions = 'main_table.id !=' . $getListId;
            $collection->getSelect()->where($conditions);

            return $collection;
        }
    }

    public function checkPage()
    {
        $checkFrontend = $this->design->getArea();
        $urls = $this->request->getRouteName();
        $controller = $this->request->getControllerName();
        $actionName = $this->request->getActionName();
        if (($checkFrontend == "frontend") && (in_array($urls, $this->allowedUrls)) && (in_array($controller, $this->allowedController)) && in_array($actionName, $this->allowedActions)) {
            return $this->getList();
        } else {
            return false;
        }
    }

    public function getList()
    {
        /* @var $list Epicor_Lists_Model_ListModel */
        $getListId = $this->request->getParam('list_id');
        if ($getListId) {
            $collections = $this->listsListModelFactory->create();
            $code4Sql = $this->resourceConnection->getConnection('default_write')->quote($getListId);
            if (!$code4Sql) {
                return false;
            }
            $resource = $this->resourceConnection;
            $readConnection = $resource->getConnection('core_read');
            $query = "SELECT settings FROM  ecc_list WHERE id=" . $code4Sql;
            $results = $readConnection->fetchOne($query);
            $settings = $results;
            $Mandatory = empty($settings) ? array() : str_split($settings);
            if (in_array('M', $Mandatory)) {
                return $code4Sql;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

}
