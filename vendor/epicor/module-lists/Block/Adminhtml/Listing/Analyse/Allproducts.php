<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Lists\Block\Adminhtml\Listing\Analyse;


class Allproducts extends \Magento\Backend\Block\Widget\Grid\Container
{

    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        array $data = []
    )
    {
        $this->_controller = 'adminhtml_listing_analyse_allproducts';
        $this->_blockGroup = 'Epicor_Lists';
        $this->_headerText = __('Products');


        parent::__construct(
            $context,
            $data
        );

        $this->buttonList->add(
            'cancel',
            [
                'label' => __('Cancel'),
                'onclick' => 'listsAnalyse.closepopup()',
            ],
            1
        );

        $this->removeButton('add');
    }

}
