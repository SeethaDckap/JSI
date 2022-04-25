<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Epicor\Comm\Plugin;

/**
 * Description of PrepareProductSave
 *
 * @author 
 */
class PrepareProductSave {
     /*
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;
    
    public function __construct(\Magento\Framework\App\RequestInterface $request)
    {
        $this->request = $request;
    }
    
    public function beforeInitializeFromData(\Magento\Catalog\Controller\Adminhtml\Product\Initialization\Helper $subject,
            \Magento\Catalog\Model\Product $product,
            array $productData)
    {
        $params = $this->request->getParam('delete_product');
        
        if (isset($params['ecc_erp_images']) && $params['ecc_erp_images']!='' ) {
            $deleteImages = explode(',', $params['ecc_erp_images']);
            $images = $product->getEccErpImages(); 
            foreach ($images as $x => $image) {
                if (in_array($x, $deleteImages)) {
                    unset($images[$x]);
                }
            } 
            $product->setResyncImagesAfterSave(1);
            $productData['ecc_previous_erp_images'] = $product->getEccErpImages();
            $productData['ecc_erp_images'] = $images;
            $productData['ecc_erp_images_processed'] = 0;
        }
        
         return [$product, $productData];
    }
    
}
