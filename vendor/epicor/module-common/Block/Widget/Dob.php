<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Common\Block\Widget;

use Magento\Customer\Api\CustomerMetadataInterface;
use Magento\Framework\Api\ArrayObjectSearch;

/**
 * Class Dob
 *
 * @SuppressWarnings(PHPMD.DepthOfInheritance)
 */
class Dob extends \Magento\Customer\Block\Widget\Dob {

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Epicor\Customerconnect\Helper\Data
     */
    protected $customerconnectHelper;

    /**
     * @return void
     */
    public function _construct() {

        parent::_construct();
        $this->setTemplate('epicor_common/widget/dob.phtml');
    }

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context, \Magento\Customer\Helper\Address $addressHelper, CustomerMetadataInterface $customerMetadata, \Magento\Framework\View\Element\Html\Date $dateElement, \Magento\Framework\Data\Form\FilterFactory $filterFactory, \Magento\Framework\Registry $registry, \Epicor\Customerconnect\Helper\Data $customerconnectHelper, array $data = []
    ) {
        $this->registry = $registry;
        $this->customerconnectHelper = $customerconnectHelper;
        parent::__construct($context, $addressHelper, $customerMetadata, $dateElement, $filterFactory, $data);
    }

    /**
     * Create correct date field
     *
     * @return string
     */
    public function getFieldHtml() {
        $this->dateElement->setData([
            'extra_params' => $this->isRequired() ? 'data-validate="{required:true}"' : '  onkeydown="return false"',
            'name' => 'required_date',
            'id' => 'required_date',
            'class' => $this->getHtmlClass().'required-entry validate-date',
            'value' => $this->getRequiredDate(),
            'date_format' => $this->getDateFormat(),
            'image' => $this->getViewFileUrl('Magento_Theme::calendar.png'),
            'change_month' => 'true',
            'change_year' => 'true',
            'show_on' => 'both'
        ]);
        return $this->dateElement->getHtml();
    }

    public function getRequiredDate() {
        $rfq = $this->registry->registry('customer_connect_rfq_details');
        $helper = $this->customerconnectHelper;
        $getFormat = $helper->convertPhpToIsoFormat($this->getDateFormat());
        $data = '';
        if (!empty($rfq->getRequiredDate())) {
            try {
                $date = new \DateTime($rfq->getRequiredDate());
                $data = date($getFormat, strtotime($rfq->getRequiredDate()));
            } catch (\Exception $ex) {
                $data = $date;
            }
        }

        return $data;
    }

}