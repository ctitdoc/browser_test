<?xml version="1.0"  encoding="UTF-8" ?>
<xsl:stylesheet version="1.0"
                xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
                xmlns:sepa="urn:iso:std:iso:20022:tech:xsd:pain.001.001.03"
                xmlns:sps="http://www.six-interbank-clearing.com/de/pain.001.001.03.ch.02.xsd"
                xmlns:func="http://exslt.org/functions"
                xmlns:set="http://exslt.org/sets"
                xmlns:bi="http://www.bilendi.com/xslFunctions"
                xmlns:php="http://php.net/xsl"
                extension-element-prefixes="func bi"
>

    <!-- logging.xsl implements a simple logging mechanism in the bi:log function (see its description in this file) -->

    <xsl:import href="../../../Xml2ObjectTransformer/logging.xsl"/>


    <!-- xml_2_object.xsl implements with the Xml2ObjectTransformer class the  bi:callMethod xsl function
    to call the methods of the object to build from the xml contents (see its description in this file) -->

    <xsl:import href="../../../Xml2ObjectTransformer/xml_2_object.xsl"/>


    <!-- These templates make the actual processing of the needed xml elements/attributes. -->

    <!-- Here is a "template" of an xsl:template element that 
      - match element "XXX";
      - call the newXXX method with the YYY child element of XXX as parameter
      - call the processing of elements ZZZ
      You can copy this template and change the method name and parameter(s) : See the description of the bi:callMethod
      function above telling how to do that.
      And you can change ZZZ with the actual xpath of the elements/attributes to further process.
      If you fully remove  select="ZZZ" attribute, then the children of the currently processed element (XXX)
      will be processed by default.
      Or you can remove the apply-templates element if no further processing is required...
      You can also change or adapt or remove the call to bi:log logging method according to your needs
      (see logging.xsl for more info on logging).
      <xsl:template match="XXX">
        <xsl:variable name="log" select="bi:log('debug','Matched XXX element')"/>
        <xsl:variable name="status" select="bi:callMethod('newXXX',1,string(sepa:Id))"/>
        <xsl:apply-templates select="ZZZ"/>
      </xsl:template>

    -->

    <xsl:template match="DocumentSet">
        <xsl:variable name="log" select="bi:log('info','Matched DocumentSet')"/>
        <xsl:apply-templates select="bi:p('Document')"/>
    </xsl:template>


    <xsl:template match="*[local-name()='Document']">
        <!-- logging is for debug purpose only until the "php xslt processor warning level issue is fixed or worked around
        see logging.xsl for more details. -->
        <!-- xsl:variable name="log" select="bi:log('info',concat('Matched Document element ', position()))"/ -->
        <xsl:for-each select="bi:p('CstmrCdtTrfInitn/PmtInf/CdtTrfTxInf')">
            <xsl:variable name="iban" select="string(bi:p('CdtrAcct/Id/IBAN'))"/>
            <xsl:variable name="credit_amount" select="number(bi:p('Amt/InstdAmt'))"/>
            <xsl:variable name="bic" select="string(bi:p('CdtrAgt/FinInstnId/BIC'))"/>
            <xsl:variable name="status" select="bi:array('append', 3, 'iban', $iban,'credit_amount', $credit_amount,'bic',$bic)"/>
        </xsl:for-each>
    </xsl:template>

    <!-- This template disable the following default xslt behaviors :
      - any processing of element or attribute without an explicit template to process them (due to wildcard * and @* in match attribute)
      - xsl default processing of text contents (or text nodes) that outputs them in the result document.
      two consequences :
      - templates that call the "newXXX" event notifications should be called explictely (by apply-templates,call-template or foreach xsl elements)
      - the processing time is optimized as only then elements that have to be notified to the builder are processed
    -->

    <xsl:template match="*|@*|text()"/>

</xsl:stylesheet>