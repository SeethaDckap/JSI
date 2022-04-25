<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Search\Block\Advanced\ProductList;

use Magento\Catalog\Helper\Product\ProductList;
use Magento\Catalog\Model\Product\ProductList\Toolbar as ToolbarModel;

class Toolbar extends \Magento\Catalog\Block\Product\ProductList\Toolbar {

    /**
     * @var \Magento\Framework\App\ProductMetadataInterface
     */
    private $_productMetaData;
    
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Catalog\Model\Session $catalogSession,
        \Magento\Catalog\Model\Config $catalogConfig,
        ToolbarModel $toolbarModel,
        \Magento\Framework\Url\EncoderInterface $urlEncoder,
        ProductList $productListHelper,
        \Magento\Framework\Data\Helper\PostHelper $postDataHelper,
        \Magento\Framework\App\ProductMetadataInterface $productMetaData,
        array $data = []
    )  {
            $this->_productMetaData = $productMetaData;
            parent::__construct($context, $catalogSession, $catalogConfig, $toolbarModel, $urlEncoder, $productListHelper, $postDataHelper, $data);
        }
    /**
     * Render pagination HTML
     *
     * @return string
     */
    public function getPagerHtml() {
        $pagerBlock = $this->getChildBlock('product_list_toolbar_pager');

        if ($pagerBlock instanceof \Magento\Framework\DataObject) {
            /* @var $pagerBlock \Magento\Theme\Block\Html\Pager */
            $pagerBlock->setAvailableLimit($this->getAvailableLimit());

            $pagerBlock->setUseContainer(
                    false
            )->setShowPerPage(
                    false
            )->setShowAmounts(
                    false
            )->setFrameLength(
                    $this->_scopeConfig->getValue(
                            'design/pagination/pagination_frame', \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                    )
            )->setJump(
                    $this->_scopeConfig->getValue(
                            'design/pagination/pagination_frame_skip', \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                    )
            )->setLimit(
                    $this->getLimit()
            )->setCollection(
                    $this->getCollection()
            );
            //Added to trigger MSQ for advanced search collection.
            $version = $this->_productMetaData->getVersion();
                if ($version >= '2.2.0') {
                    $this->_eventManager->dispatch(
                        'ecc_block_product_list_collection', ['collection' => $this->getCollection()]
                    );
                }

            return $pagerBlock->toHtml();
        }

        return '';
    }

}
