<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
                xmlns:func="http://exslt.org/functions"
                xmlns:bi="http://www.ithis.com/xslFunctions"
                extension-element-prefixes="func bi"
>
    <!-- logging.xsl implements a simple logging mechanism in the bi:log function (see its description in this file) -->

    <xsl:import href="../tests/testcommon/Xml2ObjectTransformer/logging.xsl"/>


    <!-- xml_2_object.xsl implements with the Xml2ObjectTransformer class the  bi:callMethod xsl function
    to call the methods of the object to build from the xml contents (see its description in this file) -->

    <xsl:import href="../tests/testcommon/Xml2ObjectTransformer/xml_2_object.xsl"/>

    <!-- defines output as XML and configure elements whose text content should be surrounded by CDATA sections to avoid
    text content conflicts with XML text syntax -->
    <xsl:output method="XML"
                cdata-section-elements="id title url salePrice imageUrl brand category quantityInStock type value"/>

    <xsl:template match="/">
        <channel>
            <xsl:apply-templates select="catalog/products/product"/>
        </channel>
    </xsl:template>

    <xsl:template match="product">
        <item lang="{@lang}">
            <id>
                <xsl:value-of select="reference"/>
            </id>
            <title>
                <xsl:value-of select="name"/>
            </title>
            <url>
                <xsl:value-of select="link"/>
            </url>
            <salePrice>
                <xsl:value-of select="price"/>
            </salePrice>
            <imageUrl>
                <xsl:value-of select="images/image[@type='main']"/>
            </imageUrl>

            <!-- this debug log should only be displayed in debug mode -->
            <xsl:variable name="log1" select="bi:log('debug', concat('product lang is: ', @lang))"/>


            <xsl:apply-templates select="attributes/attribute[name = bi:array_value(1, string(../../@lang))]"/>
            <brand>
                <xsl:value-of select="brand/name"/>
            </brand>
            <attributes>
                <xsl:apply-templates select="attributes/attribute[name !=  bi:array_value(1, string(../../@lang))]"/>
            </attributes>
            <quantityInStock>
                <xsl:value-of select="quantity"/>
            </quantityInStock>
        </item>
    </xsl:template>

    <xsl:template match="attribute">

        <!-- this error log should always be displayed -->
        <xsl:variable name="log2"
          select="bi:log('error', concat('label for lang: ', ../../@lang, ' is: ', bi:array_value(1, string(../../@lang))))"
        />

        <xsl:choose>
            <xsl:when test="name =  bi:array_value(1, string(../../@lang))">
            <category><xsl:value-of select="./value"/></category>
            </xsl:when>
            <xsl:otherwise>
                <attribute>
                    <type><xsl:value-of select="name"/></type>
                    <value><xsl:value-of select="value"/></value>
                </attribute>
            </xsl:otherwise>
        </xsl:choose>
    </xsl:template>
</xsl:stylesheet>