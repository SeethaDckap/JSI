<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Customerconnect\Block\Customer\Rfqs\Details\Lines\Renderer;


/**
 * RFQ Line comments renderer
 * 
 * @category   Epicor
 * @package    Epicor_Customerconnect
 * @author     Epicor Websales Team
 */
class Linecomments extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->registry = $registry;
        parent::__construct(
            $context,
            $data
        );
    }


    public function render(\Magento\Framework\DataObject $row)
    {
        $key = $this->registry->registry('rfq_new') ? 'new' : 'existing';
        $index = $this->getColumn()->getIndex();
        $comment = $this->escapeHtml($row->getData($index));

        if ($this->registry->registry('rfqs_editable')) {
            $html = '<textarea class="lines_additional_text"  name="lines[' . $key . '][' . $row->getUniqueId() . '][additional_text]">' . $comment . '</textarea>';
        } else {
            $html = nl2br($comment) . '<input name="lines[' . $key . '][' . $row->getUniqueId() . '][additional_text]" type="hidden" value="' . $comment . '" />';
        }

        return $html;
    }

}
