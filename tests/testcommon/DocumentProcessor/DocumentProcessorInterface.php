<?php

namespace TestCommon\DocumentProcessor;

/**
 *
 * Document Processor Interface of the Xml Document managed by the xml schemas of this bundle
 */
interface DocumentProcessorInterface
{

    /**
     * Process a document with an xslt script integrating a PHP object;
     * @param string $documentFilePath : the document to be processed by the xslt script;
     * @param string $xslFileRelativePath : the path to an  xslt script executed
     * to process the document; this relative path is converted to an absolute path using getXslFile method
     * (see how in its cmt);
     * @param bool $isMultipleDocument : true if $documentFilePath contains a set of document elements that should be
     * surrounded by a parent element to have a valid xml document with a single root element (defaults to false);
     * @param string $logLevel : the log level setting of the xslt script among :
     * 'error', 'warning', 'info', 'debug' (defaults to 'info');
     * @param Object $object : the object the xsl script takes as parameter (and call its methods)
     * while processing the document.
     */
    public function processDocumentWithObject(string $documentFilePath, string $xslFileRelativePath, object $object,
                                              bool $isMultipleDocument = false, string $logLevel = 'info'): void;


    /**
     * @param string $xslFileRelativePath
     * @return string the xsl script full path supposing it is :
     * <this interface full path directory>/xslt/$xslFileRelativePath
     *
     */
    public function getXslFile(string $xslFileRelativePath) : string;
}