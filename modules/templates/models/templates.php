<?php
class templatesModelEbbs extends modelEbbs {
    protected $_allTemplates = array();
    public function get($d = array()) {
        parent::get($d);
        if(empty($this->_allTemplates)) {
            $directories = utilsEbbs::getDirList(EBBS_TEMPLATES_DIR);
            if(!empty($directories)) {
                foreach($directories as $code => $dir) {
                    if($xml = utilsEbbs::getXml($dir['path']. 'settings.xml')) {
                        $this->_allTemplates[$code] = $xml;
                        $this->_allTemplates[$code]->prevImg = EBBS_TEMPLATES_PATH. $code. '/screenshot.png';
                    }
                }
            }
            if(is_dir( utilsEbbs::getCurrentWPThemeDir(). 'csp'. DS )) {
                if($xml = utilsEbbs::getXml( utilsEbbs::getCurrentWPThemeDir(). 'csp'. DS. 'settings.xml' )) {
                    $code = utilsEbbs::getCurrentWPThemeCode();
					if(strpos($code, '/') !== false) {	// If theme is in sub-folder
						$code = explode('/', $code);
						$code = trim( $code[count($code)-1] );
					}
                    $this->_allTemplates[$code] = $xml;
					if(is_file(utilsEbbs::getCurrentWPThemeDir(). 'screenshot.jpg'))
						$this->_allTemplates[$code]->prevImg = utilsEbbs::getCurrentWPThemePath(). '/screenshot.jpg';
					else
						$this->_allTemplates[$code]->prevImg = utilsEbbs::getCurrentWPThemePath(). '/screenshot.png';
                }
            }
        }
        if(isset($d['code']) && isset($this->_allTemplates[ $d['code'] ]))
            return $this->_allTemplates[ $d['code'] ];
        return $this->_allTemplates;
    }
}
