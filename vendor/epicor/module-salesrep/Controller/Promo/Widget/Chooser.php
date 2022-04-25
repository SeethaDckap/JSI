<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\SalesRep\Controller\Promo\Widget;

class Chooser extends \Epicor\SalesRep\Controller\Promo\Widget
{
    
    /**
     * Prepare block for chooser
     *
     * @return void
     */
    public function execute()
    {
        $request = $this->getRequest();

        switch ($request->getParam('attribute')) {
            case 'sku':
                 
                 $block = $this->_view->getLayout()->createBlock(
                    'Magento\CatalogRule\Block\Adminhtml\Promo\Widget\Chooser\Sku',
                    'promo_widget_chooser_sku',
                    ['data' => ['js_form_object' => $request->getParam('form')]]
                );
                
                /*$block = $this->getLayout()->createBlock(
                    'adminhtml/promo_widget_chooser_sku', 'promo_widget_chooser_sku', array('js_form_object' => $request->getParam('form'),
                )); */
                break;

            case 'category_ids':
                $ids = $request->getParam('selected', array());
                if (is_array($ids)) {
                    foreach ($ids as $key => &$id) {
                        $id = (int) $id;
                        if ($id <= 0) {
                            unset($ids[$key]);
                        }
                    }

                    $ids = array_unique($ids);
                } else {
                    $ids = array();
                }

                $block = $this->_view->getLayout()->createBlock(
                    'Magento\Catalog\Block\Adminhtml\Category\Checkboxes\Tree',
                    'promo_widget_chooser_category_ids',
                    ['data' => ['js_form_object' => $request->getParam('form')]]
                ) ->setTemplate('Epicor_SalesRep::epicor/salesrep/catalog/category/checkboxes/tree.phtml')
                  ->setCategoryIds(
                    $ids
                );
                  
                /*
                $block = $this->getLayout()->createBlock(
                        'adminhtml/catalog_category_checkboxes_tree', 'promo_widget_chooser_category_ids', array('js_form_object' => $request->getParam('form'))
                    )
                    ->setTemplate('epicor/salesrep/catalog/category/checkboxes/tree.phtml')
                    ->setCategoryIds($ids);
                 */
                break;

            default:
                $block = false;
                break;
        }

        if ($block) {
            $this->getResponse()->setBody($block->toHtml());
        }
    }

    }
