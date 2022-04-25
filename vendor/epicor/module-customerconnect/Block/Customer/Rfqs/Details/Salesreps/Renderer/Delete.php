<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Customerconnect\Block\Customer\Rfqs\Details\Salesreps\Renderer;


/**
 * RFQ salesrep delete tickbox renderer
 *
 * @category   Epicor
 * @package    Epicor_Customerconnect
 * @author     Epicor Websales Team
 */
class Delete extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Input
{

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
    )
    {
        $this->registry = $registry;
        parent::__construct(
            $context,
            $data
        );
    }

    public function render(\Magento\Framework\DataObject $row)
    {
        $key = $this->registry->registry('rfq_new') ? 'new' : 'existing';
        if ($this->registry->registry('rfqs_editable')) {
            $html = '<input type="checkbox" class="salesreps_delete" name="salesreps[' . $key . '][' . $row->getUniqueId() . '][delete]" />';
        } else {
            $html = '';
        }


        $oldDetails = array(
            'name' => $row->getName(),
            'number' => $row->getNumber(),
        );

        $html .= '<input type="hidden" name="salesreps[' . $key . '][' . $row->getUniqueId() . '][old_data]" value="' . base64_encode(serialize($oldDetails)) . '" /> ';

        return $html;
    }

}
