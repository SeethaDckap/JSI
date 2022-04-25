<?php
/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

namespace Epicor\Punchout\Model\Response;

use Epicor\Punchout\Model\AbstractPunchout;

/**
 * Class Response
 *
 * @package Epicor\Punchout\Model\Response
 */
class Response extends AbstractPunchout implements ResponseInterface
{

    /**
     * Node attribute
     */
    const NODE_ATTRIBUTE = '_attributes';

    /**
     *
     * @var \DOMDocument
     */
    protected $xmlDoc;

    /**
     * Send response
     *
     * @return \SimpleXMLElement
     */
    public function sendResponse($data)
    {
        $this->imp = new \DOMImplementation();
        $this->dtd = $this->imp->createDocumentType('cXML', '', '"http://xml.cXML.org/schemas/cXML/1.2.011/cXML.dtd"');
        $this->xmlDoc = $this->imp->createDocument("", "", $this->dtd);

        $this->createXml($data, $this->xmlDoc);
        $this->xmlDoc->formatOutput = true;
        return @$this->xmlDoc->saveXML();

    }//end sendResponse()


    /**
     * Preapare Data for Setup Response
     *
     * @param $data
     *
     * @return array
     */
    public function prepareData($data)
    {
        $responseData = [
            'cXML' => [
                self::NODE_ATTRIBUTE => [
                    'timestamp' => $data['timestamp'],
                    'payloadID' => $this->getPayloadID(),
                ],
                'Response'    => [

                ],
            ],
        ];
        $errorMessage =   $this->getErrorMessage($data);

        $statusCode =   [
            '_attributes' => [
                'code' => $data['code'],
                'text' => $errorMessage['text'],
            ], $errorMessage['msg'],
        ];
        $responseData['cXML']['Response']['Status'] = $statusCode;

        if (!empty($data['url'])) {
            $punchoutResponse = [
                'StartPage' => [
                    'URL' => $data['url'],
                ],
            ];

            $responseData['cXML']['Response']['PunchOutSetupResponse'] = $punchoutResponse;
        }
        return $responseData;

    }//end prepareData()

    // function defination to convert array to xml
    /**
     *  function defination to convert array to xml
     *
     * @param $data
     * @param $xmlData
     */
    protected function createXml($data, $node)
    {
        if (is_array($data)) {
            foreach ($data as $element => $value) {
                $tag = $node->appendChild($this->xmlDoc->createElement($element));
                $this->addAttributes($tag, $value);
                if (is_array($value)) {
                    $this->iterateArray($value, $tag, $element, $node);
                } elseif ($value != null) {
                    $tag->appendChild($this->xmlDoc->createTextNode($value));
                }
            }
        }
    }

    protected function addAttributes(&$tag, &$value)
    {
        if (is_array($value) && isset($value[self::NODE_ATTRIBUTE])) {
            foreach ($value[self::NODE_ATTRIBUTE] as $attr => $attr_value) {
                $tag->appendChild(
                    $this->xmlDoc->createAttribute($attr)
                )->appendChild(
                    $this->xmlDoc->createTextNode($attr_value)
                );
            }
            unset($value[self::NODE_ATTRIBUTE]);

            if (count($value) == 1) {
                $keys = array_keys($value);
                if ($keys[0] == 0 && isset($value[0]) && !is_array($value[0])) {
                    $tag->appendChild($this->xmlDoc->createTextNode($value[0]));
                    $value = null;
                }
            }
        }
    }


    public function iterateArray($value, $tag, $element, $node)
    {
        $numItems = count($value);
        $i = 0;
        foreach ($value as $key => $key_value) {
            if (!is_int($key)) {
                $this->createXml($value, $tag);
                break;
            } else {
                $this->getInternalNode($key_value, $key, $element, $node, $numItems, $i, $tag);
            }
        }
    }

    public function getInternalNode($key_value, $key, $element, $node, $numItems, $i, $tag)
    {
        if (is_int($key) && !is_array($key_value)) {
            if ($key == 0) {
                $tag->appendChild($this->xmlDoc->createTextNode($key_value));
            } else {
                $this->createXml([$element => $key_value], $node);
            }
        } else {
            $this->addAttributes($tag, $key_value);
            $this->createXml($key_value, $tag);
            if (++$i !== $numItems) {
                $tag = $node->appendChild($this->xmlDoc->createElement($element));
            }
        }
    }
    public function getErrorMessage($data)
    {
        return $this->getMessage($data, self::MSG_CODES[$data['code']]);

    }//end getErrorMessage()


    /**
     * Get Error Message based upon code.
     *
     * @param array $data         Data.
     * @param string $messageCode MessageCode.
     *
     * @return array
     */
    public function getMessage(array $data, array $messageCode)
    {
        $message = $messageCode['message'];
        if (!empty($data['error_message'])) {
            $message = $data['error_message'];
        }
        return [
               'code'  => $data['code'],
                'text' => $messageCode['text'],
                'msg'  => $message,
            ];

    }//end getMessage()


}//end class

