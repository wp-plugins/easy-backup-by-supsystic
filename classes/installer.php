<?php
class installerEbbs {
	static public $update_to_version_method = '';
	static public function init() {
		global $wpdb;
		$wpPrefix = $wpdb->prefix;
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

		if (!dbEbbs::exist($wpPrefix.EBBS_DB_PREF."htmltype")) {
			dbDelta("CREATE TABLE IF NOT EXISTS `".$wpPrefix.EBBS_DB_PREF."htmltype` (
			  `id` int(11) NOT NULL AUTO_INCREMENT,
			  `label` varchar(32) NOT NULL,
			  `description` varchar(255) NOT NULL,
			  PRIMARY KEY (`id`),
			  UNIQUE INDEX `label` (`label`)
			) DEFAULT CHARSET=utf8");
			dbEbbs::query("INSERT INTO `".$wpPrefix.EBBS_DB_PREF."htmltype` VALUES
				(1, 'text', 'Text'),
				(2, 'password', 'Password'),
				(3, 'hidden', 'Hidden'),
				(4, 'checkbox', 'Checkbox'),
				(5, 'checkboxlist', 'Checkboxes'),
				(6, 'datepicker', 'Date Picker'),
				(7, 'submit', 'Button'),
				(8, 'img', 'Image'),
				(9, 'selectbox', 'Drop Down'),
				(10, 'radiobuttons', 'Radio Buttons'),
				(11, 'countryList', 'Countries List'),
				(12, 'selectlist', 'List'),
				(13, 'countryListMultiple', 'Country List with posibility to select multiple countries'),
				(14, 'block', 'Will show only value as text'),
				(15, 'statesList', 'States List'),
				(16, 'textFieldsDynamicTable', 'Dynamic table - multiple text options set'),
				(17, 'textarea', 'Textarea'),
				(18, 'checkboxHiddenVal', 'Checkbox with Hidden field')");
		}
		/**
		 * modules
		 */
		if (!dbEbbs::exist($wpPrefix.EBBS_DB_PREF."modules")) {
			dbDelta("CREATE TABLE IF NOT EXISTS `".$wpPrefix.EBBS_DB_PREF."modules` (
			  `id` int(11) NOT NULL AUTO_INCREMENT,
			  `code` varchar(64) NOT NULL,
			  `active` tinyint(1) NOT NULL DEFAULT '0',
			  `type_id` smallint(3) NOT NULL DEFAULT '0',
			  `params` text,
			  `has_tab` tinyint(1) NOT NULL DEFAULT '0',
			  `label` varchar(128) DEFAULT NULL,
			  `description` text,
			  `ex_plug_dir` varchar(255) DEFAULT NULL,
			  PRIMARY KEY (`id`),
			  UNIQUE INDEX `code` (`code`)
			) DEFAULT CHARSET=utf8;");
			dbEbbs::query("INSERT INTO `".$wpPrefix.EBBS_DB_PREF."modules` (id, code, active, type_id, params, has_tab, label, description) VALUES
				(NULL, 'adminmenu',1,1,'',0,'Admin Menu',''),
				(NULL, 'options',1,1,'',1,'Options',''),
				(NULL, 'log', 1, 1, '', 1, 'Log', 'Internal system module to log some actions on server'),
				(NULL, 'templates',1,1,'',0,'Templates for Plugin',''),
				(NULL, 'backup', 1, 1, '', 1, 'DropBox Backup by Supsystiс!', 'DropBox Backup by Supsystiс'),
				(NULL, 'dropbox', 1, 1, '', 1, 'dropbox', 'dropbox')");
		}
        if(!dbEbbs::exist($wpPrefix.EBBS_DB_PREF."modules", 'code', 'dropbox')){
            dbEbbs::query("INSERT INTO `".$wpPrefix.EBBS_DB_PREF."modules` (id, code, active, type_id, params, has_tab, label, description) VALUES
            (NULL, 'dropbox', 1, 1, '', 1, 'dropbox', 'dropbox')");
        }
		/**
		 *  modules_type
		 */
		if(!dbEbbs::exist($wpPrefix.EBBS_DB_PREF."modules_type")) {
			dbDelta("CREATE TABLE IF NOT EXISTS `".$wpPrefix.EBBS_DB_PREF."modules_type` (
			  `id` int(11) NOT NULL AUTO_INCREMENT,
			  `label` varchar(64) NOT NULL,
			  PRIMARY KEY (`id`)
			) AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;");
			dbEbbs::query("INSERT INTO `".$wpPrefix.EBBS_DB_PREF."modules_type` VALUES
				(1,'system'),
				(2,'addons')");
		}
		/**
		 * options
		 */
		$warehouse = '/'.EBBS_WP_CONTENT_DIR.'/easy-backup-storage/';
		if(!dbEbbs::exist($wpPrefix.EBBS_DB_PREF."options")) {
			dbDelta("CREATE TABLE IF NOT EXISTS `".$wpPrefix.EBBS_DB_PREF."options` (
			  `id` int(11) NOT NULL AUTO_INCREMENT,
			  `code` varchar(64) CHARACTER SET latin1 NOT NULL,
			  `value` longtext NULL,
			  `label` varchar(128) CHARACTER SET latin1 DEFAULT NULL,
			  `description` text CHARACTER SET latin1,
			  `htmltype_id` smallint(2) NOT NULL DEFAULT '1',
			  `params` text NULL,
			  `cat_id` mediumint(3) DEFAULT '0',
			  `sort_order` mediumint(3) DEFAULT '0',
			  `value_type` varchar(16) CHARACTER SET latin1 DEFAULT NULL,
			  PRIMARY KEY (`id`),
			  KEY `id` (`id`),
			  UNIQUE INDEX `code` (`code`)
			) DEFAULT CHARSET=utf8");
			dbEbbs::query("INSERT INTO `".$wpPrefix.EBBS_DB_PREF."options` (`id`,`code`,`value`,`label`,`description`,`htmltype_id`,`params`,`cat_id`,`sort_order`,`value_type`) VALUES
				(NULL,'full','1','Full backup','on/off full backup',1,'',0,0,'dest_backup'),
				(NULL,'wp_core','1','Wordpress core backup','on/off Wordpress core backup',1,'',0,0,'dest_backup'),
				(NULL,'plugins','1','Plugins','on/off backup plugins',1,'',0,0,'dest_backup'),
				(NULL,'themes','1','Themes','on/off backup themes',1,'',0,0,'dest_backup'),
				(NULL,'uploads','1','Uploads','on/off backup uploads',1,'',0,0,'dest_backup'),
				(NULL,'database','1','Database','on/off backup database',1,'',0,0,'db_backup'),
				(NULL,'any_directories','1','Any','Any other directories found inside wp-content',1,'',0,0,'dest_backup'),
				(NULL,'warehouse','".$warehouse."','Warehouse','path to storage',1,'',0,0,''),
				(NULL,'warehouse_ignore','easy-backup-storage','Warehouse_ignore','Name ignore directory storage',1,'',0,0,''),
				(NULL,'safe_array','','Safe array','Safe file array',1,'',0,0,''),
				(NULL,'dropbox_model','','Dropbox model','Module uses two models: for PHP 5.2.x and for PHP >= 5.3.x', '1','', '', '','');"
            );
		}
		//(NULL,'exclude','upgrade,cache','Exclude','Exclude directories',1,'',0,0,'')
        if(!dbEbbs::exist($wpPrefix.EBBS_DB_PREF."options", 'code', 'dropbox_model')){
            dbEbbs::query("INSERT INTO `".$wpPrefix.EBBS_DB_PREF."options` (`id`,`code`,`value`,`label`,`description`,`htmltype_id`,`params`,`cat_id`,`sort_order`,`value_type`) VALUES
			    (NULL,'dropbox_model','','Dropbox model','Module uses two models: for PHP 5.2.x and for PHP >= 5.3.x', '1','', '', '','');");
        }
		/* options categories */
		if(!dbEbbs::exist($wpPrefix.EBBS_DB_PREF."options_categories")) {
			dbDelta("CREATE TABLE IF NOT EXISTS `".$wpPrefix.EBBS_DB_PREF."options_categories` (
			  `id` int(11) NOT NULL AUTO_INCREMENT,
			  `label` varchar(128) NOT NULL,
			  PRIMARY KEY (`id`),
			  KEY `id` (`id`)
			) DEFAULT CHARSET=utf8");
			dbEbbs::query("INSERT INTO `".$wpPrefix.EBBS_DB_PREF."options_categories` VALUES
				(1, 'General'),
				(2, 'Template'),
				(3, 'Subscribe'),
				(4, 'Social');");
		}
		installerDbUpdaterEbbs::runUpdate();

		update_option(EBBS_DB_PREF. 'db_version', EBBS_VERSION);
		add_option(EBBS_DB_PREF. 'db_installed', 1);
		dbEbbs::query("UPDATE `".$wpPrefix.EBBS_DB_PREF."options` SET value = '". EBBS_VERSION. "' WHERE code = 'version' LIMIT 1");

		$warehouse = ABSPATH.$warehouse;
		if (!file_exists($warehouse)) {
			utilsEbbs::createDir($warehouse, $params = array('chmod' => 0755, 'httpProtect' => 2));
		}
	}
	static public function delete() {
		global $wpdb;
		$wpPrefix = $wpdb->prefix;

        $wpdb->query("DROP TABLE IF EXISTS `".$wpPrefix.EBBS_DB_PREF."modules`");
        $wpdb->query("DROP TABLE IF EXISTS `".$wpPrefix.EBBS_DB_PREF."modules_type`");
        $wpdb->query("DROP TABLE IF EXISTS `".$wpPrefix.EBBS_DB_PREF."options`");
        $wpdb->query("DROP TABLE IF EXISTS `".$wpPrefix.EBBS_DB_PREF."options_categories`");
        $wpdb->query("DROP TABLE IF EXISTS `".$wpPrefix.EBBS_DB_PREF."htmltype`");
        $wpdb->query("DROP TABLE IF EXISTS `".$wpPrefix.EBBS_DB_PREF."log`");

		//frameEbbs::_()->getModule('schedule')->getModel()->unSetSchedule(frameEbbs::_()->getModule('options')->getEvery());
		
		delete_option(EBBS_DB_PREF. 'db_version');
		delete_option(EBBS_DB_PREF. 'db_installed');
	}
	static protected function _addPageToWP($post_title, $post_parent = 0) {
		return wp_insert_post(array(
			 'post_title' => __($post_title, EBBS_LANG_CODE),
			 'post_content' => __($post_title. ' Page Content', EBBS_LANG_CODE),
			 'post_status' => 'publish',
			 'post_type' => 'page',
			 'post_parent' => $post_parent,
			 'comment_status' => 'closed'
		));
	}
	static public function update() {
		global $wpdb;
		$wpPrefix = $wpdb->prefix; /* add to 0.0.3 Versiom */
		$currentVersion = get_option(EBBS_DB_PREF. 'db_version', 0);
		$installed = (int) get_option(EBBS_DB_PREF. 'db_installed', 0);

		if($installed && version_compare(EBBS_VERSION, $currentVersion, '>')) {
			self::init();
			update_option($wpPrefix. 'db_version', EBBS_VERSION);
		}
	}
}
