<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Lists\Block\Customer\Account\Listing\Renderer;


/**
 * Is grouped part link grid renderer
 *
 * @category   Epicor
 * @package    Epicor_Lists
 * @author     Epicor Websales Team
 */
class IsGrouped extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{

    /**
     * Store manager
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magento\Store\Model\StoreManagerInterface $_storeManager,
        array $data = []
    )
    {
        $this->_storeManager = $_storeManager;
        parent::__construct(
            $context,
            $data
        );
    }

    public function render(\Magento\Framework\DataObject $row)
    {
        $html = '';
        if ($row->getTypeId() == 'grouped' || $row->getTypeId() == 'configurable') {
            $title = __('This product has multiple products associated with it. Enabling / Disabling it will have the same effect on its associated products');
            $imgurl = $this->getViewFileUrl("Epicor_Lists::epicor/lists/images/group_icon.png");
            $html = "<img width='20px' height='20px' title='$title' src='$imgurl'>";
        }
        return $html;
    }

}
