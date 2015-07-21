<?php
class tableLogEbbs extends tableEbbs {
    public function __construct() {
        $this->_table = '@__log';
        $this->_id = 'id';     /*Let's associate it with posts*/
        $this->_alias = 'toe_log';
        $this->_addField('id', 'text', 'int', 0, __('ID', EBBS_LANG_CODE), 11)
                ->_addField('type', 'text', 'varchar', '', __('Type', EBBS_LANG_CODE), 64)
                ->_addField('data', 'text', 'text', '', __('Data', EBBS_LANG_CODE))
                ->_addField('date_created', 'text', 'int', '', __('Date created', EBBS_LANG_CODE))
				->_addField('uid', 'text', 'int', 0, __('User ID', EBBS_LANG_CODE))
				->_addField('oid', 'text', 'int', 0, __('Order ID', EBBS_LANG_CODE));
    }
}