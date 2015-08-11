<style>
    #bupMainOption div.error{
        display: none;
    }
</style>
<section xmlns="http://www.w3.org/1999/html">
    <div class="supsystic-item supsystic-panel">
        <?php
        $not_piad = utilsEbbs::checkPRO() ? '' : 'bupNotPaid';
        ?><div class="description" style="border-bottom: 1px dashed #e3e3e3; padding-bottom: 10px; margin-bottom: 10px">
            <p style="white-space: normal !important;">
                <?php _e('To restore website backup, be sure that all files and folders in the core directory have writing permissions. Backup restoration can rewrite some of them.', EBBS_LANG_CODE) ?>
            </p>
        </div>

        <div class="bupStartBackupAndLog">
            <form class="cspNiceStyle" id="bupAdminMainForm">
                <?php if($this->zipNotExtMsg !== true) {?>
                    <p class="bupErrorMsg"><?php echo $this->zipNotExtMsg; ?></p>
                <?php }?>

                <table width="100%">
                    <tr class="cspAdminOptionRow cspTblRow">
                        <td style="padding-left: 0">
                            <?php echo htmlEbbs::hidden('reqType', array('value' => 'ajax'))?>
                            <?php echo htmlEbbs::hidden('page', array('value' => 'backup'))?>
                            <?php echo htmlEbbs::hidden('action', array('value' => 'createAction'))?>
                            <?php echo htmlEbbs::hidden('dest_opt', array('value' => 'ftp', 'attrs' => 'id="bupBackupDestination"'))?>
                            <?php $attrs = array('class="button button-primary button-large" style="margin-right: 10px;"'); $style = ''; ?>
                            <?php if (defined('EBBS_LOCK_FIELD') && get_option(EBBS_LOCK_FIELD) == 1): ?>
                                <?php $attrs[] = 'style="display:none;"'; ?>
                            <?php else: ?>
                                <?php $style = 'display:none;'; ?>
                            <?php endif; ?>

                            <p>
                                <?php if($isUserAuthenticatedInDropBox):?>
                                    <button class="button button-primary button-large bupStartDropBoxBackup"><?php _e('Start DropBox Backup', EBBS_LANG_CODE)?></button>
                                    <span class="bupBackupButtonAnnotate">
                                        <?php _e('With this option you can backup files and data<br>base to DropBox.', EBBS_LANG_CODE)?>
                                    </span>
                                <?php else:
                                    echo $dropBoxAuthButton;?>
                                    <span class="bupBackupButtonAnnotate">
                                        <?php _e('Before start backup to DropBox, you need to<br>authorize - just click on the button.', EBBS_LANG_CODE)?>
                                    </span>
                                <?php endif; ?>

                                <button class="button button-primary button-large bupStartLocalBackup" style="margin-left: -200px"><?php _e('Start Local Backup', EBBS_LANG_CODE)?></button>
                                <span class="bupBackupButtonAnnotate">
                                    <?php _e('With this option you can backup files and data<br>base to local server.', EBBS_LANG_CODE)?>&shy;
                                </span>
                            <p>
                            <?php if($isUserAuthenticatedInDropBox):?>
                            <p>
                                <?php
                                $dropBoxLogOutButton = '<button class="button button-primary button-small" id="bupDropboxLogout">' .__('Log Out', EBBS_LANG_CODE) . '</button>';
                                _e(sprintf(
                                    'To change DropBox account - you need to %s from current account and Authenticate again.', $dropBoxLogOutButton),
                                    EBBS_LANG_CODE)?>
                            </p>
                            <?php endif; ?>

                            <div id="bupInfo">
                                <p style="font-size: 15px;">
<!--                                    --><?php //_e('Available space:', EBBS_LANG_CODE) ?><!-- <br/>-->
                                    <?php if (frameEbbs::_()->getModule('warehouse')->getWarehouseStatus()): ?>
<!--                                        --><?php //echo frameEbbs::_()->humanSize(
//                                            disk_free_space(frameEbbs::_()->getModule('warehouse')->getPath())
//                                        );
                                        ?>
                                    <?php else: ?>
                                        <span class="bupErrorMsg">
                                        <?php _e('An errors has been occured while initialize warehouse module.', EBBS_LANG_CODE); ?>
                                    </span>
                                    <?php endif; ?>
                                </p>
                            </div>

                        </td>
                    </tr>
                </table>


                <div id="EBBS_MESS_MAIN"></div>

            </form>

            <div class="bupBackupLog">
                <h3 style="text-align: center; margin: 0;"><?php _e('Backup Log', EBBS_LANG_CODE)?> <span id="bupBackupStatusPercent"></span></h3>
                <p class="bupBackupLogContentText" style="white-space: normal;">

                </p>
            </div>
        </div>

        <div class="bupBackupSettingsBlock">
            <button class="button button-primary button-large bupShowBackupAdvancedOptions" style="float: right; min-width: 188px; margin-top: 17px;">Show Advanced Options</button>
            <h3 style="margin-bottom: 0.5em; margin-top: 13px !important;">
                <?php _e('Backup Type:', EBBS_LANG_CODE)?> <span style="font-weight: normal" id="bupBackupType"><?php _e('Full Backup', EBBS_LANG_CODE);?></span>
            </h3>
            <div class="bupSelectedOptionsWrapper" style="font-weight: bold">
                <?php _e('Selected options: ');?><div id="bupSelectedOptions"  style="font-weight: normal; display: inline"></div>
            </div>

            <div class="bupBackupAdvancedSettings">
                <?php echo $this->backupOptions ?>
            </div>
        </div>

        <div id="bupBackupWrapper">
            <?php echo $this->backupsList ?>
        </div>

    </div>
</section>
