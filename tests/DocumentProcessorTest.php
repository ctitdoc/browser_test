<?php

use PHPUnit\Framework\Attributes\WithoutErrorHandler;
use PHPUnit\Framework\TestCase;
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
 * To run this test, one has to adapt the path of $xmlFile, $xsltFile, $xmlResultFile to the path where this repo is
 * deployed.
 *
 */
class DocumentProcessorTest extends TestCase
{
    public function setUp(): void
    {
    }

    public function tearDown(): void
    {
    }

    /*
     * as XSLTProcessor outputs <xsl:message... as php warnings, let's disable phpunit's default error handling
     * which stops the execution at first warning...
     */
    #[WithoutErrorHandler]
    public function testVerbolia()
    {
        $xmlFile = '/home/franck/dev/browser_test/tmp/test1.xml';
        $xsltFile = '/home/franck/dev/browser_test/tmp/verboliaProcessing.xsl';
        $xmlResultFile = '/home/franck/dev/browser_test/tmp/test1_out.xml';
        $processor = new DocumentProcessor();
        $categories = new \ArrayObject(array());

        $categories['es'] = 'Tipo de vehiculo';
        $categories['ca'] = 'Tipa de vehicula';

        //in debug mode all xsl script's bi:log(..) message calls should be displayed in console,
        //replace debug by error to get only error level messages;
        //one can also play with info and warning log levels, see logging.xsl/bi:log() function.
        $processor->processDocumentWithObject($xmlFile, $xsltFile, $categories, false, 'debug', $xmlResultFile);

    }
}
