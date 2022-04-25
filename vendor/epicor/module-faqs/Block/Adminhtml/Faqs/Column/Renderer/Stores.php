<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Faqs\Block\Adminhtml\Faqs\Column\Renderer;


/**
 * Renderer for Sites > Stores column, shows list of stores for the site
 * 
 * @category   Epicor
 * @package    Epicor_HostingManager
 * @author     Epicor Websales Team
 */
class Stores extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        array $data = []
    ) {
        $this->storeManager = $storeManager;
        parent::__construct(
            $context,
            $data
        );
    }


    public function render(\Magento\Framework\DataObject $row)
    {
        try {
            $storeIds = explode(',', $row->getStores());
            $websites = array();
            foreach ($storeIds as $storeId) {
                $store = $this->storeManager->getStore($storeId);
                $website = $store->getWebsite();
                $websites[$website->getId()]['Name'] = $website->getName();
                $websites[$website->getId()]['Stores'][$storeId] = $store->getName();
            }

            $output = '<dl>';
            foreach ($websites as $websiteId => $website) {
                $output .= '<dt>Website:</dt>';
                $output .= '<dd style="margin-left:10px;">' . $website['Name'] . '</dd>';
                $output .= '<dt style="margin-left:10px;">Stores:</dt>';
                foreach ($website['Stores'] as $storeId => $store) {
                    $output .= '<dd style="margin-left:20px;">' . $store . '</dd>';
                }
            }
        } catch (\Exception $e) {
            $output = '<dl><dt>Store Not Found. Store may have been deleted</dt>';
        }
        $output .= '</dl>';

        return $output;
    }

}
