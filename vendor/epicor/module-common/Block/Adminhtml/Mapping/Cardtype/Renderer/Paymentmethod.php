<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Block\Adminhtml\Mapping\Cardtype\Renderer;


/**
 * Payment method renderer
 *
 * @category   Epicor
 * @package    Epicor_Comm
 * @author Epicor Websales Team
 */
class Paymentmethod extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Payment\Model\Config
     */
    protected $paymentConfig;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Payment\Model\Config $paymentConfig,
        array $data = []
    ) {
        $this->registry = $registry;
        $this->paymentConfig = $paymentConfig;
        $this->scopeConfig = $context->getScopeConfig();
        parent::__construct(
            $context,
            $data
        );
    }


    /**
     * Render column
     *
     * @param   \Magento\Framework\DataObject $row
     * @return  string
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        return $this->_getPaymentMethod($row->getData($this->getColumn()->getIndex()));
    }

    public function _getPaymentMethod($code)
    {
        if (!$this->registry->registry('payment_method_cache')) {
            $payments = $this->paymentConfig->getActiveMethods();

            $methods = array(array('value' => '', 'label' => __('--Please Select--')));

            $methods['all'] = array(
                'label' => 'All Payment Methods',
                'value' => 'all',
            );

            foreach ($payments as $paymentCode => $paymentModel) {
                $paymentTitle = $this->scopeConfig->getValue('payment/' . $paymentCode . '/title', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                $methods[$paymentCode] = array(
                    'label' => $paymentTitle,
                    'value' => $paymentCode,
                );
            }

            $this->registry->register('payment_method_cache', $methods, true);
        } else {
            $methods = $this->registry->registry('payment_method_cache');
        }

        return isset($methods[$code]) ? $methods[$code]['label'] : $code;
    }

}
