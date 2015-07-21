<?php
class tableHtmltypeEbbs extends tableEbbs {
    public function __construct() {
        $this->_table = '@__htmltype';
        $this->_id = 'id';     
        $this->_alias = 'toe_htmlt';
        $this->_addField('id', 'hidden', 'int', 0, __('ID', EBBS_LANG_CODE))
            ->_addField('label', 'text', 'varchar', 0, __('Method', EBBS_LANG_CODE), 32)
            ->_addField('description', 'text', 'varchar', 0, __('Description', EBBS_LANG_CODE), 255);
    }
}
?>
