<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\QuickOrderPad\Block\Catalogsearch;


/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class Result extends \Magento\CatalogSearch\Block\Result
{

    /**
     * @var \Epicor\Lists\Helper\Frontend\Quickorderpad
     */
    protected $listsQopHelper;

    /**
     * @var \Magento\CatalogSearch\Helper\Data
     */
    protected $catalogSearchHelper;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Catalog\Model\Layer\Resolver $layerResolver,
        \Magento\CatalogSearch\Helper\Data $catalogSearchData,
        \Magento\Search\Model\QueryFactory $queryFactory,
        \Epicor\Lists\Helper\Frontend\Quickorderpad $listsQopHelper,
        \Magento\CatalogSearch\Helper\Data $catalogSearchHelper,
        array $data = []
    ) {
        $this->listsQopHelper = $listsQopHelper;
        $this->catalogSearchHelper = $catalogSearchHelper;
        parent::__construct(
            $context,
            $layerResolver,
            $catalogSearchData,
            $queryFactory,
            $data
        );
    }

    /**
     * @return \Magento\CatalogSearch\Helper\Data
     */
    public function getCatalogSearchHelper()
    {
        return $this->catalogSearchHelper;
    }


    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        // add Home breadcrumb

        $breadcrumbs = $this->getLayout()->getBlock('breadcrumbs');

        $csv = $this->getRequest()->getParam('csv');

        $actionName = $this->getRequest()->getActionName();
        'addToCartFromWishlist';

        $query = $this->catalogSearchHelper->getEscapedQueryText();

        $title = '';
        if ($actionName == 'addToCartFromWishlist') {
            $title = __('Add to cart from Wishlist');
        } elseif ($actionName == 'addToCartFromMyOrdersWidget') {
            $title = __('Add to cart from My Orders Widget');
        } elseif ($csv == 1) {
            $title = __("CSV upload");
        } elseif (!empty($query)) {
            //M1 > M2 Translation Begin (Rule 55)
            //$title = __("Search results for: '%s'", $query);
            $title = __("Search results for: '%1'", $query);
            //M1 > M2 Translation End
            if ($list = $this->listsQopHelper->getSessionList()) {
                $title .= __(' in List: "%1"', $list->getTitle());
            }
        } elseif ($list = $this->listsQopHelper->getSessionList()) {
            $title = $list->getTitle();
        }
        
        $this->pageConfig->getTitle()->set($title);

        if ($breadcrumbs) {
            $breadcrumbs->addCrumb('home', array(
                'label' => __('Home'),
                'title' => __('Go to Home Page'),
                'link' => $this->getBaseUrl()
            ))->addCrumb('search', array(
                'label' => $title,
                'title' => $title
            ));
        }

        // modify page title
        //$this->getLayout()->getBlock('head')->setTitle($title);
        $pageMainTitle = $this->getLayout()->getBlock('page.main.title');
        if ($pageMainTitle) {
            $pageMainTitle->setPageTitle($title);
        }
        
        return $this;
    }

    public function getNoResultText()
    {
        $csv = $this->getRequest()->getParam('csv');

        if ($csv == 1 || ($this->listsQopHelper->listsEnabled() && !$this->catalogSearchHelper->getEscapedQueryText())) {
            return '';
        } else {
            return parent::getNoResultText();
        }
    }

    public function getHeaderText()
    {
        $csv = $this->getRequest()->getParam('csv');

        $queryText = $this->catalogSearchHelper->getEscapedQueryText();

        if ($csv == 1) {
            return __("Products that require configuration");
        } elseif (!empty($queryText)) {
            $title = __("Search results for: '%1'", $queryText);
            if ($list = $this->listsQopHelper->getSessionList()) {
                $title .= __(' in List: %1', $list->getTitle());
            }
            return $title;
        } elseif ($list = $this->listsQopHelper->getSessionList()) {
            return $list->getTitle();
        } else {
            return parent::getHeaderText();
        }
    }

}
