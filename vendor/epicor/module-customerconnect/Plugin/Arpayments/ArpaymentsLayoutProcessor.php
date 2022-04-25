<?php
namespace Epicor\Customerconnect\Plugin\Arpayments;

use Magento\Checkout\Block\Checkout\LayoutProcessor;

class ArpaymentsLayoutProcessor
{

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_registry;
    protected $_request;

    public function __construct(\Magento\Framework\Registry $registry, \Magento\Framework\App\Request\Http $request)
    {
        $this->_registry = $registry;
        $this->_request  = $request;
    }


    /**
     * @param LayoutProcessor $subject
     * @param $jsLayout
     * @return mixed
     */
    public function afterProcess(LayoutProcessor $subject, $jsLayout)
    {
        $handle = $this->_request->getFullActionName();
        if ($handle == "customerconnect_arpayments_archeckout") {
            //Remove telephone tooltip
            unset($jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']['shippingAddress']['children']);
            unset($jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']['step-config']['children']);
            unset($jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']['payment']['children']['afterMethods']['children']['discount']);
            unset($jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']['shippingAddress']['children']['before-form']);
            unset($jsLayout['components']['checkout']['children']['steps']['children']['branch-pickup-step']);

            //Term & Condition get from layout
            $agreements = [];
            if (isset($jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']['payment']['children']['payments-list']['children']['before-place-order'])) {
                $beforePlaceOrder = $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']['payment']['children']['payments-list']['children']['before-place-order'];
                foreach ($beforePlaceOrder as $bpokey => $bpovalue) {
                    if ($bpokey == "children") {
                        foreach ($bpovalue as $key => $value) {
                            if ($key == "agreements") {
                                $agreements[$bpokey][$key] = $value;
                            }
                        }
                    } else {
                        $agreements[$bpokey] = $bpovalue;
                    }
                }
            }

            unset($jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']['payment']['children']['payments-list']['children']);

            //Add Term & Condition into layout after delete
            if ($agreements) {
                $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']['payment']['children']['payments-list']['children']['before-place-order'] = $agreements;
            }

            unset($jsLayout['components']['checkout']['children']['estimation']['displayArea']);
            unset($jsLayout['components']['checkout']['children']['estimation']['displayArea']);
            unset($jsLayout['components']['checkout']['children']['steps']['children']['contact-step']);
            unset($jsLayout['components']['checkout']['children']['sidebar']['children']['summary']['displayArea']);
            unset($jsLayout['components']['checkout']['children']['sidebar']['children']['shipping-information']);
            unset($jsLayout['components']['checkout']['children']['progressBar']['displayArea']);

            //Adding Captcha details for Elements payment gateway
            $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']['payment']['children']['payments-list']['children']['elements-captcha'] = [
                'component'     => 'uiComponent',
                'displayArea'   => 'elements-captcha',
                'dataScope'     => 'elements-captcha',
                'provider'      => 'checkoutProvider',
                'config'        => [
                    'template'  => 'Magento_Checkout/payment/before-place-order'
                ],
                'children'      => [
                    'captcha'   => [
                        'component'     => 'Epicor_Elements/js/view/checkout/paymentCaptcha',
                        'displayArea'   => 'elements-captcha',
                        'formId'        => 'element-payment-form',
                        'configSource'  => 'checkoutConfig'
                    ],
                ]
            ];

            $jsLayout['components']['checkout']['children']['sidebar']['children']['summary']['componentDisabled'] = true;

            return $jsLayout;
        } else {
            return $jsLayout;
        }
    }

}
