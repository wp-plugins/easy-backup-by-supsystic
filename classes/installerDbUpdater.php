<?php
class installerDbUpdaterEbbs {
	static public function runUpdate() {
		self::update_001();
		self::update_002();
		self::update_003();
		self::update_004();
	}
	static public function update_001() {
		global $wpdb;
		$wpPrefix = $wpdb->prefix;
		if(!dbEbbs::exist($wpPrefix.EBBS_DB_PREF."options", 'code', 'count_folder')){	// It's enought to run only one check for any value from this method: if it's false - this mean that whole methid didn't triggered before
			dbEbbs::query("INSERT INTO `".$wpPrefix.EBBS_DB_PREF."options` (`id`,`code`,`value`,`label`,`description`,`htmltype_id`,`params`,`cat_id`,`sort_order`,`value_type`) VALUES
				(NULL,'count_folder','','Count folder','Count folder',1,'',0,0,''),
				(NULL,'exclude','upgrade,cache','Exclude','Exclude directories',1,'',0,0,'');");
		}
	}
	static public function update_002() {
		global $wpdb;
		$wpPrefix = $wpdb->prefix;
		if(!dbEbbs::exist($wpPrefix.EBBS_DB_PREF."options", 'code', 'email')){	// It's enought to run only one check for any value from this method: if it's false - this mean that whole methid didn't triggered before
			dbEbbs::query("INSERT INTO `".$wpPrefix.EBBS_DB_PREF."options` (`id`,`code`,`value`,`label`,`description`,`htmltype_id`,`params`,`cat_id`,`sort_order`,`value_type`) VALUES
				(NULL,'email','','Email','Email',1,'',0,0,'');");
		}
	}

	static public function update_003() {
		global $wpdb;
		$wpPrefix = $wpdb->prefix;
		if(!dbEbbs::exist($wpPrefix.EBBS_DB_PREF."options", 'code', 'glb_dest')){	// It's enought to run only one check for any value from this method: if it's false - this mean that whole methid didn't triggered before
			dbEbbs::query("INSERT INTO `".$wpPrefix.EBBS_DB_PREF."options` (`id`,`code`,`value`,`label`,`description`,`htmltype_id`,`params`,`cat_id`,`sort_order`,`value_type`) VALUES
				(NULL,'glb_dest','ftp','Manual destination','Manual destination',1,'',0,0,'')");
			dbEbbs::query("INSERT INTO `".$wpPrefix.EBBS_DB_PREF."modules` (id, code, active, type_id, params, has_tab, label, description) VALUES
				(NULL, 'logger',1,1,'',0,'System logger','');");
			dbEbbs::query("INSERT INTO `".$wpPrefix.EBBS_DB_PREF."options` (`id`,`code`,`value`,`label`,`description`,`htmltype_id`,`params`,`cat_id`,`sort_order`,`value_type`) VALUES
				(NULL,'force_update','1','Force Update','Force Update',1,'',0,0,'')");
			dbEbbs::query("INSERT INTO `".$wpPrefix.EBBS_DB_PREF."options` (`id`,`code`,`value`,`label`,`description`,`htmltype_id`,`params`,`cat_id`,`sort_order`,`value_type`) VALUES
				(NULL,'safe_update','1','Safe Update','Safe Update',1,'',0,0,'')");
			dbEbbs::query("INSERT INTO `".$wpPrefix.EBBS_DB_PREF."options` (`id`,`code`,`value`,`label`,`description`,`htmltype_id`,`params`,`cat_id`,`sort_order`,`value_type`) VALUES
				(NULL,'replace_newer','1','Replace Newer','Replace newer files or not',1,'',0,0,'')");
		}
	}
	public static function update_004() {
		global $wpdb;
		$wpPrefix = $wpdb->prefix;
		if(!dbEbbs::exist($wpPrefix.EBBS_DB_PREF."options", 'code', 'warehouse_abs')){	// It's enought to run only one check for any value from this method: if it's false - this mean that whole methid didn't triggered before
			dbEbbs::query("INSERT INTO `".$wpPrefix.EBBS_DB_PREF."options` (`id`,`code`,`value`,`label`,`description`,`htmltype_id`,`params`,`cat_id`,`sort_order`,`value_type`) VALUES
				(NULL,'warehouse_abs','1','Use relative to WordPress path','Use relative to WordPress path',1,'',0,0,'')");
			dbEbbs::query("INSERT INTO `".$wpPrefix.EBBS_DB_PREF."options` (`id`,`code`,`value`,`label`,`description`,`htmltype_id`,`params`,`cat_id`,`sort_order`,`value_type`) VALUES
				(NULL,'email_ch','0','---','---',1,'',0,0,'')");
			dbEbbs::query("INSERT INTO `".$wpPrefix.EBBS_DB_PREF."modules` (`id`, `code`, `active`, `type_id`, `params`, `has_tab`, `label`, `description`) VALUES
				(NULL, 'warehouse', '1', '1','', '0', 'Warehouse', '');");
		}
	}
}
