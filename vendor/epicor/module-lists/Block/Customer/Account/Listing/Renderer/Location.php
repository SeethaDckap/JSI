<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Lists\Block\Customer\Account\Listing\Renderer;


/**
 * Invoice Reorder link grid renderer
 *
 * @category   Epicor
 * @package    Epicor_Lists
 * @author     Epicor Websales Team
 */
class Location extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    /**
     * @var \Epicor\Comm\Helper\Locations
     */
    protected $locHelper;

    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magento\Framework\Registry $registry,
        \Epicor\Comm\Helper\Locations $locHelper,
        array $data = []
    ) {
        $this->locHelper = $locHelper;
        parent::__construct(
            $context,
            $data
        );
    }
    public function render(\Magento\Framework\DataObject $row)
    {
        $index = $this->getColumn()->getIndex();
        $locCode = $row->getData($index);
        if (!empty($locCode) && $locCode != '&nbsp;') {
            $locName = $this->locHelper->getLocationName($locCode);
            return $locName;
        }else{
            return $locCode;
        }
    }

}
