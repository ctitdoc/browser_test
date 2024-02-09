<?xml version="1.0"  encoding="UTF-8" ?>
<xsl:stylesheet version="1.0"
                xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
                xmlns:php="http://php.net/xsl"
                xmlns:func="http://exslt.org/functions"
                xmlns:str="http://exslt.org/strings"
                xmlns:dyn="http://exslt.org/dynamic"
                xmlns:bi="http://www.ithis.com/xslFunctions"
                extension-element-prefixes="func bi"
>

<!-- This stylesheet works with Xml2ObjectTransformer class as an xsl based framework to transform xml contents into objects
    class name global xsl variable is used to encapsulate the (quiet long) expanded class name of
    the xml 2 objet transformer class as php "use statements" are not supported in xsl... -->

    <xsl:variable name="transformerClassName" select="'TestCommon\Xml2ObjectTransformer\Xml2ObjectTransformer::'"/>

    <!-- php id of the object to build : used in callMethod below to get the object to build from the object register
     (see Xml2ObjectTransformer::$_objectToBuildRegister property).
    -->
    <xsl:param name="objectId"/>

    <!-- These function invokes the actual php object methods.
    This function should called from the templates that make the actual element/attributes processing,
    like that :
       <xsl:variable name="status" select="bi:callMethod('newXXX',1,string(sepa:Id))"/>
    It will call the method newXXX of the object referenced by $objectId in Xml2ObjectTransformer::$_objectToBuildRegister
    Parameter next to the method name (1 in this example), should be the number of parameters,
    so here just one : string(sepa:Id)
     So with two parameters it would be called like that : bi:callMethod('newXXX',2,string(sepa:Id),$anotherParam).
     The function currently support up to 10 parameters. If more parameters are needed just add new params element
     and update the choose element accordingly.
     -->

    <func:function name="bi:callMethod">
        <!-- param1 should be the name of the method to call -->
        <xsl:param name="param1"/>
        <!-- param2 should be the number of arguments -->
        <xsl:param name="param2"/>
        <!-- param3 to param12 should the arguments if any. If one need to call more arguments just add more param elements. -->
        <xsl:param name="param3"/>
        <xsl:param name="param4"/>
        <xsl:param name="param5"/>
        <xsl:param name="param6"/>
        <xsl:param name="param7"/>
        <xsl:param name="param8"/>
        <xsl:param name="param9"/>
        <xsl:param name="param10"/>
        <xsl:param name="param11"/>
        <xsl:param name="param12"/>
        <xsl:variable name="staticMethodName" select="concat($transformerClassName,'callMethod')"/>
        <xsl:choose>
            <xsl:when test="$param2 = 0">
                <xsl:variable name="status" select="php:function ($staticMethodName,$objectId,$param1)"/>
            </xsl:when>
            <xsl:when test="$param2 = 1">
                <xsl:variable name="status" select="php:function ($staticMethodName,$objectId,$param1,$param3)"/>
            </xsl:when>
            <xsl:when test="$param2 = 2">
                <xsl:variable name="status" select="php:function ($staticMethodName,$objectId,$param1,$param3,$param4)"/>
            </xsl:when>
            <xsl:when test="$param2 = 3">
                <xsl:variable name="status" select="php:function ($staticMethodName,$objectId,$param1,$param3,$param4,$param5)"/>
            </xsl:when>
            <xsl:when test="$param2 = 4">
                <xsl:variable name="status" select="php:function ($staticMethodName,$objectId,$param1,$param3,$param4,$param5,$param6)"/>
            </xsl:when>
            <xsl:when test="$param2 = 5">
                <xsl:variable name="status" select="php:function ($staticMethodName,$objectId,$param1,$param3,$param4,$param5,$param6,$param7)"/>
            </xsl:when>
            <xsl:when test="$param2 = 6">
                <xsl:variable name="status" select="php:function ($staticMethodName,$objectId,$param1,$param3,$param4,$param5,$param6,$param7,$param8)"/>
            </xsl:when>
            <xsl:when test="$param2 = 7">
                <xsl:variable name="status" select="php:function ($staticMethodName,$objectId,$param1,$param3,$param4,$param5,$param6,$param7,$param8,$param9)"/>
            </xsl:when>
            <xsl:when test="$param2 = 8">
                <xsl:variable name="status" select="php:function ($staticMethodName,$objectId,$param1,$param3,$param4,$param5,$param6,$param7,$param8,$param9,$param10)"/>
            </xsl:when>
            <xsl:when test="$param2 = 9">
                <xsl:variable name="status" select="php:function ($staticMethodName,$objectId,$param1,$param3,$param4,$param5,$param6,$param7,$param8,$param9,$param10,$param11)"/>
            </xsl:when>
            <xsl:when test="$param2 = 10">
                <xsl:variable name="status" select="php:function ($staticMethodName,$objectId,$param1,$param3,$param4,$param5,$param6,$param7,$param8,$param9,$param10,$param11,$param12)"/>
            </xsl:when>
            <xsl:otherwise>
                <xsl:message><xsl:value-of select="'xsl function bi:callMethod() in xml_2_object.xsl : is called with too much parameters : number of parameters coded in this function can be increased if needed.'"/></xsl:message>
            </xsl:otherwise>
        </xsl:choose>
    </func:function>


    <func:function name="bi:array">
        <!-- param1 should be the name of the method to call -->
        <xsl:param name="param1"/>
        <!-- param2 should be the number of arguments -->
        <xsl:param name="param2"/>
        <!-- param3 to param12 should the arguments if any. If one need to call more arguments just add more param elements. -->
        <xsl:param name="param3"/>
        <xsl:param name="param4"/>
        <xsl:param name="param5"/>
        <xsl:param name="param6"/>
        <xsl:param name="param7"/>
        <xsl:param name="param8"/>
        <xsl:param name="param9"/>
        <xsl:param name="param10"/>
        <xsl:param name="param11"/>
        <xsl:param name="param12"/>
        <xsl:variable name="staticMethodName" select="concat($transformerClassName,'array')"/>
        <xsl:choose>
            <xsl:when test="$param2 = 0">
                <xsl:variable name="status" select="php:function ($staticMethodName,$objectId,$param1,$param2)"/>
            </xsl:when>
            <xsl:when test="$param2 = 1">
                <xsl:variable name="status" select="php:function ($staticMethodName,$objectId,$param1,$param2,$param3,$param4)"/>
            </xsl:when>
             <xsl:when test="$param2 = 2">
                <xsl:variable name="status" select="php:function ($staticMethodName,$objectId,$param1,$param2,$param3,$param4,$param5,$param6)"/>
            </xsl:when>
            <xsl:when test="$param2 = 3">
                <xsl:variable name="status" select="php:function ($staticMethodName,$objectId,$param1,$param2,$param3,$param4,$param5,$param6,$param7,$param8)"/>
            </xsl:when>
            <xsl:when test="$param2 = 4">
                <xsl:variable name="status" select="php:function ($staticMethodName,$objectId,$param1,$param2,$param3,$param4,$param5,$param6,$param7,$param8,$param9,$param10)"/>
            </xsl:when>
            <xsl:when test="$param2 = 5">
                <xsl:variable name="status" select="php:function ($staticMethodName,$objectId,$param1,$param2,$param3,$param4,$param5,$param6,$param7,$param8,$param9,$param10,$param11,$param12)"/>
            </xsl:when>
            <xsl:otherwise>
                <xsl:message><xsl:value-of select="'xsl function bi:array() in xml_2_object.xsl : is called with too much parameters : number of parameters coded in this function can be increased if needed.'"/></xsl:message>
            </xsl:otherwise>
        </xsl:choose>
    </func:function>

    <func:function name="bi:array_value">
        <!-- $objectId should be a multidimensional key/value array -->
        <!-- param1 should be the number of key arguments -->
        <xsl:param name="param1"/>
        <!-- param2 to 5 should be keys. If one need more key arguments just add more param elements. -->
        <!-- the function returns the value of these keys in $objectId array or an error if these keys do not exist in the array-->
        <xsl:param name="param2"/>
        <xsl:param name="param3"/>
        <xsl:param name="param4"/>
        <xsl:param name="param5"/>
        <xsl:param name="param6"/>
        <xsl:variable name="staticMethodName" select="concat($transformerClassName,'array_value')"/>
        <xsl:choose>
            <xsl:when test="$param1 = 0">
                <func:result select="php:function ($staticMethodName,$objectId,$param1)"/>
            </xsl:when>
            <xsl:when test="$param1 = 1">
                <func:result select="php:function ($staticMethodName,$objectId,$param1,$param2)"/>
            </xsl:when>
            <xsl:when test="$param1 = 2">
                <func:result select="php:function ($staticMethodName,$objectId,$param1,$param2,$param3)"/>
            </xsl:when>
            <xsl:when test="$param1 = 3">
                <func:result select="php:function ($staticMethodName,$objectId,$param1,$param2,$param3,$param4)"/>
            </xsl:when>
            <xsl:when test="$param1 = 4">
                <func:result select="php:function ($staticMethodName,$objectId,$param1,$param2,$param3,$param4,$param5)"/>
            </xsl:when>
            <xsl:when test="$param1 = 5">
                <func:result select="php:function ($staticMethodName,$objectId,$param1,$param2,$param3,$param4,$param5,$param6)"/>
            </xsl:when>
            <xsl:otherwise>
                <xsl:message><xsl:value-of select="'xsl function bi:array_value() in xml_2_object.xsl : is called with too much parameters : number of parameters coded in this function can be increased if needed.'"/></xsl:message>
            </xsl:otherwise>
        </xsl:choose>
    </func:function>

    <!-- this function resolves a simple xpath element path /elem1/elem2 without namespace prefix
    thus one can use this xpath for different document namespaces
    -->
    <func:function name="bi:p">
        <xsl:param name="path"/>
        <func:result select="dyn:evaluate(bi:pxp($path))"/>
    </func:function>

    <!-- this function returns a valid "namespace agnostic" xpath expression from a simple xpath element path /elem1/elem2 -->
    <func:function name="bi:pxp">
        <xsl:param name="path"/>
        <xsl:variable name="converted">
            <xsl:variable name="apos">'</xsl:variable>
            <xsl:for-each select="str:tokenize($path,'/')">
                <xsl:value-of select="concat('/*[local-name()=',$apos,string(.),$apos,']')"/>
            </xsl:for-each>
        </xsl:variable>
        <func:result select="concat('.', $converted)"/>
    </func:function>

</xsl:stylesheet>