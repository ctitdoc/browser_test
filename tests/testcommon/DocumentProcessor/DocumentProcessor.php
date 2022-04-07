<?php

namespace TestCommon\DocumentProcessor;

use TestCommon\Xml2ObjectTransformer\Xml2ObjectTransformer;

/**
 * This builder integrates the Xml2ObjectTransformer framework class to enable xml 2 php objects transformations;
 * reading of this class is a pre-requisite for such transformations.
 */

class DocumentProcessor implements DocumentProcessorInterface
{

    /**
     * This methods uses the Xml2ObjectTransformer class to process a document with an xslt script integrating a PHP object;
     * Reading this class description/comment and of its related xslt scripts is a prerequisite;
     * @param string $documentFilePath : the document to be processed by the xslt script;
     * @param string $xslFileRelativePath : the path to an "Xml2ObjectTransformer compliant" xslt script executed
     * to process the document; this relative path is converted to an absolute path using getXslFile method
     * (see how in its cmt);
     * @param bool $isMultipleDocument : true if $documentFilePath contains a set of document elements that should be
     * surrounded by a parent element to have a valid xml document with a single root element (defaults to false);
     * @param string $logLevel : the log level setting of the xslt script among :
     * 'error', 'warning', 'info', 'debug' (defaults to 'info');
     * @param Object $object : the object the xsl script takes as parameter (and call its methods) to process the document.
     * see DocumentProcessorTest::testProcessDocumentWithObject for a live usage example.
     */
    public function processDocumentWithObject(string $documentFilePath, string $xslFileRelativePath, object $object,
                                              bool $isMultipleDocument = false, string $logLevel = 'info'): void
    {
        $xslFileAbsolutePath = $this->getXslFile($xslFileRelativePath);
        Xml2ObjectTransformer::buildObject($documentFilePath,
            $xslFileAbsolutePath,
            $object,
            $logLevel,
            ($isMultipleDocument) ? 'DocumentSet' : null);
    }

    /**
     * variant of previous method with a DOMDocument instead of a document file path;
     */
    public function processDOMDocumentWithObject(\DOMDocument $document, string $xslFileRelativePath, object $object,
                                              bool $isMultipleDocument = false, string $logLevel = 'info'): void
    {
        $xslFileAbsolutePath = $this->getXslFile($xslFileRelativePath);
        Xml2ObjectTransformer::buildObjectFromDOM($document,
            $xslFileAbsolutePath,
            $object,
            $logLevel);
    }

    /**
     * @param string $xslFileRelativePath
     * @return string the xsl script full path supposing it is :
     * <this class full path directory>/xslt/$xslFileRelativePath
     *
     */
    public function getXslFile(string $xslFileRelativePath) : string {
        return __DIR__ . '/xslt/' . $xslFileRelativePath;
    }

}