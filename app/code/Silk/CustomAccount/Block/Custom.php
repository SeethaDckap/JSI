<?php

namespace Silk\CustomAccount\Block;

class Custom extends \Magento\Framework\View\Element\Template
{

    protected $productFactory;

    protected $categoryFactory;

    protected $request;

    protected $varFactory;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Variable\Model\VariableFactory $varFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->categoryFactory = $categoryFactory;
        $this->productFactory = $productFactory;
        $this->request = $request;
        $this->varFactory = $varFactory;
    }

    public function getCategoryById($id)
    {
        try {
            return $this->categoryFactory->create()->load($id);
        } catch (\Exception $e) {
            return null;
        }
    }

    public function getProductById($id)
    {
        try {
            return $this->productFactory->create()->load($id);
        } catch (\Exception $e) {
            return null;
        }
    }

    public function getParam($code){
        return $this->request->getParam($code);
    }

    public function getVariableValue($code){
        $var = $this->varFactory->create()->loadByCode($code);

        return $var ? $var->getPlainValue() : '';
    }

}
