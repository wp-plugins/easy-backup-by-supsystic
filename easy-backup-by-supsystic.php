<?php
/**
 * Plugin Name: DropBox Backup by Supsystic
 * Description:  Backup to the DropBox and FTP with one click. DropBox backup restoration. Different backup types.
 * Version: 1.1
 * Author: Supsystic
 * Author URI: http://supsystic.com/
 **/

require_once(dirname(__FILE__). DIRECTORY_SEPARATOR. 'config.php');
require_once(dirname(__FILE__). DIRECTORY_SEPARATOR. 'functions.php');

importClassEbbs('dbEbbs');
importClassEbbs('installerEbbs');
importClassEbbs('baseObjectEbbs');
importClassEbbs('moduleEbbs');
importClassEbbs('modelEbbs');
importClassEbbs('viewEbbs');
importClassEbbs('controllerEbbs');
importClassEbbs('helperEbbs');
importClassEbbs('tabEbbs');
importClassEbbs('dispatcherEbbs');
importClassEbbs('fieldEbbs');
importClassEbbs('tableEbbs');
importClassEbbs('frameEbbs');
importClassEbbs('langEbbs');
importClassEbbs('reqEbbs');
importClassEbbs('uriEbbs');
importClassEbbs('htmlEbbs');
importClassEbbs('responseEbbs');
importClassEbbs('fieldAdapterEbbs');
importClassEbbs('validatorEbbs');
importClassEbbs('errorsEbbs');
importClassEbbs('utilsEbbs');
importClassEbbs('modInstallerEbbs');
importClassEbbs('wpUpdater');
importClassEbbs('toeWordpressWidgetEbbs');
importClassEbbs('installerDbUpdaterEbbs');
importClassEbbs('templateModuleEbbs');
importClassEbbs('templateViewEbbs');
importClassEbbs('fileuploaderEbbs');

installerEbbs::update();
errorsEbbs::init();

dispatcherEbbs::doAction('onBeforeRoute');
frameEbbs::_()->parseRoute();
dispatcherEbbs::doAction('onAfterRoute');

dispatcherEbbs::doAction('onBeforeInit');
frameEbbs::_()->init();
dispatcherEbbs::doAction('onAfterInit');

dispatcherEbbs::doAction('onBeforeExec');
frameEbbs::_()->exec();
dispatcherEbbs::doAction('onAfterExec');