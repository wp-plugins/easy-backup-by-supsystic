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
                        <?php echo ($type == 'zip') ? $model->formatBackupSize($logs[$id]['backupFolderSize']) : $model->formatBackupSize($data['size']); ?>
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
<!--                        <button class="button button-primary button-small bupDownload" data-filename="--><?php //echo $data['name']; ?><!--">-->
<!--                            --><?php //_e('Download', EBBS_LANG_CODE); ?>
<!--                        </button>-->
                        <!-- /downloadButton -->
                        <!-- deleteButton -->
                        <button class="button button-primary button-small bupDelete" data-id="<?php echo $id; ?>" data-filename="<?php echo $data['name']; ?>">
                            <?php _e('Delete', EBBS_LANG_CODE); ?>
                        </button>
                        <!-- /deleteButton -->
                        <!-- logButton -->
                        <button class="button button-primary button-small bupShowLogDlg" data-log="<?php echo !empty($logs[$id]['content']) ? nl2br($logs[$id]['content']) : __('Log is empty.', EBBS_LANG_CODE)?>">
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
                        <?php echo ($type == 'zip') ? $model->formatBackupSize($logs[$id]['backupFolderSize']) : $file['size']; ?>
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