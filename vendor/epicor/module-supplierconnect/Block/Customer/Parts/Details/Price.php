<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Supplierconnect\Block\Customer\Parts\Details;


/**
 * Parts price info setup
 * 
 * @category   Epicor
 * @package    Epicor_Supplierconnect
 * @author     Epicor Websales Team
 */
class Price extends \Epicor\Supplierconnect\Block\Customer\Info
{

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Epicor\Supplierconnect\Helper\Data $supplierconnectHelper,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Framework\Url\EncoderInterface $urlEncoder,
        array $data = []
    ) {
         parent::__construct(
            $context,
            $supplierconnectHelper,
            $registry,
            $localeResolver,
            $backendHelper,
            $urlEncoder,
            $data
        );
        $this->setColumnCount(2);
        $part = $this->registry->registry('supplier_connect_part_details');
        $this->_infoData = array(
            __('Part Number: ')->render() => $part->getProductCode(),
            __('Subcontract Operation: ')->render() => $part->getPart()->getSubcontractOperation(),
        );
        $this->setTitle(__('Price Information'));
    }

}
