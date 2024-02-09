<?php

use TestCommon\BrowserTest;
use TestCommon\DocumentProcessor\DocumentProcessor;

/**
 * Test (that serves also as demo) of the DocumentProcessor service.
 * This service manage XML document processing using XSLT scripts, with
 * support of php objects usage in the XSLT scripts.
 * It encapsulates all the lower level XML/XSLT APIs to do so, and also provides an enhanced xsl script logging api,
 * and enhanced XSLT processing Error managment.
 * One can look at the testVebolia method, that demonstrates a transformation of an xml file in the shoppingfeed XML
 * format test1.xml, into an xml file in the verbolia XML format, using a $categories php array parameter to access
 * a label in different languages (es => spanish or ca => catalan).
 * To run this test, one has to adapt the path of $xmlFile, $xsltFile, $xmlResultFile to the path wher this repo is
 * deployed.
 *
 */
class DocumentProcessorTest extends BrowserTest
{
    use MpzSharedScenario\MpzSharedScenarioTrait;

    protected $fixtures;

    protected $testStates = array();

    public function setUp(): void
    {
    }

    public function tearDown(): void
    {
    }

    public function testVerbolia()
    {
        $xmlFile = '/home/franck/dev/browser_test/tmp/test1.xml';
        $xsltFile = '/home/franck/dev/browser_test/tmp/verboliaProcessing.xsl';
        $xmlResultFile = '/home/franck/dev/browser_test/tmp/test1_out.xml';
        $processor = new DocumentProcessor();
        $categories = new \ArrayObject(array());

        $categories['es'] = 'Tipo de vehiculo';
        $categories['ca'] = 'Tipa de vehicula';

        $processor->processDocumentWithObject($xmlFile, $xsltFile, $categories, false, 'debug', $xmlResultFile);

    }
}
