<?php

/**
 * Class backupModelEbbs
 * Filesystem & Database facade
 * Database and filesystem models need to implement some interface, but i think
 * in here it is not critical
 */
class backupModelEbbs extends modelEbbs {

    /**
     * @var filesystemModelEbbs
     */
    protected $filesystem;

    /**
     * @var databaseModelEbbs
     */
    protected $database;

    /**
     * @var backupTechLogModelEbbs
     */

    protected $techLog;
    /**
     * @var backupLogTxtModelEbbs
     */
    protected $logTxt;

    /**
     * @var array
     */
    protected $config;

    /**
     * @var string
     */
    protected $warehouseError;

    /**
     * @var int
     */
    public $id;

    /**
     * @var int
     */
    protected  $maxExecutionTime;

    /**
     * @var int
     */
    protected  $startTime;

    /**
     * @var int For how many seconds before the end of the allotted time to complete the work
     */
    protected  $timeDeadLine = 15;

    /**
     * Constructor
     */
    public function __construct()
    {
        /** @var backupEbbs $backup */
        $backup = frameEbbs::_()->getModule('backup');
        /** @var backupControllerEbbs $controller */
        $controller = $backup->getController();
        $this->maxExecutionTime = (int)ini_get('max_execution_time');
        $this->startTime = time();

        if ($this->filesystem === null) {
            $this->filesystem = $controller->getModel('filesystem');
        }

        if ($this->database === null) {
            $this->database = $controller->getModel('database');
        }

        if ($this->techLog === null) {
            $this->techLog = $controller->getModel('backupTechLog');
        }

        if ($this->logTxt === null) {
            $this->logTxt = $controller->getModel('backupLogTxt');
        }

        /* Set configuration array in the database model. For backward compatibility */
        $this->database->setConfig($this->getConfig());
    }

    public function createBackup(array $post) {
        $this->techLog->setLogName(basename($post['backupId']));
        $this->logTxt->setLogName(basename($post['backupId']));
        $this->logTxt->add(__(sprintf('Create backup filesystem in folder: %s', $post['backupId']), EBBS_LANG_CODE));

        while(!$this->isTimeOver() && $stack = $this->getNextStack(basename($post['backupId']))) {
            $createdStack = $this->filesystem->getTemporaryArchive($stack, $post['backupId']);
            $this->logTxt->add(__(sprintf('Successful created stack: %s', $createdStack), EBBS_LANG_CODE));
        }

        $stacks = $this->techLog->get('stacks');

        if(!empty($stacks)){
            $data = array(
                'page' => 'backup',
                'action' => 'createBackupAction',
                'backupId' => $post['backupId']
            );

            $this->sendSelfRequest($data);
            exit;
        }

        $this->techLog->set('backupFolderSize', $this->getBackupFolderSize($post['backupId']));

        $this->logTxt->add(__('Filesystem backup complete', EBBS_DB_PREF));

        $uploadingList = $this->techLog->get('uploadingList');
        $destination = $this->techLog->get('destination');

        if($destination !== 'ftp' && !empty($uploadingList)) {
            $handlers = $this->getDestinationHandlers();

            if (array_key_exists($destination, $handlers)) {
                $handler = $handlers[$destination];

                if(!$this->techLog->get('addedCloudHeader')) {
                    $this->logTxt->add(__(sprintf('Upload to the "%s" required', ucfirst($destination)), EBBS_LANG_CODE));
                    $this->techLog->set('addedCloudHeader', 1);
                    $filesToCloud = array();

                    foreach($uploadingList as $key => $file){
                        if(is_dir($file)){
                            $files = glob($file . DS . 'BUP*.zip');
                            if(!empty($files)){
                                foreach($files as $file){
                                    $filesToCloud[] = $file;
                                }
                            }
                        } elseif(is_file($file)) {
                            $filesToCloud[] = $file;
                        }
                    }

                    $uploadingList = $filesToCloud;
                    $this->techLog->set('uploadingList', $uploadingList);
                    $this->techLog->set('filesToCloudCount', count($uploadingList));
                }

                $filesToCloudCount = $this->techLog->get('filesToCloudCount');
                $this->techLog->set('backupMessage', __(sprintf('Uploading to "%s"', ucfirst($destination)), EBBS_LANG_CODE));

                foreach($uploadingList as $key => $fileToCloud){
                    if(is_file($fileToCloud)){
                        $extension = pathinfo($fileToCloud, PATHINFO_EXTENSION);
                        $stackFolder = ($extension === 'zip') ? (basename($post['backupId'])) . '/' : '';
                        $result  = call_user_func_array(
                            $handler, array(
                                array($fileToCloud),
                                $stackFolder
                            )
                        );

//                        file_put_contents('C:\WebServer\www\wp.test\htdocs\wp-content\easy-backup-storage\1.txt', var_export($result, true));

                        if ($result === true || $result == 200 || $result == 201) {
                            $this->logTxt->add(__(sprintf('Successfully uploaded to the "%s": %s', ucfirst($destination), $fileToCloud), EBBS_LANG_CODE));

                            unset($uploadingList[$key]);
                            $this->techLog->set('uploadingList', $uploadingList);

                            $this->techLog->set(
                                'uploadedFilesCount',
                                abs($filesToCloudCount - count($uploadingList))
                            );
                            $uploadedPercent =  (int)abs((count($uploadingList) - $filesToCloudCount) / $filesToCloudCount * 100);
                            $this->techLog->set('uploadedPercent', $uploadedPercent);
                            $this->techLog->set('backupMessage', __(sprintf('Uploading to "%s" %s %s', ucfirst($destination), $uploadedPercent, "%"), EBBS_LANG_CODE));
                        } else {
                            switch ($result) {
                                case 401:
                                    $error = __('Authentication required.', EBBS_LANG_CODE);
                                    break;
                                case 404:
                                    $error = __('File not found', EBBS_LANG_CODE);
                                    break;
                                case 500:
                                    $error = is_object($handler[0]) ? $handler[0]->getErrors() : __('Unexpected error (500)', EBBS_LANG_CODE);
                                    break;
                                default:
                                    $error = __('Unexpected error', EBBS_LANG_CODE);
                            }

                            //todo:if error occurred -  need call method, which will be delete uploaded files from cloud, because backup data is not full. or try to upload file again

                            $this->logTxt->add(__(
                                sprintf(
                                    'Cannot upload to the "%s": %s',
                                    ucfirst($destination),
                                    is_array($error) ? array_pop($error) : $error
                                )
                                , EBBS_LANG_CODE));
                        }

                    }
                }

                if(!empty($uploadedPercent) && $uploadedPercent == 100){
                    $this->remove(basename($post['backupId']));
                    $this->remove(basename($post['backupId']) . '.sql');
                }


            }
        }

        $this->techLog->set('complete', true);
        $this->logTxt->add(__('Backup complete', EBBS_DB_PREF));
        $this->logTxt->saveBackupDirSetting(array('backupFolderSize' => $this->techLog->get('backupFolderSize')));
    }

