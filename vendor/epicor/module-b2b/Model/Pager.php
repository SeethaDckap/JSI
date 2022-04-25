<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\B2b\Model;


class Pager extends \Magento\Framework\Data\Collection
{

    private $backendPageCount;
    private $useBackendPaging = false;
    private $isLoaded = false;

    /*     * *
     * load only current page items
     */

    /**
     * @var \Magento\Framework\DataObjectFactory
     */
    protected $dataObjectFactory;

    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Magento\Framework\DataObjectFactory $dataObjectFactory
    ) {
        $this->dataObjectFactory = $dataObjectFactory;
        parent::__construct(
            $entityFactory
        );
    }


    public function load($printQuery = false, $logQuery = false)
    {

        if (!$this->isLoaded) {
            $this->isLoaded = true;
            $this->loadForPager();
        }
        return $this;
    }

    public function setPageCount($pageCount)
    {
        $this->useBackendPaging = true;
        $this->backendPageCount = $pageCount;

        for ($i = 1; $i < $this->getCurPage(); $i++) {
            for ($row = 0; $row < $this->getPageSize(); $row++) {
                $this->addItem($this->dataObjectFactory->create());
            }
        }
    }

    // public function 

    public function getLastPageNumber()
    {

        if ($this->useBackendPaging) {
            $result = $this->backendPageCount;
        } else {
            $result = parent::getLastPageNumber();
        }
        return $result;
    }

    public function loadForPager()
    {

        if (!$this->getPageSize()) {
            return $this;
        }

        $items = array();
        $currentPage = $this->getCurPage();
        $i = 0;
        foreach ($this->_items as $item) {
            if ($i < ($currentPage - 1) * $this->getPageSize()) {
                $i++;
            } elseif ($i >= $currentPage * $this->getPageSize()) {
                break;
            } else {
                $items[] = $item;
                $i++;
            }
        }

        $this->_items = $items;
        return $this;
    }

}
