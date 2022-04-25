<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
namespace Epicor\Common\Model;


/**
 * XML validator
 *
 * Validates an XML object based on rules supplied
 * 
 * @author Gareth.James
 */
class Xmlvalidator extends \Magento\Framework\DataObject
{

    protected $_conditions = array(
        'eq' => 'equal to',
        'neq' => 'not equal to',
        'lteq' => 'less than or equal to',
        'gteq' => 'greater than or equal to',
    );
    private $_errors = array();
    private $_valid = true;
    public function __construct(
        array $data = []
    ) {
        parent::__construct(
            $data
        );
    }


    /**
     * Returns the valid flag
     * 
     * @return boolean
     */
    public function isValid()
    {
        return $this->_valid;
    }

    /**
     * Returns errors array
     * 
     * @return array
     */
    public function getErrors()
    {
        return $this->_errors;
    }

    /**
     * Validates and xml object based on rules set for the request type
     * 
     * @param array $rules - rules for validation
     * @param \Epicor\Common\Model\Xmlvarien $obj - an object to use instead of the main request (useful for recursion)
     * 
     * @return boolean
     */
    public function validateXmlObject($rules, $obj)
    {
        if (!empty($rules)) {
            if (!$obj instanceof \Magento\Framework\DataObject) {
                $this->_addError('Invalid XML object');
            } else {
                $this->_processRules($rules, $obj);
            }
        }

        return $this->isValid();
    }

    /**
     * Processes the rules agains an object
     * 
     * @param array $rules
     * @param \Epicor\Common\Model\Xmlvarien $obj
     */
    public function _processRules($rules, $obj)
    {
        foreach ($rules as $rule) {
            $xPath = $this->getPath();

            $path = $rule['path'];
            $this->setPath($this->getPath() . '/' . $path);

            // check the path exists
            if (!$obj->hasVarienDataFromPath($path)) {
                if (!isset($rule['tagOptional']) || !$rule['tagOptional']) {
                    $this->_addError('[' . $this->getPath() . '] ' . $path . ' tag is missing');
                }
            } else {

                $pathObj = $obj->getVarienDataFromPath($path);
                /* @var $pathObj Epicor_Common_Model_Xmlvarien */

                if ($this->_validateRuleCondition($pathObj, $rule)) {
                    // check for repeating groups validation
                    if (isset($rule['repeatingGroup'])) {

                        $this->_validateRepeatingGroups($rule['repeatingGroup'], $pathObj);
                    }
                }
            }
            $this->setPath($xPath);
        }
    }

    /**
     * Validates an object against the condition key of a rule
     * 
     * @param \Epicor\Common\Model\Xmlvarien $obj - object to check
     * @param array $rule - rule array
     * 
     * @return boolean
     */
    private function _validateRuleCondition($obj, $rule)
    {

        $valid = true;

        if (isset($rule['condition'])) {
            if (!is_array($rule['condition'])) {
                if ($obj != $rule['condition']) {
                    $valid = false;
                    $this->_addError($this->getPath() . ' does not match validation condition'
                        . '(should be equal to ' . $rule['condition'] . ')');
                }
            } else {

                switch ($rule['condition'][0]) {
                    case 'lteq':
                        if (!($obj <= $rule['condition'][1])) {
                            $valid = false;
                            $this->_addError($this->getPath() . ' does not match validation condition'
                                . '(should be ' . $this->_validationConditions[$rule['condition'][0]] . $rule['condition'][1] . ')');
                        }
                        break;
                    case 'gteq':
                        if (!($obj >= $rule['condition'][1])) {
                            $valid = false;
                            $this->_addError($this->getPath() . ' does not match validation condition'
                                . '(should be ' . $this->_validationConditions[$rule['condition'][0]] . $rule['condition'][1] . ')');
                        }
                        break;
                    case 'eq':
                        if ($obj != $rule['condition'][1]) {
                            $valid = false;
                            $this->_addError($this->getPath() . ' does not match validation condition'
                                . '(should be ' . $this->_validationConditions[$rule['condition'][0]] . $rule['condition'][1] . ')');
                        }
                        break;
                    case 'neq':
                        if ($obj == $rule['condition'][1]) {
                            $valid = false;
                            $this->_addError($this->getPath() . ' does not match validation condition'
                                . '(should be ' . $this->_validationConditions[$rule['condition'][0]] . $rule['condition'][1] . ')');
                        }
                        break;
                    case 'notnull':
                        if (is_null($obj)) {
                            $valid = false;
                            $this->_addError($this->getPath() . ' does not match validation condition (should not be null)');
                        }
                        break;
                    case 'in':
                        if (!in_array($obj, $rule['condition'][1])) {
                            $valid = false;
                            $this->_addError($this->getPath() . ' does not match validation condition'
                                . '(must be one of ' . implode(',', $rule['condition'][1]) . ')');
                        }
                        break;
                }
            }
        } else {

            $nullable = isset($rule['nullable']) ? $rule['nullable'] : true;

            if (is_null($obj) && !isset($rule['repeatValidation']) && !$nullable) {
                $valid = false;
                $this->_addError($this->getPath() . ' does not match validation condition (should not be null)');
            }
        }

        return $valid;
    }

    /**
     * Validates an array of repeating groups
     * 
     * @param array $rules - rules for the group
     * @param \Epicor\Common\Model\Xmlvarien $pathObj
     * 
     */
    private function _validateRepeatingGroups($rules, $pathObj)
    {
        foreach ($rules as $repeatingGroupRules) {

            $rPath = $this->getPath();
            $this->setPath($this->getPath() . '/' . $repeatingGroupRules['path']);
            if (!$pathObj->hasVarienDataFromPath($repeatingGroupRules['path'])) {
                $this->_addError('Repeating group ' . $repeatingGroupRules['path'] . ' is missing  [' . $this->getPath() . ']');
            } else {

                $repeatingGroup = $pathObj->getVarienDataFromPath($repeatingGroupRules['path']);
                /* @var $repeatingGroup Epicor_Common_Model_Xmlvarien */

                if (!is_array($repeatingGroup)) {
                    $repeatingGroup = array($repeatingGroup);
                }

                // validate each value in the repeating group according to the rules
                foreach ($repeatingGroup as $x => $group) {
                    /* @var $group Epicor_Common_Model_Xmlvarien */
                    $gPath = $this->getPath();
                    $this->setPath($this->getPath() . '(' . $x . ')');

                    if (isset($repeatingGroupRules['elements'])) {
                        $this->_processRules($repeatingGroupRules['elements'], $group);
                    } else {
                        $this->_validateRuleCondition($group, $repeatingGroupRules);
                    }

                    $this->setPath($gPath);
                }
            }
            $this->setPath($rPath);
        }
    }

    /**
     * Adds an error to the error array
     * 
     * @param string $error
     */
    private function _addError($error)
    {
        $this->_valid = false;
        $this->_errors[] = $error;
    }

}
