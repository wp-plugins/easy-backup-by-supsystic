<?php
class optionsViewEbbs extends viewEbbs {

    public function getAdminPage() {
        $tabsData =  array(
            'bupMainOptions' => array(
                'title'   => __('Backup', EBBS_LANG_CODE),
                'content' => array($this, 'getMainOptionsTab'),
                'faIcon' => 'fa-home',
				'sort_order' => 10,
            )
        );
//        $tabsData = dispatcherEbbs::applyFilters('adminOptionsTabs', $tabsData);
//		uasort($tabsData, array($this, 'sortTabsClb'));
        $activeTabForCssClass = $this->getModule()->getActiveTabForCssClass($tabsData);
        $activeTab = $this->getModule()->getActiveTab();

        if(!empty($tabsData[$activeTab]['content'])) {
            $content = call_user_func_array($tabsData[$activeTab]['content'], array());
        } else {
            $content = call_user_func_array($tabsData['bupMainOptions']['content'], array());
            $activeTab = 'bupMainOptions';
        }

        $page = !empty($_GET['page']) ? $_GET['page'] : EBBS_PLUGIN_PAGE_URL_SUFFIX;
        frameEbbs::_()->addJSVar('adminOptionsEbbs', 'bupActiveTab', ($activeTab != $activeTabForCssClass) ? $activeTabForCssClass : $activeTab); // This js var used for highlighting current item submenu in admin menu
        frameEbbs::_()->addJSVar('adminOptionsEbbs', 'bupPageTitle', strip_tags($tabsData[$activeTab]['title']));
        $this->assign('tabsData', $tabsData);
        $this->assign('page', $page);
        $this->assign('activeTab', $activeTab);
        $this->assign('content', $content);
        $this->assign('activeTabForCssClass', $activeTabForCssClass);

        parent::display('optionsAdminPage');
    }

	public function sortTabsClb($a, $b) {
		if(isset($a['sort_order']) && isset($b['sort_order'])) {
			if($a['sort_order'] > $b['sort_order'])
				return 1;
			if($a['sort_order'] < $b['sort_order'])
				return -1;
		}
		return 0;
	}

    public function getMainOptionsTab() {
        $req = reqEbbs::get('get');

        if(!isset($this->optModel))
            $this->assign('optModel', $this->getModel());

		$zipNotExtMsg = frameEbbs::_()->getModule('backup')->getController()->checkExtensions();
        $zipExtExist = ($zipNotExtMsg !== true) ? 'disabled' : true;

        $dropBoxAuthButton = null;
        $dropBoxModule = frameEbbs::_()->getModule('dropbox');
        $isUserAuthenticatedInDropBox = $dropBoxModule->getController()->getModel()->isAuthenticated();
        if(!$isUserAuthenticatedInDropBox) {
            $dropBoxAuthButton = $dropBoxModule->getController()->authenticateAction();
        }

        $model   = frameEbbs::_()->getModule('backup')->getController()->getModel();
        $backups = dispatcherEbbs::applyFilters('adminGetUploadedFiles', array());
        if(!empty($backups))
            krsort($backups);

        $logs = frameEbbs::_()->getModule('log')->getModel()->getFilesList();

//        $lastBackupId = !empty($req['last_backup_id']) ? $req['last_backup_id'] : '0';
//        frameEbbs::_()->addJSVar('adminOptionsEbbs', 'lastBackupId', $lastBackupId);

        $this->assign('zipExtExist', $zipExtExist);
        $this->assign('zipNotExtMsg', $zipNotExtMsg);
        $this->assign('backupOptions', parent::getContent('backupOptions'));

        return parent::getContent('mainOptionsTab', array(
            'backups'  => $backups,
            'logs'     => $logs,
            'model'    => $model,
            'isUserAuthenticatedInDropBox' => $isUserAuthenticatedInDropBox,
            'dropBoxAuthButton' => $dropBoxAuthButton,
        ));
    }

	public function displayDeactivatePage() {
        $this->assign('GET', reqEbbs::get('get'));
        $this->assign('POST', reqEbbs::get('post'));
        $this->assign('REQUEST_METHOD', strtoupper(reqEbbs::getVar('REQUEST_METHOD', 'server')));
        $this->assign('REQUEST_URI', basename(reqEbbs::getVar('REQUEST_URI', 'server')));
        parent::display('deactivatePage');
    }
}
