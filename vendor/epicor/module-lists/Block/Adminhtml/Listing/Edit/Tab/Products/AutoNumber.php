<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Lists\Block\Adminhtml\Listing\Edit\Tab\Products;


class AutoNumber extends \Magento\Backend\Block\Template
{
    /**
     * @var \Epicor\Lists\Block\Adminhtml\Listing\Edit\Tab\Products
     */
    private $productsList;

    /**
     * AutoNumber constructor.
     * @param \Epicor\Lists\Block\Adminhtml\Listing\Edit\Tab\Products $productsList
     * @param \Magento\Backend\Block\Template\Context $context
     * @param array $data
     */
    public function __construct(
        \Epicor\Lists\Block\Adminhtml\Listing\Edit\Tab\Products $productsList,
        \Magento\Backend\Block\Template\Context $context,
        array $data = []
    ){
        parent::__construct($context, $data);
        $this->productsList = $productsList;
    }

    /**
     * @return int
     */
    public function getSelectedItems()
    {
        $list = $this->productsList->getList();
        $productCount = 0;
        foreach ($list->getProducts() as $product) {
            if($product->getSku()){
                $productCount++;
            }
        }

        return $productCount;
    }

    /**
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getAutoNumberButton()
    {
        return $this->getLayout()->createBlock(\Magento\Backend\Block\Widget\Button::class)->setData(
            [
                'label' => __('Auto Number'),
                'onclick' => '',
                'class' => 'task action-secondary auto-position-button',
            ]
        )->setDataAttribute(
            [
                'action' => 'auto-position'
            ]
        )->toHtml();
    }
}