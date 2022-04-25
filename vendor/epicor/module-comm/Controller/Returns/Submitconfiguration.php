<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Controller\Returns;

class Submitconfiguration extends \Epicor\Comm\Controller\Returns
{

    /**
     * @var \Epicor\Comm\Helper\Returns
     */
    protected $commReturnsHelper;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Epicor\Comm\Model\Customer\ReturnModelFactory
     */
    protected $commCustomerReturnModelFactory;

    /**
     * @var \Magento\Framework\Session\Generic
     */
    protected $generic;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $catalogProductFactory;

    /**
     * @var \Magento\Framework\DataObjectFactory
     */
    protected $dataObjectFactory;


    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Epicor\Comm\Helper\Returns $commReturnsHelper,
        \Magento\Customer\Model\Session $customerSession,
        \Epicor\Comm\Model\Customer\ReturnModelFactory $commCustomerReturnModelFactory,
        \Magento\Framework\Session\Generic $generic,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Catalog\Model\ProductFactory $catalogProductFactory,
        \Magento\Framework\DataObjectFactory $dataObjectFactory)
    {
        $this->request = $request;
        $this->dataObjectFactory = $dataObjectFactory;
        $this->catalogProductFactory = $catalogProductFactory;
        parent::__construct(
            $context,
            $commReturnsHelper,
            $customerSession,
            $commCustomerReturnModelFactory,
            $generic,
            $jsonHelper,
            $registry);
    }

public function execute()
    {
        $productId = $this->request->getParam('productid');
        $att = $this->request->getParam('super_attribute');
        $grp = $this->request->getParam('super_group');
        $opt = $this->request->getParam('options');
        $qty = $this->request->getParam('qty');

        $product = $this->catalogProductFactory->create();
        /* @var $product Epicor_Comm_Model_Product */

        $product->load($productId);

        $request = $this->dataObjectFactory->create(array(
            'product' => $productId,
            'super_group' => $grp,
            'super_attribute' => $att,
            'qty' => $qty,
            'options' => $opt
        ));

        $cartCandidates = $product->getTypeInstance(true)
            ->prepareForCartAdvanced($request, $product, null);

        $finalProduct = array();

        foreach ($cartCandidates as $candidate) {
            if ($product->getTypeId() == 'configurable') {
                if ($candidate->getSku() != $product->getSku()) {
                    $finalProduct = $candidate;
                }
            } else if ($product->getTypeId() == 'grouped') {
                $finalProduct[] = $candidate;
            } else {
                $finalProduct = $candidate;
            }
        }

        if (!$finalProduct) {
            $response = json_encode(array('error' => $cartCandidates));
        } else {
            /* @var $finalProduct Epicor_Comm_Model_Product */

            $prodArray = array();
            if (!is_array($finalProduct)) {
                $product = $this->catalogProductFactory->create()->load($finalProduct->getId());
                $product->setQty($qty);
                $prodArray[$productId] = $product->getData();
            } else {
                $prodArray['grouped'] = array();
                foreach ($finalProduct as $fProduct) {
                    $product = $this->catalogProductFactory->create()->load($fProduct->getId());
                    $product->setQty($grp[$fProduct->getId()]);
                    $prodArray['grouped'][] = $product->getData();
                }
            }

            $response = json_encode($prodArray);
        }
        $this->getResponse()->setBody($response);
    }

}
