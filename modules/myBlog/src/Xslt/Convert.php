<?php
namespace myBlog\Xslt;

class Convert {
    const SITE = 'SITE';
    const RSS = 'RSS';
    
    protected $xsl;
    protected $baseXsl;
    
    public function __construct() {
        $this->xsl = array(
            self::SITE => realpath(__DIR__).'/site.xsl',
            self::RSS => realpath(__DIR__).'/rss.xsl',
        );
        $this->baseXsl = realpath(__DIR__).'/base.xsl';
    }
    
    public function getSite($text) {
        return $this->process($text, self::SITE);
    }
    public function getRss($text) {
        return $this->process($text, self::RSS);
    }
    
    protected function process($text, $type) {
        $xslt = $this->xsl[self::SITE];
        if (isset($this->xsl[$type])) {
            $xslt = $this->xsl[$type];
        }
        
        $xml = new \DOMDocument('1.0', 'utf-8');
        try {
            if(@$xml->loadXML($text) == false){
                throw new \Exception();
            }
            
            $xslP = $this->loadXSLTProcessor($xslt);
            
            return $xslP->transformToXML($xml);

        } catch (\Exception $e) {
            return $text;
        }
        
        return $text;
    }
    
    protected function loadXSLTProcessor($xslFile) {
        
        $xslDoc = new \DOMDocument();
        $xslDoc->load($xslFile);
        
        $xslP = new \XSLTProcessor();
        $xslP->importStyleSheet($this->baseXsl);
        $xslP->importStyleSheet($xslDoc);
        
        return $xslP;
    }
}