    public function getNextStack($backupId) {
        $this->techLog->setLogName($backupId);
        $stackFileList = $this->techLog->get('stacks');

        if (empty($stackFileList)){
            return false;
        } else {
            $stack = array_shift($stackFileList);
            $this->techLog->set('stacks', !empty($stackFileList) ? $stackFileList : null);
            return $stack;
        }
    }

    public function isTimeOver() {
        if($this->maxExecutionTime === 0)
            return false;

        return (time() - $this->startTime + $this->timeDeadLine > $this->maxExecutionTime);
    }

    public function sendSelfRequest(array $data) {
        $data['auth'] = AUTH_KEY;
        $data['pl'] = 'ebbs';
        $url = get_option('siteurl');
        $pu = parse_url($url);
        $host = $pu['host'];

        if (function_exists('fsockopen')) {
            $content = http_build_query($data);

            $fp = @fsockopen($host, 80, $errno, $errstr, 30);

            if ($fp) {
                fwrite($fp, "POST / HTTP/1.1\r\n");
                fwrite($fp, "Host: " . $host . "\r\n");
                fwrite($fp, "Content-Type: application/x-www-form-urlencoded\r\n");
                fwrite($fp, "Content-Length: ".strlen($content)."\r\n");
                fwrite($fp, "Connection: close\r\n");
                fwrite($fp, "\r\n");
                fwrite($fp, $content);
//                    @sleep(1);
                header('Content-type: text/plain');
                fclose($fp);
            } else {
                $curl = curl_init($url . "/");
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($curl, CURLOPT_POST, true);
                curl_setopt($curl, CURLOPT_POSTFIELDS, $content);
                curl_setopt($curl, CURLOPT_TIMEOUT, 10);
                $res = curl_exec($curl);
                curl_close($curl);
            }
        }
    }

    public function getBackupFolderSize($dir){
        $countSize = 0;
        $dirArray = glob($dir . DS . '*.zip');

        foreach($dirArray as $key => $filename) {
            if(file_exists($filename) && is_file($filename))
                $countSize += filesize($filename);
        }

        return $countSize;
    }

