<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Comm\Block\Adminhtml\Widget\Grid\Column\Renderer\Syn;


/**
 * Syn log link renderer
 *
 * @category   Epicor
 * @package    Epicor_Comm
 * @author Epicor Websales Team
 */
class Uploadedlink extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{

    /**
     * @var \Epicor\Comm\Helper\Entityreg
     */
    protected $commEntityregHelper;

    /**
     * @var \Magento\Backend\Helper\Data
     */
    protected $backendHelper;

    /**
     * @var \Magento\Framework\Locale\ResolverInterface
     */
    protected $_localeResolver;
    
     /**
     * @var \Magento\Framework\Url\EncoderInterface
     */
    protected $urlEncoder;
    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Epicor\Comm\Helper\Entityreg $commEntityregHelper,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \Magento\Framework\Url\EncoderInterface $urlEncoder,
        array $data = []
    ) {
        $this->commEntityregHelper = $commEntityregHelper;
        $this->backendHelper = $backendHelper;
        $this->_localeResolver = $localeResolver;
        $this->urlEncoder = $urlEncoder;
        parent::__construct(
            $context,
            $data
        );
    }


    /**
     * Render column
     *
     * @param   \Epicor\Comm\Model\Syn\Log $row
     * @return  string
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        /* @var $row Epicor_Comm_Model_Syn_Log */
        $helper = $this->commEntityregHelper;
        /* @var $helper Epicor_Comm_Helper_Entityreg */

        $typeFilter = implode(',', $helper->getRegistryTypeDescriptions($this->unserialize($row->getTypes())));

        if (!empty($typeFilter)) {
            //M1 > M2 Translation Begin (Rule p2-6.4)
           // $locale = $this->_localeResolver->getLocale()->getLocaleCode(); 
            $locale = $this->_localeResolver->getLocale(); 
            $from = $this->urlEncoder->encode($this->getUrl('*/*/*', $this->getRequest()->getParams()));
            $modified = $helper->getLocalDate(
                strtotime($row->getCreatedAt()), \IntlDateFormatter::SHORT, true
            );
            
            $link = $this->backendHelper->getUrl(
                'adminhtml/epicorcomm_advanced_entityreg/index', array(
                'filter' => urlencode($this->urlEncoder->encode('type=' . $typeFilter
                        . '&is_dirty=1'
                        . '&modified_at[locale]=' . $locale
                        . '&modified_at[to]=' . $modified)),
                'back' => $from
                )
            );
            $html = '<a href="' . $link . '">' . __('Purge List') . '</a>';
        } else {
            $html = '';
        }

        return $html;
    }
    
    /**
     * {@inheritDoc}
     */
    public function unserialize($string)
    {
        if (false === $string || null === $string || '' === $string) {
            throw new \InvalidArgumentException('Unable to unserialize value.');
        }
       try {
        $result = unserialize($string, ['allowed_classes' => false]);
       } catch (\Exception $e) {     
           $result = [];
           // throw new \InvalidArgumentException($string.'-Unable to unserialize value.'.$e->getMessage());
       }
        return $result;
    }

}
