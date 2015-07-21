<?php
class tableModules_typeEbbs extends tableEbbs {
    public function __construct() {
        $this->_table = '@__modules_type';
        $this->_id = 'id';     /*Let's associate it with posts*/
        $this->_alias = 'toe_m_t';
        $this->_addField($this->_id, 'text', 'int', '', __('ID', EBBS_LANG_CODE))->
                _addField('label', 'text', 'varchar', '', __('Label', EBBS_LANG_CODE), 128);
    }
}
?>
