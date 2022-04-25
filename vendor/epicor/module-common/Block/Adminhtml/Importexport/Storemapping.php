<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Block\Adminhtml\Importexport;


class Storemapping extends \Magento\Widget\Block\Adminhtml\Widget
{

    public $_storesList;
    protected $_storeNumber = 0;
    protected $_inputfile;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
    )
    {
        $this->registry = $registry;
        parent::__construct(
            $context,
            $data
        );

        $this->_serializedArray = unserialize(file_get_contents($_FILES['import_epicor_comm_settings_file']['tmp_name']));
        // extract the stores from the config data and place into $this->_storeslist array
        foreach ($this->_serializedArray as $key => $arrayEntry) {
            if (isset($arrayEntry['config_data']['data'])) {
                $configData = $arrayEntry['config_data']['data'];
                foreach (unserialize($arrayEntry['config_data']['data']) as $key => $value) {
                    $this->_storesList[$value['scope_id']] = $value['scope_id'];
                }
                array_shift($this->_storesList);     // remove 0 from beginning of array as we don't want it to be displayed
            }
        }
    }

    public function getHeaderText()
    {
        return __('Import / Export Store Mapping Settings');
    }

    //M1 > M2 Translation Begin (Rule p2-8)
    /**
     * @param $key
     * @return mixed
     */
    public function registry($key)
    {
        return $this->registry->registry($key);
    }

    /**
     * @param $key
     * @param $value
     * @param bool $graceful
     */
    public function register($key, $value, $graceful = false)
    {
        $this->registry->register($key, $value, $graceful);
    }

    /**
     * @param $key
     */
    public function unregister($key)
    {
        $this->registry->unregister($key);
    }
    
    
    public function getButtonHtml()
    {
        $button = $this->getLayout()->createBlock(
            'Magento\Backend\Block\Widget\Button'
        )->setData(
            [
                'id' => 'importbutton',
                'label' => __('Submit'),
                'onclick' => 'this.form.submit()',
            ]
        );

        return $button->toHtml();
    }
    
    //M1 > M2 Translation End

}
