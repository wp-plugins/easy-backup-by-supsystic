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

                <div id="EBBS_MESS_MAIN"></div>

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

                            <div id="EBBS_SHOW_LOG" style="display: none;">
                                <p id="inProcessMessage" class="bupErrorMsg" style="<?php echo $style; ?>">
                                    <?php _e('Backup already in process.', EBBS_LANG_CODE) ?>
                                </p>
                            </div>

                        </td>
                    </tr>
                </table>

            </form>

            <div class="bupBackupLog">
                <h3 style="text-align: center; margin: 0;"><?php _e('Backup Log', EBBS_LANG_CODE)?> <span id="bupBackupStatusPercent" style="display: none"></span></h3>
                <?php _e('Start', EBBS_LANG_CODE);?><br/>
                <p class="bupBackupLogContentText">

                </p>
            </div>
        </div>

        <div class="bupBackupSettingsBlock">
            <button class="button button-primary button-large bupShowBackupAdvancedOptions" style="float: right; min-width: 188px;">Show Advanced Options</button>
            <h3 style="margin-bottom: 0.5em">
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

                <?php
                if(!empty($backups)):?>
                    <div align="left" id="MSG_EL_ID_"></div>
                    <div id="bupDropboxAlertsFiles"></div>
                    <table class="table table-hover bupBackupsList">
                        <tr>
                            <th><?php _e('Type', EBBS_LANG_CODE)?></th>
                            <th><?php _e('Size', EBBS_LANG_CODE)?></th>
                            <th><?php _e('Date', EBBS_LANG_CODE)?></th>
                            <th><?php _e('Actions', EBBS_LANG_CODE)?></th>
                        </tr>
                    <?php
                    foreach ($backups as $id => $type):
                        $backupType = key($type);
                        $backupStartDateTime  = (!empty($logs[$id]['content'])) ? __('<b>Start: </b>', EBBS_LANG_CODE) . '<b>' . $model->getBackupStartTimeFromLog($logs[$id]['content']) . '</b>' : '' ;
                        $backupFinishDateTime = (!empty($logs[$id]['content'])) ? __('<b>Finish: </b>', EBBS_LANG_CODE) . '<b>' . $model->getBackupFinishTimeFromLog($logs[$id]['content']) . '</b>' : '';
                        $backupTimeInfo = $backupStartDateTime . ' ' . $backupFinishDateTime;

                        if($backupType == 'ftp'):
                            $backup = $type['ftp'];
                            ?>

                            <!--  FTP files rendering start    -->
                                <?php foreach ($backup as $type => $data): ?>

                                    <tr id="<?php echo $data['name']; ?>" class="bupBackupIdObj_<?php echo $id?>">
                                        <td>
                                            <strong>#<?php echo $id;?></strong>
                                            <i class="fa fa-question supsystic-tooltip" title="<?php echo !empty($logs[$id]['settings']) ? $logs[$id]['settings'] : __('Settings not found!', EBBS_LANG_CODE); ?>"></i>
                                            <?php echo $type == 'zip' ? __(' Local Server - Filesystem', EBBS_LANG_CODE) : __(' Local Server - Database', EBBS_LANG_CODE);?>
                                            <input type="hidden" id="bup_id_<?php echo $id;?>" value="<?php echo count($backup)?>"/>
                                        </td>

                                        <td>
                                            <?php echo $data['size']?>
                                        </td>

                                        <td>
                                            <?php echo $data['date']?>
                                            <i class="fa fa-question supsystic-tooltip" title="<?php echo $backupStartDateTime . '<br>'. $backupFinishDateTime ?>"></i>
                                        </td>

                                        <td>
                                            <!-- restoreButton -->
                                            <button class="button button-primary button-small bupRestore" data-id="<?php echo $id; ?>" data-filename="<?php echo $data['name']; ?>" >
                                                <?php _e('Restore', EBBS_LANG_CODE); ?>
                                            </button>
                                            <!-- /restoreButton -->
                                            <!-- downloadButton -->
                                            <button class="button button-primary button-small bupDownload" data-filename="<?php echo $data['name']; ?>">
                                                <?php _e('Download', EBBS_LANG_CODE); ?>
                                            </button>
                                            <!-- /downloadButton -->
                                            <!-- deleteButton -->
                                            <button class="button button-primary button-small bupDelete" data-id="<?php echo $id; ?>" data-filename="<?php echo $data['name']; ?>">
                                                <?php _e('Delete', EBBS_LANG_CODE); ?>
                                            </button>
                                            <!-- /deleteButton -->
                                            <!-- logButton -->
                                            <button class="button button-primary button-small bupShowLogDlg" data-log="<?php echo !empty($logs[$id]['content']) ? nl2br($logs[$id]['content']) : __('Log is empty.', EBBS_LANG_CODE);?>">
                                                <?php _e('Show Backup Log', EBBS_LANG_CODE) ?>
                                            </button>
                                            <!-- /logButton -->
                                        </td>
                                    </tr>
                                <?php endforeach; ?>


                            <!--  FTP files rendering end    -->

                            <!--  DropBox files rendering start    -->
                        <?php
                        elseif($backupType == 'dropbox'):
                            $files = $type['dropbox'];
                            ?>
                                <?php foreach($files as $type => $file):?>

                                    <tr id="row-<?php echo $type.'-'.$id; ?>" class="bupBackupIdObj_<?php echo $id?>">
                                        <td>
                                            <strong>#<?php echo $id;?></strong>
                                            <i class="fa fa-question supsystic-tooltip" title="<?php echo !empty($logs[$id]['settings']) ? $logs[$id]['settings'] : __('Log is empty!', EBBS_LANG_CODE); ?>"></i>
                                            <?php echo $type == 'zip' ? __('DropBox - Filesystem', EBBS_LANG_CODE) : __('DropBox - Database', EBBS_LANG_CODE);?>
                                            <input type="hidden" id="bup_id_<?php echo $id;?>" value="<?php echo count($files)?>"/>
                                        </td>

                                        <td>
                                            <?php echo $file['size']?>
                                        </td>

                                        <td>
                                            <?php echo $file['backupInfo']['date']?>
                                            <i class="fa fa-question supsystic-tooltip" title="<?php echo $backupStartDateTime . '<br>'. $backupFinishDateTime ?>"></i>
                                        </td>

                                        <td>
                                            <!-- restoreButton -->
                                            <button
                                                class="button button-primary button-small bupDropboxRestore"
                                                data-filename="<?php echo basename($file['path']); ?>"
                                                data-row-id="<?php echo $id; ?>"
                                                >
                                                <?php _e('Restore', EBBS_LANG_CODE); ?>
                                            </button>
                                            <!-- /restoreButton -->
                                            <button
                                                class="button button-primary button-small bupDropboxDelete"
                                                data-filepath="<?php echo $file['path']; ?>"
                                                data-row-id="<?php echo $id; ?>"
                                                data-file-type="<?php echo $type; ?>"
                                                >
                                                <?php _e('Delete', EBBS_LANG_CODE); ?>
                                            </button>
                                            <!-- logButton -->
                                            <button class="button button-primary button-small bupShowLogDlg" data-log="<?php echo !empty($logs[$id]['content']) ? nl2br($logs[$id]['content']) : __('Log is empty.', EBBS_LANG_CODE);?>">
                                                <?php _e('Show Backup Log', EBBS_LANG_CODE) ?>
                                            </button>
                                            <!-- /logButton -->
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <!--  DropBox files rendering end    -->

                        <?php endif; ?>
                    <?php endforeach;?>
                    </table>
                <?php else:?>
                    <h3><?php _e('You have no Backups for now.', EBBS_LANG_CODE) ?></h3>
                <?php endif;
                ?>

            <!-- Log modal window start  -->
            <div id="bupShowLogDlg" title="<?php _e('Backup Log:', EBBS_LANG_CODE); ?>">
                <p id="bupLogText" style="margin: 0;"></p>
            </div>
            <!-- Log modal window end  -->
        </div>

    </div>
</section>
