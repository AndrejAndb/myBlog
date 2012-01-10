<?php
namespace myBlog\Xslt;

class Convert {
    const SITE = 'SITE';
    const RSS = 'RSS';
    
    protected $locator;
    
    public function setLocator(\Zend\Di\Locator $locator)
    {
        $this->locator = $locator;
    }

    public function getLocator()
    {
        return $this->locator;
    }
    
    protected $xsl;
    protected $_cached;
    
    public function __construct() {
        $this->xsl = array(
            self::SITE => realpath(__DIR__).'/site.xsl',
            self::RSS => realpath(__DIR__).'/rss.xsl',
        );
    }
    
    public function getSite(\DOMDocument $xml) {
        return $this->convert($xml, self::SITE);
    }
    public function getRss(\DOMDocument $xml) {
        return $this->convert($xml, self::RSS);
    }
    
    protected function convert(\DOMDocument $xml, $type) {
        $xslt = $this->xsl[self::SITE];
        if (isset($this->xsl[$type])) {
            $xslt = $this->xsl[$type];
        }

        $xslP = $this->getXSLTProcessor($xslt);
        return $xslP->transformToXML($xml);

    }
    
    protected function getXSLTProcessor($xslFile) {
        
        if(!isset($this->_cached[$xslFile])) {
            $xslDoc = new \DOMDocument();
            $xslDoc->load($xslFile);

            $xslP = new \XSLTProcessor();
            $xslP->registerPHPFunctions();
            $xslP->importStyleSheet($xslDoc);
            $this->_cached[$xslFile] = $xslP;
        }
        
        return $this->_cached[$xslFile];
    }
    
    
    public static function pygmentPHP($str, $code, $name) {
        
        
        $code = 'html+php';
        
        switch($code) {
            case 'php':
            case 'html+php':
                $code = 'html+php';
                break;
        }
        
        return self::pygmentize($str, $code, 'colorful', $name);
    }
    
    public static function pygmentize( $code, $language, $style="default", $aname ='', $tabwidth=4, $extra_opts="" ) {

	// Create a temporary file.  This is easier than dealing with PHP's cranky STDOUT handling:
	$temp_name = tempnam( "/tmp", "sourcesc_" );
        
	$highlight_class = basename( $temp_name );

	$file_handle = fopen( $temp_name, "w" );
	fwrite( $file_handle, $code );
	fclose( $file_handle );
        
        if(strlen($aname) == 0) {
            $aname = $highlight_class;
        }

	// Add the "full" and style options:
	if ( $extra_opts == "" ) {
		$extra_opts = "-O linenos,lineanchors=$aname,anchorlinenos,full,style=".$style.",cssclass=".$highlight_class;
	} else {
		// Just append these to the passed-in args:
		$extra_opts .= ",full,style=".$style.",cssclass=".$highlight_class;
	}

	// Color it.  We depend on pygmentize being in the PATH (prolly in /usr/bin/):
	$command = "pygmentize -f html $extra_opts -l $language $temp_name";
	$output = array();
	$retval = -1;

	exec( $command, $output, $retval );
        //var_dump($command);
	unlink( $temp_name );

	$output_string = join( "\n", $output );

	$original_output_string = join( "\n", $output );
	// Manually wrap tabs in a "tabspan" class, so the tab width can be set to 4
	$output_string = str_replace( "\t", "<span class='tabspan'>\t</span>", $output_string );

	// We use the Pygments "full" option, so that we don't need to manage separate 
	// CSS files (and links) for  every possible value of "style".
	// However, "full" exports a full HTML doctype, <title>, <body>, etc. which we don't
	// want when embedding into another PHP document.  So we manually remove that stuff here.

	// Replace everything up to (and including) the first <style> tag:
	if ( strpos( $output_string, '<style type="text/css">' ) != FALSE ) {
		$header_ending_position = strpos( $output_string, '<style type="text/css">' ) + strlen( '<style type="text/css">' );
		$output_string = substr( $output_string, $header_ending_position );
	}
	// We also prepend extra CSS info for the tab width and line numbering here.
	$output_string = <<<EOD
<style type="text/css">



$output_string
EOD;

	// Remove these other unneeded tags.  (The <h2></h2> is empty because we didn't supply a "title".)
	// Note that other tags (like <html> and <head> were removed above, with the first <style> tag.
	$html_tags_to_remove = array ( "</head>", "<body>", "<h2></h2>", "</body>", "</html>" );
	foreach( $html_tags_to_remove as $tag ) {
		$output_string = str_replace( $tag, "", $output_string );		
	}

	// A quirk of pygmentize is that, if you use the "full" option to get CSS and HTML at once,
	// it does not honor the -a option to set the style class.  Instead, it applies the CSS to the 
	// "body" elements.  So here we manually replace those "body" elements with the name of the wrapper class.
	// We only replace "body" up to </style>, so as not to affect the highlighted code.
	// We also make the hardcoded class "linenodiv" match the code style.
	if ( strpos( $output_string, '</style>' ) != FALSE ) {
		$css_ending_position = strpos( $output_string, '</style>' ) + strlen( '</style>' );
		$css_header_part = substr( $output_string, 0, $css_ending_position );
		$html_tail_part = substr( $output_string, $css_ending_position );

		// Note, unlike "body", CSS class names have a prepended period.  
		$css_header_part = str_replace( "body", '.'.$highlight_class, $css_header_part );

		// I prefer a narrow gap in the line numbers.  I replace the 10 with 1:
		$css_header_part = str_replace( "padding-right: 10px;", 'padding-right: 1px;', $css_header_part );

		// Make the linenodiv match the style:
		$html_tail_part = str_replace( '<div class="linenodiv">', '<div class="linenodiv '.$highlight_class.'">', $html_tail_part );
		// Make the linenodiv match the style:
		$html_tail_part = str_replace( '<td class="linenos">', '<td class="linenos '.$highlight_class.'">', $html_tail_part );

		$output_string = $css_header_part . $html_tail_part;
	}
//echo $output_string;

	return $output_string;	
        
    }
    
}