    /**
     * Generate filename with specified extensions
     * @param array $extensions An array of required extensions
     * @return array An associative array with filename
     */
    public function generateFilename(array $extensions)
    {
        $pattern = 'backup_{datetime}_id{id}.{extension}';
        $search = array('{datetime}', '{id}', '{extension}');
        $replace = array(date('Y_m_d-H_i_s'), $this->getId());
        $warehouse = frameEbbs::_()->getModule('warehouse')->getPath();
        $names = array();

        foreach ($extensions as $extension) {
            $replace = array_merge($replace, array('{extension}' => $extension));
            $names[$extension] = rtrim($warehouse, '/') . '/' . str_replace($search, $replace, $pattern);
        }

        $pattern = 'backup_{datetime}_id{id}';
        $names['folder'] = rtrim($warehouse, '/') . '/' . str_replace($search, $replace, $pattern);

        return $names;
    }

    /**
     * Get next identifier
     * @return int|mixed
     */
    public function getId()
    {
        if ($this->id === null) {
            $files = scandir($this->getConfig('warehouse'));

            $matches = array();
            $results = array();

            foreach($files as $file) {
                if(preg_match('/id([\d]+)/', $file, $matches)) {
                    $results[] = $matches[1];
                }
            }

            if(!empty($results)) {
                $this->id = max($results) + 1;
            }
            else {
                $this->id = 1;
            }
        }

        return $this->id;
    }
    /**
     * Return all founded backups
     * @return null|array
     */
    public function getBackupsList() {
        $config  = $this->getConfig();
        $pattern = '/(backup_([0-9_-]*)_id([0-9]+))\.(zip|sql)/ui';
        $backups = array();

        $dir = @scandir($config['warehouse']);

        if (!is_array($dir) || empty($dir)) {
            return array();
        }


        foreach ($dir as $file) {
            $backupInfo = $this->getBackupInfoByFilename($file);

            if (!empty($backupInfo)) {
                $extension = !empty($backupInfo['ext']) ? $backupInfo['ext'] : 'zip';
                $backups[$backupInfo['id']]['ftp'][strtolower($extension)] = array(
                    'id'   => $backupInfo['id'],
                    'name' => $backupInfo['name'],
                    'raw'  => $backupInfo['raw'],
                    'ext'  => $extension,
                    'date' => $backupInfo['date'],
                    'time' => $backupInfo['time'],
                    'size' => is_file($config['warehouse'] . $file) ? filesize($config['warehouse'] . $file) : null,
                );
                $backups[$backupInfo['id']]['ftp'][strtolower($extension)] = dispatcherEbbs::applyFilters('addInfoIfEncryptedDb', $backups[$backupInfo['id']]['ftp'][strtolower($extension)]);
            }
        }
        krsort($backups);
        return $backups;
    }

    /**
     * Remove backup
     * @param string $filename File name of the backup
     * @return bool TRUE if file exists and successfully removed, FALSE otherwise.
     */
    public function remove($filename)
    {
        if (file_exists($file = $this->getConfig('warehouse') . $filename)) {
            if (is_file($file) && unlink($file)) {
                return true;
            } elseif (is_dir($file)) {
                $this->filesystem->deleteLocalBackup(array($file));
                return true;
            }

            return false;
        }

        return false;
    }

    /**
     * Restore from backup
     * @param string $filename
     * @return bool
     */
    public function restore($filename)
    {
        if (file_exists($file = $this->getConfig('warehouse') . $filename)) {

            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

            if ($ext === 'sql') {
                return $this->database->restore($file);
            } elseif (!$ext) {
                return $this->filesystem->restore($file);
            }

            return false;
        }

        return false;
    }

    /**
     * Is database backup required?
     * @return bool TRUE if required, FALSE otherwise.
     */
    public function isDatabaseRequired()
    {
        return $this->isSomethingRequired(array(
            'full_backup', 'database'
        ));
    }

    /**
     * Is filesystem backup required?
     * @return bool TRUE if require, FALSE otherwise.
     */
    public function isFilesystemRequired()
    {
        return $this->isSomethingRequired(array(
            'full_backup', 'any_dir', 'plugins_dir', 'themes_dir', 'uploads_dir', 'wp_core'
        ));
    }

    /**
     * Returns an array of excluded directories
     * @return array
     */
    public function getExcludedDirectories()
    {
        $directories = $this->getConfig('exclude');

        if (empty($directories)) {
            return array();
        }

        return $this->filesystem->splitExcludedDirectories($directories);
    }

