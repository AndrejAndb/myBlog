<?xml version="1.0" encoding="UTF-8"?>

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
    <xsl:import href="base.xsl"/>
    <xsl:output method="html"/>
    
    <xsl:template match="document">
        <xsl:apply-templates/>
    </xsl:template>
    
        
    <xsl:template match="document/example">
        <p>
        Пример:
          <xsl:element name="a">
              <xsl:attribute name="href"><xsl:value-of select="../@link"/><xsl:if test="@name">#<xsl:value-of select="@name"/></xsl:if></xsl:attribute>
            <xsl:if test="@title">
              <xsl:value-of select="@title"/>
            </xsl:if>
            <xsl:if test="not(@title)">смотреть на сайте</xsl:if>
          </xsl:element>
        </p>
    </xsl:template>
    
    
    

</xsl:stylesheet>