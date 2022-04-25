<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Block\Adminhtml\Renderer\Erpimages;


/**
 * ERP Image store list renderer
 *
 * @category   Epicor
 * @package    Epicor_Comm
 * @author     Epicor Websales Team
 */
class Stores extends \Magento\Backend\Block\AbstractBlock
{

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Epicor\Comm\Block\Adminhtml\Renderer\Erpimages\Types
     */
    protected $commAdminhtmlRendererErpimagesTypes;

    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Epicor\Comm\Block\Adminhtml\Renderer\Erpimages\Types $commAdminhtmlRendererErpimagesTypes,
        array $data = []
    ) {
        $this->commAdminhtmlRendererErpimagesTypes = $commAdminhtmlRendererErpimagesTypes;
        $this->storeManager = $storeManager;
        parent::__construct(
            $context,
            $data
        );
    }


     public function _generateHtml(\Magento\Framework\DataObject $row)
      { 
         $html = '';
        $stores = $row->getStores();
        if ($stores) {
            $storeData = $this->storeManager->getStores();
            $stores = $stores->getData();
            $storeInfo = $row->getStoreInfo()->getData();
            $storeList = array();

            $html .= '
                <table class="border">
                    <thead>
                        <tr class="headings">
                            <th>Store</th>
                            <th>Description</th>
                            <th>Position</th>
                            <th>Types</th>
                            <th>Source</th>
                        </tr>
                    </thead>
                    <tbody>
            ';

            foreach ($stores as $store) {
                if (isset($storeData[$store])) {
                    //$typeRenderer = $this->commAdminhtmlRendererErpimagesTypes->create(array('row_data' => $storeInfo[$store]));
                    $typeRenderer = $this->commAdminhtmlRendererErpimagesTypes->_generateHtml($storeInfo[$store]);

                    $source = array();
                    if ($storeInfo[$store]['STK']) {
                        $source[] = 'STK';
                    }
                    if ($storeInfo[$store]['STT']) {
                        $source[] = 'STT';
                    }

                    $html .= '
                        <tr>
                            <td>' . $storeData[$store]->getName() . '</td>
                            <td>' . $storeInfo[$store]['description'] . '</td>
                            <td>' . $storeInfo[$store]['position'] . '</td>
                            <td>' . $typeRenderer . '</td>
                            <td>' . implode(',', $source) . '</td>
                        </tr>
                    ';
                }
            }

            $html .= '
                    </tbody>
                </table>
            ';

            $html .= implode('<br />', $storeList);
        }
        return $html;
    }

}