    /**
     * Set excluded directories
     * @param string|array $directories Comma-separated string or an array of directories
     * @param bool $replace If TRUE then specified directories will replace current, FALSE - append to the end.
     */
    public function setExcludedDirectories($directories, $replace = false)
    {
        /** @var optionsEbbs $options */
        $options = frameEbbs::_()->getModule('options');

        if (is_array($directories)) {
            $directories = implode(',', $directories);
        }

        if (!$replace) {
            $exclude = $this->getConfig('exclude');

            $directories = implode(',', array(rtrim($exclude, ','), $directories));
        }

        $this->config['exclude'] = $directories;
    }

    /**
     * Is specified directory is in excluded list?
     * @param string $directory Name of the directory
     * @return bool TRUE if it in the list, FALSE otherwise.
     */
    public function isExcluded($directory)
    {
        $excluded = $this->getConfig('exclude');

        foreach ($this->filesystem->splitExcludedDirectories($excluded) as $excludedDirectory) {
            if (strtolower($excludedDirectory) === strtolower($directory)) {
                return true;
            }
        }

        return false;
    }

    public function getFilesList($optionsModel = false)
    {
        $excluded = $this->getDefaultExcludedFolders();
        if(!$optionsModel)
            $options  = frameEbbs::_()->getModule('options');
        else
            $options  = $optionsModel;

        $directory = trailingslashit(realpath(ABSPATH)) . EBBS_WP_CONTENT_DIR;

        // Default folders inside wp-content
        $defaults = array('themes', 'plugins', 'uploads');

        // Excluded folder by user.
        $dbexcluded = $options->get('exclude');

        // Folder that contains backups.
        $warehouseDir = $options->get('warehouse_ignore');

        $excluded = array_merge(
            $excluded,
            array_map('trim', explode(',', $dbexcluded))
        );

        // Exclude plugin's "warehouse".
        if (!in_array($warehouseDir, $excluded)) {
            $excluded[] = $warehouseDir;
        }

        // If any directory inside "wp-content" isn't selected, then exclude all nodes from it.
        if (0 == $options->get('any_directories')) {
            $nodes = glob(untrailingslashit(WP_CONTENT_DIR) . '/*');

            if (is_array($nodes) && !empty($nodes)) {
                foreach ($nodes as $node) {
                    if (!in_array($nodeName = basename($node), $defaults)) {
                        $excluded[] = $nodeName;
                    }
                }
            }
        }

        // What about plugins, themes and uploads?
        if (0 == $options->get('plugins')) {
            $excluded[] = 'plugins';
        }

        if (0 == $options->get('themes')) {
            $excluded[] = 'themes';
        }

        if (0 == $options->get('uploads')) {
            $excluded[] = 'uploads';
        }

        $fileList = $this->filesystem->getFilesList($directory, $excluded);

        if(1 == $options->get('wp_core')){
            $directory = realpath(ABSPATH);
            $excluded = $this->getDefaultExcludedFolders();

            $excluded = array_merge(
                $excluded,
                array_map('trim', explode(',', $dbexcluded))
            );

            if(is_array($excluded))
                $excluded[] = EBBS_WP_CONTENT_DIR;
            else
                $excluded = array(EBBS_WP_CONTENT_DIR);

            $wpCoreFileList = $this->filesystem->getFilesList($directory, $excluded);
            $fileList = array_merge($fileList,  $wpCoreFileList);
        }

        $files = array();
        $files[0] = array();
        $i = 0;
        $size = 0;
        $stackSize = 2097152;

        foreach($fileList as $f) {
            $filePath = ABSPATH . $f;
            $f_size = @filesize($filePath);
            $files[$i][] = $f;
            $size += $f_size;

            if ($size > $stackSize) {
                $i ++;
                $size = 0;
                $files[$i] = array();
            }
        }

        unset($fileList);

        return $files;
    }

    public function getDefaultExcludedFolders()
    {
        return array(EBBS_PLUG_NAME, 'wpadm_backups', 'wpadm_backup', 'easy-backup-storage', '.idea', '.git', '.svn', 'upsupsystic', 'nbproject');
    }

    /**
     * Get the database model
     * @return databaseModelEbbs
     */
    public function getDatabase()
    {
        return $this->database;
    }

    /**
     * Get the filesystem model
     * @return filesystemModelEbbs
     */
    public function getFilesystem()
    {
        return $this->filesystem;
    }

    public function getDestinationHandlers() {
        $handlers = array();
        $handlers = dispatcherEbbs::applyFilters('adminBackupUpload', $handlers);

        return $handlers;
    }

