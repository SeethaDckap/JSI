<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Customerconnect\Block\Customer\Rfqs\Details\Contacts\Renderer;


/**
 * Line comment display
 *
 * @author Gareth.James
 */
class Editabletext extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
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

        $index = $this->getColumn()->getIndex();
        $value = $row->getData($index);

        $key = $this->registry->registry('rfq_new') ? 'new' : 'existing';

        if ($this->registry->registry('rfqs_editable')) {
            $html = '<input type="text" name="contacts[' . $key . '][' . $row->getUniqueId() . '][' . $index . ']" value="' . $value . '" class="contacts_' . $index . '"/>';
        } else {
            $html = $value;
        }

        return $html;
    }

}
