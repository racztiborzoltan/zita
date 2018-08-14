<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet 
    version="1.0" 
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    xmlns:php="http://php.net/xsl"
    >
    
    <xsl:template match="/">
		<xsl:variable name="temp" select="php:function('\Zita\XsltPhpFunctionContainer::sitebuild', 'copyDirectory', 'assets')" />
		<xsl:apply-templates></xsl:apply-templates>
    </xsl:template>

    <!--  
    copy all nodes and attribute recursively
    -->
	<xsl:template match="node()|@*">
		<xsl:copy>
			<xsl:apply-templates select="node()|@*" />
		</xsl:copy>
	</xsl:template>

    <!--  
    copy relative file paths in <link> tags into public directory
    -->
	<xsl:template match="link[@rel='stylesheet'][@href]">
        <xsl:copy-of select="."></xsl:copy-of>
        <xsl:variable name="temp" select="php:function('\Zita\XsltPhpFunctionContainer::sitebuild', 'copyFile', string(@href))" />
	</xsl:template>

    <!-- 
    Copy relative image paths in <img> tags into public directory
    -->
	<xsl:template match="img[@src]">
        <xsl:copy-of select="."></xsl:copy-of>
        <xsl:variable name="temp" select="php:function('\Zita\XsltPhpFunctionContainer::sitebuild', 'copyFile', string(@src))" />
	</xsl:template>

    <!-- 
    Copy relative javascript files in <script> tags into public directory
    -->
	<xsl:template match="script[@src]" priority="1">
		<xsl:copy>
			<xsl:apply-templates select="@*|node()"></xsl:apply-templates>
			<xsl:attribute name="src">
   				<xsl:value-of select="php:function('\Zita\XsltPhpFunctionContainer::sitebuild', 'copyFileWithPathPrefix', string(@src), 'cache/')" />
			</xsl:attribute>
		</xsl:copy>
	</xsl:template>
	
	<xsl:template match="a[contains(concat(' ',normalize-space(@class),' '),' fa-facebook ')]">
        <xsl:copy>
	        <xsl:attribute name="href">
	            <xsl:value-of select="'https://www.facebook.com/facebook'"></xsl:value-of>
	        </xsl:attribute>
	        <xsl:apply-templates/>
        </xsl:copy>
	</xsl:template>
	
	<xsl:template match="a[contains(concat(' ',normalize-space(@class),' '),' fa-twitter ')]">
	   <xsl:call-template name="remove"></xsl:call-template>
	</xsl:template>
	
	<xsl:template name="remove">
	</xsl:template>

</xsl:stylesheet>
