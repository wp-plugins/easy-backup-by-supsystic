<?php


class backupLogModelEbbs extends modelEbbs
{

    /** Session key */
    const KEY = 'ebbs_logger';
    const EBBS_DIR_SETTINGS_KEY = 'ebbs_dir_setting';
    const CURRENT_BACKUP_FILES_NAME = 'ebbs_current_backup_files_name';
    const CURRENT_BUP_PATH_INFO = 'current_bup_path_info';

    /**
     * Write heading message
     * @param $text
     */
    public function header($text)
    {
        $separator = str_repeat('-', 50);

        $this->write(implode(PHP_EOL, array(
            $separator, $text, $separator
        )));
    }

    /**
     * Write string
     * @param $text
     */
    public function string($text)
    {
        $this->write(sprintf('[%s] %s', date('Y-m-d H:i:s'), $text));
    }

    /**
     * Clear session
     */
    public function clear()
    {
        if (isset($_SESSION[self::KEY])) {
            unset ($_SESSION[self::KEY]);
        }
        if (isset($_SESSION[self::EBBS_DIR_SETTINGS_KEY])) {
            unset ($_SESSION[self::EBBS_DIR_SETTINGS_KEY]);
        }
        if (isset($_SESSION[self::CURRENT_BACKUP_FILES_NAME])) {
            unset ($_SESSION[self::CURRENT_BACKUP_FILES_NAME]);
        }
        if (isset($_SESSION[self::CURRENT_BUP_PATH_INFO])) {
            unset ($_SESSION[self::CURRENT_BUP_PATH_INFO]);
        }
    }

    /**
     * Save to the log file
     * @param $filename
     * @return int
     */
    public function save($filename)
    {
        if(!empty($_SESSION[self::EBBS_DIR_SETTINGS_KEY])) {
            $this->string(__('Please, don\'t delete the line that is lower, it is used for technical purposes!', EBBS_LANG_CODE));
        }

        $content = $this->getContents();

        if(!empty($_SESSION[self::EBBS_DIR_SETTINGS_KEY])) {
            $content .= PHP_EOL . $_SESSION[self::EBBS_DIR_SETTINGS_KEY];
            $this->string($_SESSION[self::EBBS_DIR_SETTINGS_KEY]);
        }

        return file_put_contents($filename, $content);
    }

    public function getContents()
    {
        return implode(PHP_EOL, $_SESSION[self::KEY]);
    }

    /** Write to the session */
    protected function write($text)
    {
        $_SESSION[self::KEY][] = $text;
    }

    public function getBackupLog()
    {
        return isset($_SESSION[self::KEY]) ? $_SESSION[self::KEY] : '';
    }

    /**
     * Write to the log backup settings
     * @param array $settingsArray
     */
    public function writeBackupSettings($settingsArray){
        $text = __('Backup settings: ', EBBS_LANG_CODE);
        $settingsStringArray = array();
        if(!empty($settingsArray['full']))
            $settingsStringArray[] = __('Full backup', EBBS_LANG_CODE);

        if(!empty($settingsArray['wp_core']))
            $settingsStringArray[] = __('Wordpress Core', EBBS_LANG_CODE);

        if(!empty($settingsArray['plugins']))
            $settingsStringArray[] = __('Plugins folder', EBBS_LANG_CODE);

        if(!empty($settingsArray['themes']))
            $settingsStringArray[] = __('Themes folder', EBBS_LANG_CODE);

        if(!empty($settingsArray['uploads']))
            $settingsStringArray[] = __('Uploads folder', EBBS_LANG_CODE);

        if(!empty($settingsArray['any_directories']))
            $settingsStringArray[] = __('Any folder inside wp-content', EBBS_LANG_CODE);

        if(!empty($settingsArray['database']))
            $settingsStringArray[] = dispatcherEbbs::applyFilters('changeDBSettingStringInLog', 'Database backup');

        if(!empty($settingsArray['exclude']))
            $settingsStringArray[] = __('Exclude: ', EBBS_LANG_CODE) . $settingsArray['exclude'];

        if(!empty($settingsArray['email_ch']))
            $settingsStringArray[] = __('Email notification: ', EBBS_LANG_CODE) . $settingsArray['email'];

        $text .= implode('; ', $settingsStringArray) . '.';
        $this->string($text);
    }

    public function setCurrentBackupFilesName($filename){
        if(!empty($filename)) {
            $files = $this->getCurrentBackupFilesName();
            $files = is_array($files) ? array_merge($files, array($filename)) : array($filename);
            $_SESSION[self::CURRENT_BACKUP_FILES_NAME] = $files;
        }
    }

    public function getCurrentBackupFilesName(){
        return !empty($_SESSION[self::CURRENT_BACKUP_FILES_NAME]) ? $_SESSION[self::CURRENT_BACKUP_FILES_NAME] : null;
    }

    public function setCurrentBupInfo(array $info){
        $_SESSION[self::CURRENT_BUP_PATH_INFO] = $info;
    }

    public function getCurrentBupInfo(){
        return !empty($_SESSION[self::CURRENT_BUP_PATH_INFO]) ? $_SESSION[self::CURRENT_BUP_PATH_INFO] : false;
    }
}
