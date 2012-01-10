<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:php="http://php.net/xsl" xsl:extension-element-prefixes="php" exclude-result-prefixes="xsl php" version="1.0">

    <xsl:template match="document/p">
        <p>
            <xsl:call-template name="classAttribute" />
            <xsl:apply-templates/>
        </p>
    </xsl:template>
    
    
    <xsl:template name="classAttribute">
    </xsl:template>
    
    <xsl:template name="nameHeader">
    </xsl:template>
    
    <xsl:template name="linkToNameHeader">
    </xsl:template>
    
    <xsl:template name="titleAttribute">
        <xsl:if test="@title">
          <xsl:attribute name="title">
              <xsl:value-of select="@title"/>
          </xsl:attribute>
        </xsl:if>
    </xsl:template>
    
    <xsl:template match="document/div">
        <div>
            <xsl:call-template name="titleAttribute" />
            <xsl:call-template name="classAttribute" />
            <xsl:apply-templates/>
        </div>
    </xsl:template>
    
    <xsl:template match="img">
        <img>
            <xsl:call-template name="classAttribute" />
            <xsl:call-template name="titleAttribute" />
            <xsl:if test="@src">
              <xsl:attribute name="src">
                  <xsl:value-of select="@src"/>
              </xsl:attribute>
            </xsl:if>
            <xsl:if test="@width">
              <xsl:attribute name="width">
                  <xsl:value-of select="@width"/>
              </xsl:attribute>
            </xsl:if>
            <xsl:if test="@height">
              <xsl:attribute name="height">
                  <xsl:value-of select="@height"/>
              </xsl:attribute>
            </xsl:if>
            <xsl:if test="@alt">
              <xsl:attribute name="alt">
                  <xsl:value-of select="@alt"/>
              </xsl:attribute>
            </xsl:if>
        </img>
    </xsl:template>
    
    
    <xsl:template match="a">
        <a>
            <xsl:call-template name="classAttribute" />
            <xsl:call-template name="titleAttribute" />
            <xsl:if test="@href">
              <xsl:attribute name="href">
                  <xsl:value-of select="@href"/>
              </xsl:attribute>
            </xsl:if>
            <xsl:if test="@target">
              <xsl:attribute name="target">
                  <xsl:value-of select="@target"/>
              </xsl:attribute>
            </xsl:if>
            <xsl:apply-templates/>
        </a>
    </xsl:template>
    
    <xsl:template match="document/h1">
        <h2>
            <xsl:call-template name="classAttribute" />
            <xsl:call-template name="nameHeader" />
            <xsl:apply-templates/>
            <xsl:call-template name="linkToNameHeader" />
        </h2>
    </xsl:template>
    
    <xsl:template match="document/h2">
        <h3>
            <xsl:call-template name="classAttribute" />
            <xsl:call-template name="nameHeader" />
            <xsl:apply-templates/>
            <xsl:call-template name="linkToNameHeader" />
        </h3>
    </xsl:template>
    
    <xsl:template match="document/h3">
        <h4>
            <xsl:call-template name="classAttribute" />
            <xsl:call-template name="nameHeader" />
            <xsl:apply-templates/>
            <xsl:call-template name="linkToNameHeader" />
        </h4>
    </xsl:template>
    
    <xsl:template match="document/ul">
        <ul>
            <xsl:call-template name="classAttribute" />
            <xsl:apply-templates/>
        </ul>
    </xsl:template>
    
    <xsl:template match="document/ol">
        <ol>
            <xsl:call-template name="classAttribute" />
            <xsl:apply-templates/>
        </ol>
    </xsl:template>
    
    <xsl:template match="document/ul/li|document/ol/li">
        <li>
            <xsl:call-template name="classAttribute" />
            <xsl:apply-templates/>
        </li>
    </xsl:template>

    
    <xsl:template match="b">
        <b>
            <xsl:call-template name="titleAttribute" />
            <xsl:call-template name="classAttribute" />
            <xsl:apply-templates/>
        </b>
    </xsl:template>
    
    <xsl:template match="code">
        <code>
            <xsl:call-template name="titleAttribute" />
            <xsl:call-template name="classAttribute" />
            <xsl:apply-templates/>
        </code>
    </xsl:template>
    
    <xsl:template match="i">
        <i>
            <xsl:call-template name="titleAttribute" />
            <xsl:call-template name="classAttribute" />
            <xsl:apply-templates/>
        </i>
    </xsl:template>
    
    
    <xsl:template match="sub">
        <sub>
            <xsl:call-template name="titleAttribute" />
            <xsl:call-template name="classAttribute" />
            <xsl:apply-templates/>
        </sub>
    </xsl:template>
    
    <xsl:template match="s">
        <s>
            <xsl:call-template name="titleAttribute" />
            <xsl:call-template name="classAttribute" />
            <xsl:apply-templates/>
        </s>
    </xsl:template>
    
    <xsl:template match="sup">
        <sup>
            <xsl:call-template name="titleAttribute" />
            <xsl:call-template name="classAttribute" />
            <xsl:apply-templates/>
        </sup>
    </xsl:template>
    
    <xsl:template match="abbr">
        <abbr>
            <xsl:call-template name="titleAttribute" />
            <xsl:call-template name="classAttribute" />
            <xsl:apply-templates/>
        </abbr>
    </xsl:template>
    
    <xsl:template match="nobr">
        <span>
            <xsl:attribute name="style">white-space:nowrap;</xsl:attribute>
            <xsl:apply-templates/>
        </span>
    </xsl:template>
    
    <xsl:template match="u">
        <span>
            <xsl:attribute name="style">text-decoration:underline;</xsl:attribute>
            <xsl:apply-templates/>
        </span>
    </xsl:template>
    
    <xsl:template match="anchor">
        <a>
            <xsl:attribute name="name"><xsl:value-of select="text()"/></xsl:attribute>
        </a>
    </xsl:template>
    
    <xsl:template match="document/blockquote">
        <blockquote>
            <xsl:call-template name="classAttribute" />
            <xsl:apply-templates/>
        </blockquote>
    </xsl:template>
    
    <xsl:template match="br">
        <br>
            <xsl:call-template name="classAttribute" />
        </br>
    </xsl:template>
    
    <xsl:template match="span">
        <span>
            <xsl:call-template name="titleAttribute" />
            <xsl:call-template name="classAttribute" />
            <xsl:apply-templates/>
        </span>
    </xsl:template>
    
    <xsl:template match="document/pre">
        <pre>
            <xsl:call-template name="classAttribute" />
            <xsl:apply-templates/>
        </pre>
    </xsl:template>
    
    <xsl:template match="document/hr">
        <hr>
            <xsl:call-template name="classAttribute" />
        </hr>
    </xsl:template>
    
    <xsl:template match="document/sources">
        <div class="sources-code">
            <xsl:value-of select="php:function('myBlog\Xslt\Convert::pygmentPHP', string(.), string(@code), string(@name) )" disable-output-escaping="yes" />
        </div>
    </xsl:template>

</xsl:stylesheet>
