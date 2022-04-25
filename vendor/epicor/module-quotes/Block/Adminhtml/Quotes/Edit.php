<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Quotes\Block\Adminhtml\Quotes;


class Edit extends \Magento\Backend\Block\Widget\Form\Container
{

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Backend\Helper\Data
     */
    protected $backendHelper;

    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Backend\Helper\Data $backendHelper,
        array $data = []
    )
    {
        $this->registry = $registry;
        $this->backendHelper = $backendHelper;
        
        //$this->_objectId = 'id';
        $this->_controller = 'adminhtml\Quotes';
        $this->_blockGroup = 'Epicor_Quotes';
        $this->_mode = 'edit';
       
        parent::__construct(
            $context,
            $data
        );

        $quote = $this->registry->registry('quote');
        
        $backUrl = $this->backendHelper->getUrl('epicorquotes/quotes_quotes/index/');
        
         $this->addButton(
                'backpage', array(
                'label' => __('Back'),
                'onclick' => 'window.location = \'' . $backUrl . '\'',
                'class' => 'action- scalable back'
                ), 0
            );
         
  
        
        if ($quote->isActive()) {
            $rejectUrl = $this->backendHelper->getUrl(
                'epicorquotes/quotes_quotes/reject/', array('id' => $quote->getId())
            );
            
           $this->addButton(
                'rejectquote', array(
                'label' => __('Reject Quote'),
                'onclick' => 'window.location = \'' . $rejectUrl . '\'',
                'class' => 'action- scalable delete',
                ), 1
            );

            $acceptUrl = $this->backendHelper->getUrl(
                'epicorquotes/quotes_quotes/accept/', array('id' => $quote->getId())
            );

            if ($quote->getStatusId() == \Epicor\Quotes\Model\Quote::STATUS_PENDING_RESPONSE) {
                $this->addButton(
                    'acceptquote', array(
                    'label' => __('Accept Quote'),
                    'onclick' => 'quoteform.accept(\'' . $acceptUrl . '\')',
                    'class' => 'save primary',
                    ), 2
                );

                $saveUrl = $this->backendHelper->getUrl(
                    'epicorquotes/quotes_quotes/save/', array('id' => $quote->getId())
                );

                $this->addButton(
                    'savequote', array(
                    'label' => __('Save Quote for Later'),
                    'onclick' => 'quoteform.save(\'' . $saveUrl . '\')',
                    'class' => 'save primary',
                    ), 3
                );
            }
        } elseif (!$quote->isActive() && $quote->getStatusId() != \Epicor\Quotes\Model\Quote::STATUS_QUOTE_ORDERED) {

            if ($quote->getStatusId() == \Epicor\Quotes\Model\Quote::STATUS_QUOTE_ACCEPTED) {
                $label = 'Retract Quote';
            } else {
                $label = 'Re-Activate Quote';
            }

            $reactivateUrl = $this->backendHelper->getUrl(
                'epicorquotes/quotes_quotes/reactivate/', array('id' => $quote->getId())
            );
         
            $this->addButton(
                'activatequote', array(
                'label' => __($label),
                'onclick' => 'window.location = \'' . $reactivateUrl . '\'',
                'class' => 'save primary',
                ), 4
            );
        }
    }
     
    /*
     * To remove the Insert Quote button from the Quote Form,
     *  we have to disable the footer button region
     */
     public function hasFooterButtons()
    {
        return false;
    }
     
    public function getHeaderText()
    {
        $quote = $this->registry->registry('quote');
        /* @var $quote Epicor_Quotes_Model_Quote */
        if ($quote && $quote->getId()) {
            $title = $quote->getId();
            $customer = $quote->getCustomer(true);

            $header = __(
            //M1 > M2 Translation Begin (Rule 55)
                //'Quote-%s %s (%s)', $this->htmlEscape($title), $this->htmlEscape($customer->getName()), $this->htmlEscape($customer->getEmail())
                'Quote-%1 %2 (%3)', $this->escapeHtml($title), $this->escapeHtml($customer->getName()), $this->escapeHtml($customer->getEmail())
            //M1 > M2 Translation End
            );
        } else {
            $header = __('Quote');
        }

        return $header;
    }
    

}
