<?php
/**
 * Class for templates module tab at options page
 */
class templatesViewEbbs extends viewEbbs {
    /**
     * Get the content for templates module tab
     * 
     * @return type 
     */
    public function getTabContent(){
       $templates = frameEbbs::_()->getModule('templatesEbbs')->getModel()->get();
       if(empty($templates)) {
           $tpl = 'noTemplates';
       } else {
           $this->assign('templatesEbbs', $templates);
           $this->assign('default_theme', frameEbbs::_()->getModule('optionsEbbs')->getModel()->get('default_theme'));
           $tpl = 'templatesTab';
       }
       return parent::getContent($tpl);
   }
}

