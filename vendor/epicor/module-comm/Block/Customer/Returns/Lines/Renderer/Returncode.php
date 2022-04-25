<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Block\Customer\Returns\Lines\Renderer;


class Returncode extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Epicor\Customerconnect\Helper\Messaging
     */
    protected $customerconnectMessagingHelper;

    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Customer\Model\Session $customerSession,
        \Epicor\Customerconnect\Helper\Messaging $customerconnectMessagingHelper,
        array $data = []
    ) {
        $this->registry = $registry;
        $this->customerSession = $customerSession;
        $this->customerconnectMessagingHelper = $customerconnectMessagingHelper;
        parent::__construct(
            $context,
            $data
        );
    }


    public function render(\Magento\Framework\DataObject $row)
    {
        /* @var $row Epicor_Comm_Model_Customer_ReturnModel_Line */

        if (!$this->registry->registry('review_display') && $row->isActionAllowed('Return code')) {
            $disabled = $row->getToBeDeleted() == 'Y' ? ' disabled="disabled"' : '';
            if($disabled) {
                $validateSelect = '';
            } else {
                $validateSelect = ' validate-select';
            }            
            $html = '<select name="lines[' . $row->getUniqueId() . '][return_code]" class="return_line_returncode'.$validateSelect.'"' . $disabled . '>';

            $customer = $this->customerSession->getCustomer();
            /* @var $customer Epicor_Comm_Model_Customer */
            $codes = $customer->getReturnReasonCodes();

            $html .= '<option value="">Please select</option>';

            foreach ($codes as $code => $description) {
                $selected = $row->getReasonCode() == $code ? 'selected="selected"' : '';
                $html .= '<option value="' . $code . '" ' . $selected . '>' . $description . '</option>';
            }
            $html .= '</select>';
        } else {
            $helper = $this->customerconnectMessagingHelper;
            /* @var $helper Epicor_Customerconnect_Helper_Messaging */

            $html = $helper->getReasonCodeDescription($row->getReasonCode());
        }

        return $html;
    }

}
