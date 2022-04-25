<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\SalesRep\Controller\Promo\Widget;

class CategoriesJson extends \Epicor\SalesRep\Controller\Promo\Widget
{



/**
     * Get tree node (Ajax version)
     */
    public function execute()
    {
        if ($categoryId = (int) $this->getRequest()->getPost('id')) {
            $this->getRequest()->setParam('id', $categoryId);

            if (!$category = $this->_initCategory()) {
                return;
            }
            $block = $this->_view->getLayout()->createBlock(
                'Magento\Catalog\Block\Adminhtml\Category\Checkboxes\Tree'
            )->setCategoryIds(
                [$categoryId]
            );
            $this->getResponse()->representJson(
                $block->getTreeJson($category)
            );
            /*$this->getResponse()->setBody(
                $this->getLayout()->createBlock('adminhtml/catalog_category_tree')
                    ->getTreeJson($category)
            ); */
        }
    }

    }
