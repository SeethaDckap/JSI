<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Block\Adminhtml\Message\Syn\Send;


/**
 * Epicor_Comm_Block_Adminhtml_Message_Syn
 * 
 * Form for SYN Send form
 * 
 * @author Gareth.James
 */
class Form extends \Magento\Backend\Block\Widget\Form\Generic
{

    /**
     * @var \Epicor\Comm\Model\Config\Source\Sync\StoresFactory
     */
    protected $commConfigSourceSyncStoresFactory;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Epicor\Comm\Helper\Data
     */
    protected $commHelper;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTimeFactory
     */
    protected $dateTimeDateTimeFactory;

    /**
     * @var \Epicor\Comm\Helper\Messaging
     */
    protected $commMessagingHelper;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\Data\FormFactory
     */
    protected $formFactory;

    /**
     * @var \Magento\Framework\Locale\ListsInterface
     */
    protected $_localeLists;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $_date;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Epicor\Comm\Model\Config\Source\Sync\StoresFactory $commConfigSourceSyncStoresFactory,
        \Epicor\Comm\Helper\Data $commHelper,
        \Magento\Framework\Stdlib\DateTime\DateTimeFactory $dateTimeDateTimeFactory,
        \Epicor\Comm\Helper\Messaging $commMessagingHelper,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Framework\Locale\ListsInterface $localeLists,
        array $data = []
    ) {
        $this->formFactory = $formFactory;
        $this->commConfigSourceSyncStoresFactory = $commConfigSourceSyncStoresFactory;
        $this->scopeConfig = $context->getScopeConfig();
        $this->commHelper = $commHelper;
        $this->dateTimeDateTimeFactory = $dateTimeDateTimeFactory;
        $this->commMessagingHelper = $commMessagingHelper;
        $this->storeManager = $context->getStoreManager();
        $this->_localeLists = $localeLists;
        $this->_date = $context->getLocaleDate();
       
        parent::__construct($context, $registry, $formFactory, $data);

    }


    protected function _prepareForm()
    {
        $form = $this->_formFactory->create(
           ['data' => [
                'id' => 'edit_form',
                'action' => $this->getUrl('*/*/send'),
                'method' => 'post'
                ]
          ]);


        $form->setUseContainer(true);
        $this->setForm($form);
       
        $fieldset = $form->addFieldset(
            'layout_block_form', array(
            'legend' => __('Send SYN request')
            )
        );
                
        $fieldset->addField(
            'syn_stores', 'multiselect', array(
            'label' => __('Store'),
            'required' => true,
            'values' => $this->commConfigSourceSyncStoresFactory->create()->toOptionArray(),
            'name' => 'stores'
            )
        );
       
        $fieldset->addField(
            'messages0', 'multiselect', array(
            'label' => __('Upload Messages'),
            'required' => true,
            'name' => 'simple_messages',
            'class' => 'simple_messages',
            'style' => 'float:left',
            'values' => $this->_getSimpleUploadMessages(),
            )
        );
        
        // for hidden fields lable not required..
        $fieldset->addField(
            'messages1', 'multiselect', array(
            'label' => __('Upload Messages'),
            'required' => false,
            'name' => 'advanced_messages',
            'class' => 'advanced_messages scalable',
            'values' => $this->_getUploadMessages(),
            )
        );
       
        $fieldset->addField('msg_toggle_button','hidden', [
            'required' => false,
            'class' => 'hidden',
            'name' => '',
            ]);
        
       
        $hidden_button = $fieldset->addField('toggle_button', 'button', array(
            'value' => __('Advanced'),
            'style' => 'float: right'
            
        ));
        $hidden_button->setAfterElementHtml("
        <script>
          require(['jquery','prototype'], function(jQuery){
            $('messages1').up(1).style.display='none';
             jQuery('#toggle_button').click(function(event) {
                  $('toggle_button').value = ($('toggle_button').getValue() == 'Advanced')? 'Simple' : 'Advanced'; 
                  $('messages1').toggleClassName('required-entry').up(1).toggle();
                  $('messages0').toggleClassName('required-entry').up(1).toggle();
                  $$('select#messages1 option').each(function(o){				
                      o.selected = false;
                  });
                });    
           });
        </script>");
        
        $fieldset->addField(
            'languages', 'multiselect', array(
            'label' => __('Languages'),
            'required' => true,
            'size' => 5,
            'name' => 'languages',
            'values' => $this->_getLanguages()
            )
        );
         
         
       
        $syncType = $fieldset->addField(
            'sync_type', 'select', array(
            'label' => __('Sync Type'),
            'required' => true,
            'values' => array(
                'full' => 'Full Sync - No From Date',
                'partial' => 'Partial Sync - With From Date'
            ),
            'name' => 'sync_type'
            )
        );
        
        
        
        $after ='<small>To Update Date Click on Calendar Icon</small>';
        $synValues = $this->scopeConfig->getValue('epicor_comm_enabled_messages/syn_request/full_sync');
        if ($this->scopeConfig->getValue('Epicor_Comm/licensing/erp', \Magento\Store\Model\ScopeInterface::SCOPE_STORE) != '') {
            if ($synValues) {
                $msgArray = explode(',', $synValues);
                $endMsg = '';
                if (count($msgArray) > 1) {
                    $lastMsg = array_pop($msgArray);
                    $endMsg = " and " . $lastMsg;
                }
                $flatMsg = implode(',', $msgArray);
                $after .= "<br /><span><strong>Note:</strong> Date From will be ignored for {$flatMsg}{$endMsg}</br><span>messages</span>.";
            }
        }
       
         
        $datetime = $this->_date->date(strtotime('-1 week'))->format('d-m-Y');
        $fromDate = $fieldset->addField('date', 'date', array( 
                'label' => __('Date From'),
                'tabindex' => 1,
                'class' => 'validate-date',
                'required' => false,
                'name' =>'date',
               //'readonly' => true,
                'format' => 'Y-MM-dd',
                'date_format' => 'Y-MM-dd',
                'value' => $datetime,
                'note' => $after,
                )
            );
       
        $fromTime = $fieldset->addField(
            'time', 'time', array(
            'label' => __('Time From'),
            'tabindex' => 1,
            'class' => 'validate-time',
            'required' => false,
            'name' => 'time',
            'format' => 'hh:mm:ss',
            'value' => $this->dateTimeDateTimeFactory->create()->date('H:i:s'),
            'notice' => 'adas'
            )
        );
        
        $this->setChild(
            'form_after',
            $this->getLayout()->createBlock(
                'Magento\Backend\Block\Widget\Form\Element\Dependence'
            )->addFieldMap($fromDate->getHtmlId(),$fromDate->getName())
            ->addFieldMap($syncType->getHtmlId(), $syncType->getName())
            ->addFieldMap($fromTime->getHtmlId(), $fromTime->getName())
            ->addFieldDependence(
                $fromTime->getName(), $syncType->getName(), 'partial'
            )
            ->addFieldDependence(
                $fromDate->getName(), $syncType->getName(), 'partial'
            )
            
         );
        
        return parent::_prepareForm();
    }

    /**
     * Gets an array of upload messages, by checking the relevant directory
     * 
     * @return array - array of messages
     */
    private function _getSimpleUploadMessages()
    {
        $messages = array();
        $simpleMessages = $this->commMessagingHelper->getSimpleMessageTypes('sync');
        if (!empty($simpleMessages)) {
            foreach ($simpleMessages as $type => $desc) {
                $desc = (array) $desc;
                $msgTypes = implode(',', $desc['value']);    // put codes required for task in csv string
                $messages[] = array(
                    'label' => $desc['label'],
                    'value' => strtoupper($msgTypes),
                );
            }
        }
        return $messages;
    }

    private function _getUploadMessages()
    {
        $messages = array();
        $messageTypes = $this->commMessagingHelper->getMessageTypes('upload');

        $excluded = array('FSUB', 'FREQ');

        if (!empty($messageTypes)) {
            foreach ($messageTypes as $type => $desc) {
                $type = strtoupper($type);
                if (!in_array($type, $excluded)) {
                    $desc = (array) $desc;
                    $messages[] = array(
                        'label' => $desc['label'],
                        'value' => $type,
                    );
                }
            }
        }

        return $messages;
    }

    /**
     * Gets an array of languages by checking each store for it's language
     * 
     * @return array - array of languages
     */
    private function _getLanguages()
    {   
        $stores = $this->storeManager->getStores();

        $languages = array();
        //M1 > M2 Translation Begin (Rule p2-6.4)
        $locales = $this->_localeLists->getOptionLocales();
        
        //M1 > M2 Translation End
        foreach ($stores as $store) {
            $storeCode = $this->scopeConfig->getValue('general/locale/code', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $store->getId());

            // only add the language if we don't already have it
            if (isset($storeCode) && !isset($languages[$storeCode])) {

                $test = new \Zend_Locale($storeCode);

                $languages[$storeCode] = array(
                    'label' => $storeCode,
                    'value' => $storeCode,
                );
            }
        }

        foreach ($locales as $locale) {
            if (isset($languages[$locale['value']])) {
                $languages[$locale['value']]['label'] = $locale['label'];
            }
        }

        return $languages;
    }

}