    /**
     * Get backup configurations
     * @param string|array $item The keys to get from config
     * @return mixed
     */
    public function getConfig($item = null)
    {
        if ((!is_array($this->config) || empty($this->config)) || count($this->config) < 1) {

            /** @var optionsEbbs $options */
            $options = frameEbbs::getInstance()->getModule('options');

            $this->config = array(
                'full_backup'   => (bool)$options->get('full'),
                'wp_core'       => (bool)$options->get('wp_core'),
                'plugins_dir'   => (bool)$options->get('plugins'),
                'themes_dir'    => (bool)$options->get('themes'),
                'uploads_dir'   => (bool)$options->get('uploads'),
                'database'      => (bool)$options->get('database'),
                'any_dir'       => (bool)$options->get('any_directories'),
                'exclude'       => $options->get('exclude'),
                'warehouse'     => frameEbbs::_()->getModule('warehouse')->getPath() . DIRECTORY_SEPARATOR,
                'dest'          => $options->get('glb_dest'),
                // Since 2.0
                'force_update'  => (bool)$options->get('force_update'),
                'safe_update'   => (bool)$options->get('safe_update'),
                'replace_newer' => (bool)$options->get('replace_newer'),
            );
        }

        if ($item === null) {
            return $this->config;
        }

        if (is_string($item)) {
            return (isset($this->config[$item]) ? $this->config[$item] : null);
        }

        if (is_array($item)) {
            $config = array();

            foreach ($item as $key) {
                if (isset($this->config[$key])) {
                    $config[$key] = $this->config[$key];
                }
            }

            return $config;
        }

        return null;
    }

    public function checkWarehouse()
    {
        if (!frameEbbs::_()->getModule('warehouse')->getWarehouseStatus()) {
            $this->warehouseError = 'Can\'t create warehouse directory or it\'s not writable.';

            return false;
        }

        return true;
    }

    public function getWarehouseError()
    {
        return $this->warehouseError;
    }

    /**
     * Is some backup type is required or not
     * @param array $keys An array of keys to check
     * @return bool TRUE if one of the keys is TRUE, FALSE otherwise.
     */
    protected function isSomethingRequired(array $keys)
    {
        foreach ($keys as $key) {
            $value = $this->getConfig($key);
            if (null !== $value) {
                if ((bool)$value) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * This function return backup start date and time
     * @param string $logContent
     * @return string
     */
    public function getBackupStartTimeFromLog($logContent)
    {
        $dateTime = null;
        if(is_string($logContent)){
            $logFileArray = explode(']', $logContent);
            foreach($logFileArray as $logString) {
                if(false !== $datePos = strpos($logString, '[')) {
                    $dateTime = substr($logString, $datePos + 1);
                    break;
                }
            }
            return $dateTime;
        } else {
            return false;
        }
    }

    /**
     * This function return backup finish date and time
     * @param string $logContent
     * @return string
     */
    public function getBackupFinishTimeFromLog($logContent)
    {
        if(is_string($logContent)){
            $logFileArray = explode('[', $logContent);
            $content = array_pop($logFileArray);
            $dateTime = substr($content, 0, strpos($content, ']'));
            return ($dateTime) ? $dateTime : false;
        } else {
            return false;
        }
    }

    public function checkCloudServiceRemoteServerIsAuth($destination)
    {
        $isAuthorized = false;
        $handlers = dispatcherEbbs::applyFilters('adminBackupUpload', array());
        /* @var modelEbbs $handlerModel*/
        $handlerModel = !empty($handlers[$destination][0]) ? $handlers[$destination][0] : null;

        if(is_a($handlerModel, 'modelEbbs')) {
            $isAuthorized = $handlerModel->isUserAuthorizedInService($destination);
            if (!$isAuthorized)
                $this->pushError($handlerModel->getErrors());
        } else {
            $this->pushError(__('Unexpected error.', EBBS_LANG_CODE));
        }

        return $isAuthorized;
    }

    public function getBackupFilesListUploading(array $backupInfo)
    {
        $filesList = array();

        foreach($backupInfo as $backup) {
            if(file_exists($backup)){
                if(is_dir($backup))
                    $filesList = array_merge($filesList, glob($backup . DS . 'BUP*.zip'));
                else
                    $filesList[] = $backup;
            }
        }

        return $filesList;
    }

    public function formatBackupSize($size) {
        return is_numeric($size) ? number_format($size / 1024 / 1024 , 2, '.', ' ') . ' mB' : __('Undefined', EBBS_LANG_CODE);
    }
}
