<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Lists\Block\Adminhtml\Listing\Edit\Tab\Restrictions\Renderer;


/**
 * List Restricted postcode renderer
 *
 * @category   Epicor
 * @package    Epicor_Lists
 * @author     Epicor Websales Team
 */
class Postcode extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Action
{
    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $jsonEncoder,
            $data
        );
    }


    public function render(\Magento\Framework\DataObject $row)
    {
        $postcode = $row->getPostcode();
        $html = str_replace(array('.', '^', '$'), '', $postcode);
        return $html;
    }

}
