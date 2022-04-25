<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Block\Adminhtml\Quickstart\Edit\Tab\General;


class Form extends \Epicor\Common\Block\Adminhtml\Quickstart\Edit\Tab\AbstractBlock
{

    /**
     * @var \Epicor\Common\Helper\Locale\Format\Date
     */
    protected $commonLocaleFormatDateHelper;

    /**
     * @var \Epicor\Comm\Model\GlobalConfig\Config
     */
    protected $globalConfig;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Epicor\Common\Helper\Quickstart $commonQuickstartHelper,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Epicor\Common\Helper\Locale\Format\Date $commonLocaleFormatDateHelper,
        \Epicor\Comm\Model\GlobalConfig\Config $globalConfig
    ) {
        $this->globalConfig = $globalConfig;
        $this->commonLocaleFormatDateHelper = $commonLocaleFormatDateHelper;
        parent::__construct(
            $context,
            $commonQuickstartHelper,
            $formFactory
        );
    }

    protected function getKeysToRender()
    {
        return array('erp', 'networking', 'licensing', 'site_monitoring');
    }

    protected function formExtras(\Magento\Framework\Data\Form $form)
    {
        //M1 > M2 Translation Begin (Rule 4)
        //$versionInfo = Mage::getConfig()->getNode('global/ecc_version_info')->asArray();
        $versionInfo = $this->globalConfig->get('ecc_version_info');
        //M1 > M2 Translation End

        $helper = $this->commonLocaleFormatDateHelper;

        $fieldset = $form->addFieldset('ecc_version', array('legend' => 'ECC Version', 'class' => 'fieldset-complete'));

        ksort($versionInfo);

        foreach ($versionInfo as $module => $info) {
            if (isset($info['version']) && isset($info['released'])) {

                if (empty($info['released'])) {
                    $text = $info['version'] . ' (Not Released)';
                } else {
                    $text = $info['version'] . ' (Released ' . $helper->getLocalFormatDate($info['released'], \IntlDateFormatter::MEDIUM, false) . ')';
                }
                $formattedModuleName = str_replace('_', ' ', $module);
                $fieldset->addField('ecc_version_module_' . $module, 'note', array(
                    'name' => 'ecc_version_module_' . $module,
                    'label' => $formattedModuleName,
                    'text' => $text
                ));
            }
        }

        return parent::formExtras($form);
    }

}
