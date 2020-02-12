<?php namespace Pyrello\LaravelXml;

use SimpleXMLElement;
use stdClass;

class XmlTools
{
    public static function serialize($data, $rootElement = 'items', $xmlVersion = '1.0', $xmlEncoding = 'UTF-8')
    {
        $xml = new SimpleXMLElement('<?xml version="' . $xmlVersion .'" encoding="' . $xmlEncoding . '"?><response/>');
        static::encode($data, $xml);

        return $xml->asXML();
    }

    public static function encode($arr, SimpleXMLElement $xml = null)
    {
        foreach ($arr as $key => $item) {
            if (is_array($item)) {

                // If the $key is numeric, we convert it to the singular form
                // of the element name it is contained in
                if (is_numeric($key)) {
                    $key = str_singular($xml->getName());
                }
                static::encode($item, $xml->addChild($key));

            } else {

                // If the item is a boolean, convert it to a string, so that it shows up
                if (is_bool($item)) {
                    $item = ($item) ? 'true' : 'false';
                }

                // We use the $xml->{$key} form to add the item because this causes
                // conversion of '&' => '&amp;'
                $xml->{$key} = $item;
            }
        }

        return $xml->asXML();
    }

    /**
     * @param SimpleXMLElement $xml
     * @return array
     */
    public static function decode(SimpleXMLElement $xml)
    {
        $arr = [];

        foreach ($xml as $k=>$element)
        {
            $e = get_object_vars($element);
            if (!empty($e))
            {
				foreach($e AS $k1=>$v1) {
					if($v1 instanceof SimpleXMLElement) {
						$e[$k1] = static::decode($v1);
					} elseif(is_array($v1)) {
						if(count($v1)) {
							foreach($v1 AS $k2=>$v2) {
								if($v2 instanceof SimpleXMLElement) {
									$v1[$k2] = static::decode($v2);
								}
							}
							$e[$k1] = $v1;
						} else {
							$e[$k1] = null;
						}
					}
				}
				$value = $e[0]?? $e;
            }
            else
            {
                $value = trim($element);
            }

            $parent = current($element->xpath('..'));
            $tag = $element->getName();

            if (\Str::singular($parent->getName()) === $tag) {
                $tag = null;
            }

            if ($tag) {
                $arr[$tag] = $value;
            }
            else {
                $arr[] = $value;
            }
        }

        return $arr;
    }
} 
