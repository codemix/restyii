<?php


namespace Restyii\MimeType;

use \Restyii\Web\Request;
use \Restyii\Web\Response;

/**
 * Parses and formats responses as XML.
 *
 * @package Restyii\MimeType
 */
class XML extends Base
{

    /**
     * @var string[] the file extensions for this format.
     */
    public $fileExtensions = array(
        "xml",
    );

    /**
     * @var string[] the mime types for this format,
     */
    public $mimeTypes = array(
        "application/xml",
        "application/hal+xml",
        "text/xml",
    );

    /**
     * @var string the name of the XML container
     */
    public $containerName = 'resource';

    /**
     * Parse the given input and return either an array of attributes => values
     * or null if the input could not be parsed.
     *
     * @param Request $request the request object
     *
     * @return array|null
     */
    public function parse(Request $request)
    {
        $input = $request->getRawBody();
        if (empty($input))
            return null;
        return $this->xmlToArray($input);
    }

    /**
     * MimeType the given request response
     *
     * @param Response $response the response to format
     *
     * @return string the formatted response data
     */
    public function format(Response $response)
    {
        $prepared = $this->prepare($response->data);
        return $this->arrayToXML($prepared, $this->containerName);
    }


    public function xmlToArray($xml)
    {
        $doc = new \DOMDocument();
        $doc->loadXML($xml);
        return $this->nodeToArray($doc->documentElement);
    }

    protected function nodeToArray(\DOMNode $node)
    {
        $output = array();
        switch ($node->nodeType) {
            case XML_TEXT_NODE;
            case XML_CDATA_SECTION_NODE;
                return trim($node->textContent);
            case XML_ELEMENT_NODE;
                foreach($node->childNodes as $child) {
                    $processed = $this->nodeToArray($child);
                    if (isset($child->tagName)) {
                        $key = $child->tagName;
                        if (!isset($output[$key]))
                            $output[$key] = array();
                        $output[$key][] = $processed;
                    }
                    else if (!empty($processed) || $processed === '0')
                        $output = (string) $processed;
                }
                if ($node->attributes->length && !is_array($output))
                    $output = array('@content' => $output);
                if (is_array($output)) {
                    if ($node->attributes->length) {
                        $output['@attributes'] = array();
                        foreach($node->attributes as $name => $attribute)
                            $output['@attributes'][$name] = (string) $attribute->value;
                    }
                    foreach($output as $key => $value)
                        if (is_array($value) && $key !== '@attributes' && count($value) === 1)
                            $output[$key] = $value[0];
                }
        }
        if (is_array($output) && !count($output))
            return null;
        else
            return $output;
    }

    /**
     * A placeholder to allow conversions of PHP arrays to XML
     * @param array $data The data to be converted
     * @param string $rootNodeName The name of the root XML node, defaults to "response"
     * @param string $itemNodeName The name of the XML node to use when converting a 0 based array to XML, defaults to "item"
     * @return string The converted XML string
     */
    public function arrayToXML($data, $rootNodeName = "resource", $itemNodeName = "item")
    {
        $writer = new \XMLWriter();
        $writer->openMemory();
        $writer->startDocument('1.0', \Yii::app()->charset);
        $writer->startElement($rootNodeName);
        if (!is_array($data) && !is_object($data)) {
            $data = array($data);
        }
        $this->encodeXMLElementInternal($writer,$data, $itemNodeName);
        $writer->endElement();
        return $writer->outputMemory(true);
    }

    /**
     * Encodes an array or object to XML.
     * This is an internal function used by {@link arrayToXML()}, do not call it directly
     * @param \XMLWriter $writer the XML writer
     * @param array|object $data the data to encode to XML
     * @param string $itemNodeName The name of the XML node to use when converting a 0 based array to XML
     */
    protected function encodeXMLElementInternal(\XMLWriter $writer, $data, $itemNodeName) {
        foreach($data as $key => $value) {
            if (is_numeric($key)) {
                $key = $itemNodeName;
            }
            elseif (!preg_match("/^_?(?!(xml|[_\d\W]))([\w.-]+)$/",$key)) {
                $key = preg_replace("/[^A-Za-z0-9\.\-$]/","_",$key);
            }
            if (is_array($value) || is_object($value)) {
                $writer->startElement($key);
                $this->encodeXMLElementInternal($writer,$value,$itemNodeName);
                $writer->endElement();
            }
            else {
                $writer->writeElement($key,$value);
            }
        }
    }
}
