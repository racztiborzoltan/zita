<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" 
xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:output method="xml" encoding="utf-8" indent="no" />
	
	<xsl:template match="/">
	   <test>
	        <php_function>
	            <xsl:value-of select="php:function('strtoupper', 'FooBar')"/>
	        </php_function>
	        <foo>
	            <xsl:value-of select="this:container('foo')"/>
	        </foo>
	        <datetime>
	            <xsl:value-of select="this:container('datetime', 'format', 'Y-m-d')"/>
	        </datetime>
	   </test>
	</xsl:template>
</xsl:stylesheet>