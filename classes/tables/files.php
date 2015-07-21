<?php
class tableFilesEbbs extends tableEbbs {
    public function __construct() {
        $this->_table = '@__files';
        $this->_id = 'id';
        $this->_alias = 'toe_f';
        $this->_addField('pid', 'hidden', 'int', '', __('Product ID', EBBS_LANG_CODE))
                ->_addField('name', 'text', 'varchar', '255', __('File name', EBBS_LANG_CODE))
                ->_addField('path', 'hidden', 'text', '', __('Real Path To File', EBBS_LANG_CODE))
                ->_addField('mime_type', 'text', 'varchar', '32', __('Mime Type', EBBS_LANG_CODE))
                ->_addField('size', 'text', 'int', 0, __('File Size', EBBS_LANG_CODE))
                ->_addField('active', 'checkbox', 'tinyint', 0, __('Active Download', EBBS_LANG_CODE))
                ->_addField('date','text','datetime','',__('Upload Date', EBBS_LANG_CODE))
                ->_addField('download_limit','text','int','',__('Download Limit', EBBS_LANG_CODE))
                ->_addField('period_limit','text','int','',__('Period Limit', EBBS_LANG_CODE))
                ->_addField('description', 'textarea', 'text', 0, __('Descritpion', EBBS_LANG_CODE))
                ->_addField('type_id','text','int','',__('Type ID', EBBS_LANG_CODE));
    }
}
