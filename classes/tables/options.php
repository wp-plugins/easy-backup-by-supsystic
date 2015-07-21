<?php
class tableOptionsEbbs extends tableEbbs {
     public function __construct() {
        $this->_table = '@__options';
        $this->_id = 'id';     /*Let's associate it with posts*/
        $this->_alias = 'toe_opt';
        $this->_addField('id', 'text', 'int', 0, __('ID', EBBS_LANG_CODE))->
                _addField('code', 'text', 'varchar', '', __('Code', EBBS_LANG_CODE), 64)->
                _addField('value', 'text', 'varchar', '', __('Value', EBBS_LANG_CODE), 134217728)->
                _addField('label', 'text', 'varchar', '', __('Label', EBBS_LANG_CODE), 255)->
                _addField('description', 'text', 'text', '', __('Description', EBBS_LANG_CODE))->
                _addField('htmltype_id', 'selectbox', 'text', '', __('Type', EBBS_LANG_CODE))->
				_addField('cat_id', 'hidden', 'int', '', __('Category ID', EBBS_LANG_CODE))->
				_addField('sort_order', 'hidden', 'int', '', __('Sort Order', EBBS_LANG_CODE))->
				_addField('value_type', 'hidden', 'varchar', '', __('Value Type', EBBS_LANG_CODE));;
    }
}
?>
