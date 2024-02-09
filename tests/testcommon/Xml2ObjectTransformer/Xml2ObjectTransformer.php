<?php

namespace TestCommon\Xml2ObjectTransformer;

use Exception;

/**
 *   - This class implements a simple xsl based xml data binding framework;
 *   - It can be used to import xml files in db, and/or load them as specific objects and/or php arrays etc...;
 *   - It works together with the xml_2_object.xsl stylesheet to do so (look at it's description too)
 *   - How it works basically :
 *     - Create an ObjectReader class for the data you want to extract as objects from the xml;
 *     - Create together an object structure with the methods to store the extracted data;
 *     - Create together an xsl script that perform the actual data extractions and calling these methods;
 *   - for more info see how this class is used in the DocumentProcessor class.
 *
 */
class Xml2ObjectTransformer
{

    
    /**
     * @var  array $_objectToBuildRegister
     */
    public static  $_objectToBuildRegister = array();

    public static function callMethod() : void
    {
        $params = func_get_args();
        $objectId = array_shift($params);
        $methodName = array_shift($params);
        $object = self::$_objectToBuildRegister[$objectId];
        if (empty($object)) {
            throw new Exception('Object ' . $objectId . ' does not exist or is not available from _objectToBuildRegister : 
            cannot call method ' . $methodName . ' on it ' . ' with param ' . var_export($params,true));
        }
        call_user_func_array(array($object,$methodName),$params);
    }

    /**
     * @param $fileLocation
     * @param $xslFileLocation
     * @param $objectToBuild
     * @param string $logLevel
     * @param null $multipleDocElement
     * see description of DocumentProcessor::processDocumentWithObject,
     * and DocumentProcessorTest::testProcessDocumentWithObject for a live usage example.
     */
    public static function buildObject(string $fileLocation, string $xslFileLocation, object $objectToBuild,
                                       string $logLevel = 'error', string $multipleDocElement = null, $uri = null) {
/*
        $documentSet= <<<EOB
<?xml version="1.0" encoding="UTF-8"?>
<DocumentSet>
<Document xmlns:xsi="http://www.w3.org/2001/XMLSchema" xmlns="urn:iso:std:iso:20022:tech:xsd:acmt.02z.001.01:Report">
<AcctSwtchngInfSvcRptV01>
<Assgnmt>
...
</Mod>
</AcctSwtchngInfSvcRptV01>
</Document>
</DocumentSet>
EOB;*/
        try {
            $document = new \DOMDocument();
            libxml_use_internal_errors(false);
            if (!empty($multipleDocElement)) {
                $content = file_get_contents($fileLocation);
                $contentWithoutXmlPI = preg_replace('/<\?xml[ \t]+.*\?>/', '', $content);
                $documentSet = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<$multipleDocElement>\n" . $contentWithoutXmlPI . "\n</$multipleDocElement>";
                //for quicker test iterations on the xml : comment the line above, uncomment  $documentSet variable above and
                //copy paste the xml you want to test with the $xslFileLocation xsl stylesheet.
                ;
                if (!$document->loadXML($documentSet)) {
                    $errors = libxml_get_errors();
                    libxml_use_internal_errors(false);
                    throw new Exception(var_export($errors,true));
                } else {
                    $document->documentURI = $fileLocation;
                }
            } else {
                if (!$document->load($fileLocation)) {
                    $errors = libxml_get_errors();
                    libxml_use_internal_errors(false);
                    throw new Exception(var_export($errors,true));
                }

            }
            self::buildObjectFromDOM($document, $xslFileLocation, $objectToBuild, $logLevel, $uri);
        } finally {
            libxml_use_internal_errors(false);
        }
    }


    public static function buildObjectFromDOM(\DOMDocument $document, string $xslFileLocation, object $objectToBuild,
                                       string $logLevel = 'error', $uri = null)
    {
        try {
            if (empty($objectToBuild)) {
                throw new Exception(
                    'parameter $objectToBuild should not be empty but an instanciated object implementing 
                the method called in $xslFileLocation to create its properties'
                );
            }
            $objectId = spl_object_hash ($objectToBuild);
            self::$_objectToBuildRegister[$objectId] = $objectToBuild;
            $xsldoc = new \DOMDocument();
            $xsldoc->load($xslFileLocation);
            $proc = new \XSLTProcessor();
            $proc->registerPHPFunctions();
            //if your stylesheet uses logging.xsl, set the log level you want here (see logging.xsl for more info).
            $proc->setParameter('', 'globalLogLevel', $logLevel);
            $proc->setParameter('', 'objectId', $objectId);
            $proc->importStyleSheet($xsldoc);
            if (empty($uri)) {
                $proc->transformToXML($document);
            } else {
                $proc->transformToUri($document, $uri);
            }
            unset(self::$_objectToBuildRegister[$objectId]);
        } catch (Exception $e) {
            if (isset($objectId)) {
                unset(self::$_objectToBuildRegister[$objectId]);
            }
            throw $e;
        }
    }

    /**
     * create arbitrary php array $arrayParam from a parameter list of key,values parameters pairs, and invoke callMethod with
     * the two first parameters, object_id and method_name, and this array, sothat object_id->method_name($arrayParam) is
     * finally invoked; the third parameter should be the number of key,value parameter pairs.
     */
    public static function array() : void
    {
        $params = func_get_args();
        $objectId = array_shift($params);
        $methodName = array_shift($params);
        $keyValuePairsCount = array_shift($params);
        $arrayParam = array();
        for ($i=0;$i<$keyValuePairsCount;$i++) {
            $key = array_shift($params);
            $value = array_shift($params);
            $arrayParam[$key] = $value;
        }
         self::callMethod($objectId, $methodName, $arrayParam);
    }

    public static function array_value()
    {
        $params = func_get_args();
        $objectId = array_shift($params);
        $keyCount = array_shift($params);
        $object = self::$_objectToBuildRegister[$objectId];
        $result = $object;
        for ($i=0;$i<$keyCount;$i++) {
            $key = array_shift($params);
            $result = $result[$key];
        }
        return $result;
    }

}
