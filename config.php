<?php
    global $wpdb;
    if (!defined('WPLANG') || WPLANG == '') {
        define('EBBS_WPLANG', 'en_GB');
    } else {
        define('EBBS_WPLANG', WPLANG);
    }
    if(!defined('DS')) define('DS', DIRECTORY_SEPARATOR);


    $wpContentArray = explode('/', content_url());
    if(count($wpContentArray) < 2)
        $wpContentArray = explode('\\', content_url());
    $wpContentFolder = array_pop($wpContentArray);
    define('EBBS_WP_CONTENT_DIR', $wpContentFolder);

    define('EBBS_PLUG_NAME', basename(dirname(__FILE__)));
	define('EBBS_PLUG_NAME_PRO', 'backup-supsystic-pro');
    define('EBBS_DIR', WP_PLUGIN_DIR. DS. EBBS_PLUG_NAME. DS);
    define('EBBS_TPL_DIR', EBBS_DIR. 'tpl'. DS);
    define('EBBS_CLASSES_DIR', EBBS_DIR. 'classes'. DS);
    define('EBBS_TABLES_DIR', EBBS_CLASSES_DIR. 'tables'. DS);
	define('EBBS_HELPERS_DIR', EBBS_CLASSES_DIR. 'helpers'. DS);
	define('EBBS_GLIB_DIR', EBBS_HELPERS_DIR. 'googlelib'. DS);
	define('EBBS_TOKEN_DIR', EBBS_HELPERS_DIR. 'tokens'. DS);
    define('EBBS_LANG_DIR', EBBS_DIR. 'lang'. DS);
    define('EBBS_IMG_DIR', EBBS_DIR. 'img'. DS);
    define('EBBS_TEMPLATES_DIR', EBBS_DIR. 'templates'. DS);
    define('EBBS_MODULES_DIR', EBBS_DIR. 'modules'. DS);
    define('EBBS_FILES_DIR', EBBS_DIR. 'files'. DS);
    define('EBBS_ADMIN_DIR', ABSPATH. 'wp-admin'. DS);
	define('EBBS_S_WP_PLUGIN_NAME', 'Easy backup by Supsystic');


    define('EBBS_SITE_URL', get_bloginfo('wpurl'). '/');
    define('EBBS_JS_PATH', WP_PLUGIN_URL.'/'.basename(dirname(__FILE__)).'/js/');
    define('EBBS_CSS_PATH', WP_PLUGIN_URL.'/'.basename(dirname(__FILE__)).'/css/');
    define('EBBS_IMG_PATH', WP_PLUGIN_URL.'/'.basename(dirname(__FILE__)).'/img/');
    define('EBBS_MODULES_PATH', WP_PLUGIN_URL.'/'.basename(dirname(__FILE__)).'/modules/');
    define('EBBS_TEMPLATES_PATH', WP_PLUGIN_URL.'/'.basename(dirname(__FILE__)).'/templates/');
    define('EBBS_IMG_POSTS_PATH', EBBS_IMG_PATH. 'posts/');
    define('EBBS_JS_DIR', EBBS_DIR. 'js/');
    define('EBBS_PLUGIN_PAGE_URL_SUFFIX', 'easy-backup-by-supsystic');

    define('EBBS_URL', EBBS_SITE_URL);

    define('EBBS_LOADER_IMG', EBBS_IMG_PATH. 'loading.gif');
    define('EBBS_DATE_DL', '/');
    define('EBBS_DATE_FORMAT', 'd/m/Y');
    define('EBBS_DATE_FORMAT_HIS', 'd/m/Y (H:i:s)');
    define('EBBS_DATE_FORMAT_JS', 'dd/mm/yy');
    define('EBBS_DATE_FORMAT_CONVERT', '%d/%m/%Y');
    define('EBBS_WPDB_PREF', $wpdb->prefix);
    define('EBBS_DB_PREF', 'ebbs_easy_');    /*BackUP*/
    define('EBBS_MAIN_FILE', 'easy-backup-by-supsystic.php');

    define('EBBS_DEFAULT', 'default');
    define('EBBS_CURRENT', 'current');


    define('EBBS_PLUGIN_INSTALLED', true);
    define('EBBS_VERSION', '1.1');
	define('EBBS_S_VERSION', EBBS_VERSION);
    define('EBBS_USER', 'user');


    define('EBBS_CLASS_PREFIX', 'ebbs');
    define('EBBS_FREE_VERSION', false);
	define('EBBS_TEST_MODE', true);
	
    define('EBBS_SUCCESS', 'Success');
    define('EBBS_FAILED', 'Failed');
	define('EBBS_ERRORS', 'ebbsErrors');

	define('EBBS_THEME_MODULES', 'theme_modules');


	define('EBBS_ADMIN',	'admin');
	define('EBBS_LOGGED','logged');
	define('EBBS_GUEST',	'guest');

	define('EBBS_ALL', 'all');

	define('EBBS_METHODS',		'methods');
	define('EBBS_USERLEVELS',	'userlevels');
	/**
	 * Framework instance code, unused for now
	 */
	define('EBBS_CODE', 'ebbs');
	define('EBBS_LANG_CODE', 'but_lng');

    /** Files per stack in filesystem backup */
    define('EBBS_FILES_PER_STACK', 400);

	//define('PCLZIP_TEMPORARY_DIR', '/usr/www/temp/');
	//require_once(EBBS_HELPERS_DIR. 'pclzip.lib.php');

    define('EBBS_LOCK_FIELD', 'ebbs_locked');
	
	define('EBBS_WP_PLUGIN_NAME', 'Easy backup by Supsystic');
