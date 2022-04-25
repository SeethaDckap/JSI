<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\QuickOrderPad\Block\Catalog\Product\Listing;


/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Sortby
 *
 * @author Paul.Ketelle
 */
class Sortby extends \Magento\Framework\View\Element\Template
{

    //put your code here


    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * @var \Magento\Catalog\Model\Session
     */
    protected $catalogSession;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Catalog\Model\Session $catalogSession,
        array $data = []
    ) {
        $this->request = $request;
        $this->catalogSession = $catalogSession;
        parent::__construct(
            $context,
            $data
        );
    }

    public function getCurrentOrganiseBy()
    {
        $sort_by = $this->request->getParam('organise_by', $this->catalogSession->getQopSortBy()) ?: 'uom';
        $this->catalogSession->setQopSortBy($sort_by);

        return $sort_by;
    }

    public function getSelectedOrganiseBy($type)
    {
        if($this->getCurrentOrganiseBy() === $type){
            return 'selected="selected"';
        }
    }

    public function getOrganiseByUrl($type): string
    {
        $params = $this->request->getParams();
        if(is_array($params)){
            $params['organise_by'] = $type;
        }

        return  $this->getUrl('*/*/*', [
            '_query' => $params,
            '_current' => true,
            '_escape' => true,
            '_use_rewrite' => true
        ]);
    }

    public function getSortByUrl($sort_by)
    {
        return $this->getUrl('*/*/*', array(
                '_query' => array('sort_by' => $sort_by),
                '_current' => true,
                '_escape' => true,
                '_use_rewrite' => true
                )
        );
    }

    public function getOrigPager()
    {
        if ($this->getParentBlock() instanceof \Magento\Framework\DataObject) {
            $pagerBlock = $this->getParentBlock()->getChildBlock('product_list_toolbar_pager-orig');
            if ($pagerBlock instanceof \Magento\Framework\DataObject) {

                /* @var $pagerBlock Mage_Page_Block_Html_Pager */
                $pagerBlock->setAvailableLimit($this->getAvailableLimit());

                $pagerBlock->setUseContainer($this->getUseContainer())
                    ->setShowPerPage($this->getShowPerPage())
                    ->setShowAmounts($this->getShowAmounts())
                    ->setLimit($this->getLimit())
                    ->setFrameLength($this->getFrameLength())
                    ->setJump($this->getJump())
                    ->setCollection($this->getCollection());

                return $pagerBlock->toHtml();
            }
        }
    }

}
