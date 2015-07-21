<div class="bupDest">
    <form id="bupMainFormOptions" method="post">
        <div id="bupOptions">

            <div id="bupMainOption">
                <h2><?php _e('Backup Settings:', EBBS_LANG_CODE) ?></h2>

                <table class="form-table" style="width: 100% !important;">
                    <tr>
                        <td class="col-w-30perc">
                            <?php _e('Full backup', EBBS_LANG_CODE) ?>
                        </td>
                        <td class="col-w-1perc">
                            <i class="fa fa-question supsystic-tooltip" title="<?php _e('Full backup', EBBS_LANG_CODE) ?>"></i>
                        </td class="col-w-1perc">
                        <td class="col-w-1perc">
                            <?php echo htmlEbbs::checkbox('opt_values[full]', array('attrs'=>'class="bupCheckbox bupFull" id="bupFullBackup" ' . $this->zipExtExist, 'value' => 1, 'checked' => (frameEbbs::_()->getModule('options')->get('full') && $this->zipExtExist === true) ? 'checked' : '' )); ?>
                        </td>
                    </tr>
                    <tr>
                        <td class="col-w-30perc">
                            <?php _e('Wordpress Core', EBBS_LANG_CODE) ?>
                        </td>
                        <td class="col-w-1perc">
                            <i class="fa fa-question supsystic-tooltip" title="<?php _e('All folders and files backup in the root directory, where the WordPress is installed, except the /wp-content folder.', EBBS_LANG_CODE) ?>"></i>
                        </td>
                        <td class="col-w-1perc">
                            <?php echo htmlEbbs::checkbox('opt_values[wp_core]', array('attrs'=>'class="bupCheckbox bupFull" ' . $this->zipExtExist, 'value' => 1, 'checked' => (frameEbbs::_()->getModule('options')->get('wp_core') && $this->zipExtExist === true) ? 'checked' : '' )); ?>
                        </td>
                    </tr>
                    <tr>
                        <td class="col-w-30perc">
                            <?php _e('Plugins folder', EBBS_LANG_CODE) ?>
                        </td>
                        <td class="col-w-1perc">
                            <i class="fa fa-question supsystic-tooltip" title="<?php _e('Plugins folder', EBBS_LANG_CODE) ?>"></i>
                        </td>
                        <td class="col-w-1perc">
                            <?php echo htmlEbbs::checkbox('opt_values[plugins]', array('attrs'=>'class="bupCheckbox bupFull" ' . $this->zipExtExist, 'value' => 1, 'checked' => (frameEbbs::_()->getModule('options')->get('plugins') && $this->zipExtExist === true) ? 'checked' : '')); ?>
                        </td>
                    </tr>
                    <tr>
                        <td class="col-w-30perc">
                            <?php _e('Themes folder', EBBS_LANG_CODE) ?>
                        </td>
                        <td class="col-w-1perc">
                            <i class="fa fa-question supsystic-tooltip" title="<?php _e('Themes folder', EBBS_LANG_CODE) ?>"></i>
                        </td>
                        <td class="col-w-1perc">
                            <?php echo htmlEbbs::checkbox('opt_values[themes]', array('attrs'=>'class="bupCheckbox bupFull" ' . $this->zipExtExist, 'value' => 1, 'checked' => (frameEbbs::_()->getModule('options')->get('themes') && $this->zipExtExist === true) ? 'checked' : '')); ?>
                        </td>
                    </tr>
                    <tr>
                        <td class="col-w-30perc">
                            <?php _e('Uploads folder', EBBS_LANG_CODE) ?>
                        </td>
                        <td class="col-w-1perc">
                            <i class="fa fa-question supsystic-tooltip" title="<?php _e('Uploads folder', EBBS_LANG_CODE) ?>"></i>
                        </td>
                        <td class="col-w-1perc">
                            <?php echo htmlEbbs::checkbox('opt_values[uploads]', array('attrs'=>'class="bupCheckbox bupFull" ' . $this->zipExtExist, 'value' => 1, 'checked' => (frameEbbs::_()->getModule('options')->get('uploads') && $this->zipExtExist === true) ? 'checked' : '')); ?>
                        </td>
                    </tr>
                    <tr>
                        <td class="col-w-30perc">
                            <?php _e('Any folder inside wp-content', EBBS_LANG_CODE) ?>
                        </td>
                        <td class="col-w-1perc">
                            <i class="fa fa-question supsystic-tooltip" title="<?php _e('Any folder inside wp-content', EBBS_LANG_CODE) ?>"></i>
                        </td>
                        <td class="col-w-1perc">
                            <?php echo htmlEbbs::checkbox('opt_values[any_directories]', array('attrs'=>'class="bupCheckbox bupFull" ' . $this->zipExtExist, 'value' => 1, 'checked' => (frameEbbs::_()->getModule('options')->get('any_directories') && $this->zipExtExist === true) ? 'checked' : '')); ?>
                        </td>
                    </tr>
                    <tr>
                        <td class="col-w-30perc">
                            <?php _e('Database backup', EBBS_LANG_CODE) ?>
                        </td>
                        <td class="col-w-1perc">
                            <i class="fa fa-question supsystic-tooltip" title="<?php _e('Database backup', EBBS_LANG_CODE) ?>"></i>
                        </td>
                        <td class="col-w-1perc">
                            <?php echo htmlEbbs::checkbox('opt_values[database]', array('attrs'=>'class="bupCheckbox bupFull bupDatabaseCheckbox"', 'value' => 1, 'checked' => frameEbbs::_()->getModule('options')->get('database') ? 'checked' : '')); ?>
                        </td>
                    </tr>
                    <tr>
                        <td class="col-w-30perc">
                            <span><?php echo ABSPATH . EBBS_WP_CONTENT_DIR. DS . 'easy-backup-storage'; ?></span>
                        </td>
                        <td class="col-w-1perc">
                            <i class="fa fa-question supsystic-tooltip" title="<?php _e('Path to directory, where will be stored backup files.', EBBS_LANG_CODE) ?>"></i>
                        </td>
                        <td class="col-w-1perc">
                            &nbsp;
                        </td>
                    </tr>
                </table>

            </div>
        </div>
    </form>
</div>