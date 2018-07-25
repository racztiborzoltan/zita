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

	<xsl:template match="node()|@*">
		<xsl:copy>
			<xsl:apply-templates select="node()|@*" />
		</xsl:copy>
	</xsl:template>

	<xsl:template match="link[@rel='stylesheet'][@href]">
        <xsl:copy-of select="."></xsl:copy-of>
        <xsl:if test="@href[not(starts-with(.,'http://'))]">
            <xsl:variable name="temp" select="php:function('\Zita\XsltPhpFunctionContainer::sitebuild', 'copyFile', string(@href))" />
        </xsl:if>
	</xsl:template>

	<xsl:template match="img[@src]">
        <xsl:copy-of select="."></xsl:copy-of>
        <xsl:if test="@src[not(starts-with(.,'http://'))]">
            <xsl:variable name="temp" select="php:function('\Zita\XsltPhpFunctionContainer::sitebuild', 'copyFile', string(@src))" />
        </xsl:if>
	</xsl:template>

	<xsl:template match="script[@src]">
        <xsl:copy-of select="."></xsl:copy-of>
        <xsl:if test="@src[not(starts-with(.,'http://'))]">
            <xsl:variable name="temp" select="php:function('\Zita\XsltPhpFunctionContainer::sitebuild', 'copyFile', string(@src))" />
        </xsl:if>
	</xsl:template>

</xsl:stylesheet>

