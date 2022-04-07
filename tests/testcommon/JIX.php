<?php

namespace TestCommon;

use DOMDocument;
use DOMXPath;

/**
 * Class JIX
 * @package testcommon
 *
 * deliver Json In Xml datatsets as php arrays, taking advantage of xml modularisation features like entities
 * which can contain text and/or tagged text and/or reference to file containing tagged text (using SYSTEM keyword).
 * Xml comments can also be used as json does not support comments.
 * Sadly php default xml parser does not support conditionnal xml  (ie doctype's conditional sections, which
 * combined with entities provide a basic environment/modular data configuration mechanism).
 * More info about such xml features on these tutorial pages :
 * http://xmlwriter.net/xml_guide/entity_declaration.shtml
 * http://xmlwriter.net/xml_guide/doctype_declaration.shtml
 * http://xmlwriter.net/xml_guide/conditional_section.shtml
 *
 */
class JIX
{

    /*
     * quelques instructions memo...
                 $content = file_get_contents('php://input');
            $doc = new DOMDocument("1.0", "UTF-8");
            $doc->loadXML($content);
            $docFileElement = $doc->getElementsByTagName('xmlCodeDocFile');
            $docFile = $docFileElement[0]->textContent;
            $codeDocContent=$docFileElement[0]->nextSibling;
            $codeDoc = new DOMDocument("1.0", "UTF-8");
            $newNode = $codeDoc->importNode($codeDocContent,True);
            $codeDoc->appendChild($newNode);
            $res = $codeDoc->save($docFile);

     */

    /**
     * @param $jixFile
     * @return mixed
     */
    public static function get($jixFile)
    {
        $doc = new DOMDocument("1.0", "UTF-8");
        $doc->resolveExternals = true;
        $doc->substituteEntities = true;
        $doc->load($jixFile);
        $json = $doc->textContent;
        $array = json_decode($json,true);
        return $array;
    }
}
//JIX::get('/media/sf_apps/selenium/tests/conf/default.btc');
//JIX::get('/media/sf_apps/selenium/tests/data/fixtures/SubscriptionScenarioTest.btf');