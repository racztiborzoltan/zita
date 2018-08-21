<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet 
    version="1.0" 
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    xmlns:php="http://php.net/xsl"
    >
    
    <xsl:template match="/">
		<xsl:variable name="temp" select="php:function('\Zita\XsltPhpFunctionContainer::sitebuild_caminar', 'copyDirectoryWithPathPrefix', 'assets', 'cache/')" />
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
		<xsl:copy>
			<xsl:apply-templates select="@*|node()"></xsl:apply-templates>
			<xsl:attribute name="href">
   				<xsl:value-of select="php:function('\Zita\XsltPhpFunctionContainer::sitebuild_caminar', 'copyFileWithPathPrefix', string(@href), 'cache/')" />
			</xsl:attribute>
		</xsl:copy>
	</xsl:template>

    <!-- 
    Copy relative image paths in <img> tags into public directory
    -->
	<xsl:template match="img[@src]">
		<xsl:copy>
			<xsl:apply-templates select="@*|node()"></xsl:apply-templates>
			<xsl:attribute name="src">
   				<xsl:value-of select="php:function('\Zita\XsltPhpFunctionContainer::sitebuild_caminar', 'copyFileWithPathPrefix', string(@src), 'cache/')" />
			</xsl:attribute>
		</xsl:copy>
	</xsl:template>

    <!-- 
    Copy relative javascript files in <script> tags into public directory
    -->
	<xsl:template match="script[@src]" priority="1">
		<xsl:copy>
			<xsl:apply-templates select="@*|node()"></xsl:apply-templates>
			<xsl:attribute name="src">
   				<xsl:value-of select="php:function('\Zita\XsltPhpFunctionContainer::sitebuild_caminar', 'copyFileWithPathPrefix', string(@src), 'cache/')" />
			</xsl:attribute>
		</xsl:copy>
	</xsl:template>

    <!--  
    remove twitter icon
    -->
	<xsl:template match="//ul/li[a[contains(concat(' ',normalize-space(@class),' '), ' fa-twitter ')]]" priority="10">
	   <!-- remove equivalent to doing nothing -->
	</xsl:template>
	
	<!--  
	Set href of social links
	-->
	<xsl:template match="//footer[@id='footer']//ul[contains(concat(' ', normalize-space(@class), ' '), ' icons ')]/li">
        <xsl:copy>
	        <xsl:choose>
	            <xsl:when test="a[contains(concat(' ',normalize-space(@class),' '),' fa-facebook ')]">
			        <xsl:attribute name="href">
			            <xsl:value-of select="'https://www.facebook.com/facebook'"></xsl:value-of>
			        </xsl:attribute>
	            </xsl:when>
	            <xsl:when test="a[contains(concat(' ',normalize-space(@class),' '),' fa-instagram ')]">
			        <xsl:attribute name="href">
			            <xsl:value-of select="'https://www.instagram.com'"></xsl:value-of>
			        </xsl:attribute>
	            </xsl:when>
	            <xsl:when test="a[contains(concat(' ',normalize-space(@class),' '),' fa-envelope-o ')]">
			        <xsl:attribute name="href">
			            <xsl:value-of select="'mailto:email@domain.com'"></xsl:value-of>
			        </xsl:attribute>
	            </xsl:when>
	        </xsl:choose>
            <xsl:apply-templates select="node()|@*" />
        </xsl:copy>
	</xsl:template>
	
	<!--  
	change copyright
	-->
	<xsl:template match="footer[@id='footer']//div[contains(concat(' ', normalize-space(@class) ,' '), ' copyright ')]">
        <xsl:copy>
            <xsl:apply-templates select="node()|@*"></xsl:apply-templates>
            <span> - Modified by XSLT</span>
        </xsl:copy>
	</xsl:template>

</xsl:stylesheet>
