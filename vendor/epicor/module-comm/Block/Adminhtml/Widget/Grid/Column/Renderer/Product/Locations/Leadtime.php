<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Block\Adminhtml\Widget\Grid\Column\Renderer\Product\Locations;


/**
 * Locations Manufacturers renderer
 *
 * @category   Epicor
 * @package    Epicor_Comm
 * @author Epicor Websales Team
 */
class Leadtime extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    public function __construct(
        \Magento\Backend\Block\Context $context,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $data
        );
    }


    /**
     * Render country grid column
     *
     * @param   \Epicor\Comm\Model\Location\Product $row
     * 
     * @return  string
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        $html = '';

        $days = $row->getLeadTimeDays();
        $text = $row->getLeadTimeText();

        if (!is_null($days)) {
            $html .= '<strong>' . __('Days') . ':</strong> <span class="col-lead_time_days">' . $days . '</span><br />';
        }

        if (!is_null($text)) {
            $html .= '<strong>' . __('Text') . ':</strong> <span class="col-lead_time_text">' . $text . '</span>';
        }

        return $html;
    }

}
