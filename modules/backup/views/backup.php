<?php

/**
 * Backup View Controller
 * @package supsysticBackup\Modules\Backup
 * @version 2.0
 */
class backupViewEbbs extends viewEbbs {
   public function getBackupsListContent() {
       $model   = frameEbbs::_()->getModule('backup')->getController()->getModel();
       $logs = frameEbbs::_()->getModule('log')->getModel()->getFilesList();
       $backups = dispatcherEbbs::applyFilters('adminGetUploadedFiles', array());

       if(!empty($backups))
           krsort($backups);

       return parent::getContent('backupsList', array(
           'backups' => $backups,
           'model'    => $model,
           'logs'     => $logs,
       ));
   }
}