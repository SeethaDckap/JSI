<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Lists\Model\ListModel\Type;


/**
 * Type Class for Contracts
 *
 * @category   Epicor
 * @package    Epicor_Lists
 * @author     Epicor Websales Team
 */
class AbstractModel extends \Epicor\Lists\Model\ListModel
{

    protected $isList = true;
    protected $isContract = false;
    protected $erpMsg = '';
    protected $erpMsgSections = array();
    protected $hasErpMsg = false;
    protected $hasExtraFields = false;
    protected $supportsAddresses = false;
    protected $hasExtraProductFields = false;
    protected $supportedSettings = array(
        'M',
        //  'F',
        'D',
        'Q',
    );
    protected $visibleSections = array(
        'labels',
        'erpaccounts',
        'brands',
        'websites',
        'stores',
        'customers',
        'products',
    );
    protected $editableSections = array(
        'labels',
        'erpaccounts',
        'brands',
        'websites',
        'stores',
        'customers',
        'products',
    );

    /**
     * Does this type have an ERP Message
     *
     * @return boolean
     */
    public function hasErpMsg()
    {
        return $this->hasErpMsg;
    }

    /**
     * Does this type have extra details fields?
     *
     * @return boolean
     */
    public function hasExtraFields()
    {
        return $this->hasExtraFields;
    }

    /**
     * Does this type have extra product fields?
     *
     * @return boolean
     */
    public function hasExtraProductFields()
    {
        return $this->hasExtraProductFields;
    }

    /**
     * Does this type supports addresses?
     *
     * @return boolean
     */
    public function supportsAddresses()
    {
        return $this->supportsAddresses;
    }

    /**
     * Return Name of ERP Message
     *
     * @return boolean
     */
    public function getErpMsg()
    {
        return $this->erpMsg;
    }

    /**
     * Return Name of ERP Message Sections to be managed
     *
     * @return boolean
     */
    public function getErpMsgSections()
    {
        return $this->erpMsgSections;
    }

    /**
     * Returns array of supported settings
     *
     * @return boolean
     */
    public function getSupportedSettings()
    {
        return $this->supportedSettings;
    }

    /**
     * Return Whether this is a list or not
     *
     * @return boolean
     */
    public function isList()
    {
        return $this->isList;
    }

    /**
     * Return Whether this is a contract or not
     *
     * @return boolean
     */
    public function isContract()
    {
        return $this->isContract;
    }

    /**
     * Return Whether a tab is visible
     *
     * @return boolean
     */
    public function isSectionVisible($tab)
    {
        return in_array($tab, $this->visibleSections);
    }

    /**
     * Return Whether a tab is editable
     *
     * @return boolean
     */
    public function isSectionEditable($tab)
    {
        return in_array($tab, $this->editableSections);
    }

}
