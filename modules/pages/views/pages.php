<?php
class pagesViewEbbs extends viewEbbs {
    public function displayDeactivatePage() {
        $this->assign('GET', reqEbbs::get('get'));
        $this->assign('POST', reqEbbs::get('post'));
        $this->assign('REQUEST_METHOD', strtoupper(reqEbbs::getVar('REQUEST_METHOD', 'server')));
        $this->assign('REQUEST_URI', basename(reqEbbs::getVar('REQUEST_URI', 'server')));
        parent::display('deactivatePage');
    }
}

