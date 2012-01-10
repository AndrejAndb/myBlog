<?xml version="1.0" encoding="UTF-8"?>

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
    <xsl:import href="base.xsl"/>
    <xsl:output method="html"/>
    
    <xsl:template match="document">
        <xsl:apply-templates/>
    </xsl:template>
    
    <xsl:template name="classAttribute">
        <xsl:if test="@class">
          <xsl:attribute name="class">
              <xsl:value-of select="@class"/>
          </xsl:attribute>
        </xsl:if>
    </xsl:template>
    
    <xsl:template name="nameHeader">
        <xsl:if test="@name">
          <xsl:element name="a">
              <xsl:attribute name="name">
                <xsl:value-of select="@name"/>
              </xsl:attribute>
          </xsl:element>
        </xsl:if>
    </xsl:template>
    
    <xsl:template name="linkToNameHeader">
        <xsl:if test="@name">
          <xsl:element name="a">
              <xsl:attribute name="href">
                #<xsl:value-of select="@name"/>
              </xsl:attribute>
              <xsl:attribute name="class">title-link-anchor</xsl:attribute>
              <xsl:text>¶</xsl:text>
          </xsl:element>
        </xsl:if>
    </xsl:template>
        
    <xsl:template match="document/example">
        <div>
            <xsl:if test="@class">
              <xsl:attribute name="class">
                  <xsl:value-of select="@class"/> example</xsl:attribute>
            </xsl:if>
            <xsl:if test="not(@class)">
              <xsl:attribute name="class">example</xsl:attribute>
            </xsl:if>

            <xsl:if test="@name">
              <xsl:element name="a">
                  <xsl:attribute name="name">
                    <xsl:value-of select="@name"/>
                  </xsl:attribute>
              </xsl:element>
            </xsl:if>

            <xsl:if test="@title">
              <xsl:element name="div">
                  <xsl:attribute name="class">example-title</xsl:attribute>
                  
                    <xsl:value-of select="@title"/>
                  
              </xsl:element>
            </xsl:if>

            <xsl:copy-of select="./*"/>
        </div>
    </xsl:template>
    
    
    

</xsl:stylesheet>
