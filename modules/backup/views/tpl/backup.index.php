<section>
    <div class="supsystic-item supsystic-panel">
        <div id="bupBackupWrapper">
            <div id="bupAdminStorageTable" style="width: 50%; display: inline-block;">

                <?php
                if(!empty($backups)):
                    foreach ($backups as $id => $type):
                    $backupType = key($type);
                    $backupStartDateTime  = (!empty($logs[$id]['content'])) ? __(' / Start:', EBBS_LANG_CODE) . '<b>' . $model->getBackupStartTimeFromLog($logs[$id]['content']) . '</b>' : '' ;
                    $backupFinishDateTime = (!empty($logs[$id]['content'])) ? __(' / Finish:', EBBS_LANG_CODE) . '<b>' . $model->getBackupFinishTimeFromLog($logs[$id]['content']) . '</b>' : '';
                    $backupTimeInfo = $backupStartDateTime . ' ' . $backupFinishDateTime;
                    if($backupType == 'ftp'):
                        $backup = $type['ftp'];
                        $sqlExist = !empty($backup['sql']) ? 'data-sql="sql"' : false; // this attribute used in JS(migration module), if it exist - show inputs for find/replace site url in DB dump
                        $encrypted = !empty($backup['sql']['encrypted']) ? $backup['sql']['encrypted'] : ''; // this class used in JS(migration module), if it exist - show input for decrypt DB dump for find/replace site url
                    ?>

                    <!--  FTP files rendering start    -->
                    <div class="backupBlock">
                        <p>
                            <?php _e('Backup to <b>FTP</b> / ID', EBBS_LANG_CODE)?> <b><?php echo $id; ?></b><?php echo $backupTimeInfo ?>
                        </p>
                        <div align="left" id="MSG_EL_ID_<?php echo $id; ?>"></div>

                        <div id="bupControl-<?php echo $id?>">

                            <table>
                                <tbody>
                                    <?php foreach ($backup as $type => $data): ?>

                                    <tr class="tabStr" id="<?php echo $data['name']; ?>">
                                        <td>
                                            <?php echo ($type == 'zip' ? __('Filesystem', EBBS_LANG_CODE) : __('Database', EBBS_LANG_CODE)); ?>
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
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>

                            <?php if(!empty($logs[$id]['content'])):?>
                                <span class="bupShowLogDlg" data-log="<?php echo nl2br($logs[$id]['content'])?>"><?php _e('Show Backup Log', EBBS_LANG_CODE) ?></span>
                            <?php else: ?>
                                <b><?php _e('Log is clear.', EBBS_LANG_CODE) ?></b>
                            <?php endif; ?>
                        </div>
                    </div>
                    <hr/>
                    <!--  FTP files rendering end    -->

                <!--  DropBox files rendering start    -->
                    <?php
                    elseif($backupType == 'dropbox'):
                        $files = $type['dropbox'];
                        $encrypted = !empty($files['sql']['backupInfo']['encrypted']) ? $files['sql']['backupInfo']['encrypted'] : '';
                        ?>
                        <div class="backupBlock">
                            <p>
                                <?php _e('Backup to <b>DropBox</b> / ID', EBBS_LANG_CODE)?> <b><?php echo $id; ?></b><?php echo $backupTimeInfo ?>
                            </p>
                            <div id="bupDropboxAlerts-<?php echo $id; ?>"></div>
                            <div id="bupControl-<?php echo $id?>">
                                <table>
                                    <tbody>
                                    <?php foreach($files as $type=>$file):?>
                                        <tr id="row-<?php echo $type.'-'.$id; ?>">
                                            <td>
                                                <?php echo ($type == 'sql') ? __('Database', EBBS_LANG_CODE) : __('Filesystem', EBBS_LANG_CODE); ?>
                                            </td>
                                            <td>
                                                <?php echo basename($file['path']); ?>
                                            </td>
                                            <td>
                                                <button
                                                    class="button button-primary button-small bupDropboxRestore"
                                                    data-filename="<?php echo basename($file['path']); ?>"
                                                    data-row-id="<?php echo $id; ?>"
                                                    >
                                                    <?php _e('Restore', EBBS_LANG_CODE); ?>
                                                </button>
                                                <button
                                                    class="button button-primary button-small bupDropboxDelete"
                                                    data-filepath="<?php echo $file['path']; ?>"
                                                    data-row-id="<?php echo $id; ?>"
                                                    data-file-type="<?php echo $type; ?>"
                                                    >
                                                    <?php _e('Delete', EBBS_LANG_CODE); ?>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                    </tbody>
                                </table>

                                <?php if(!empty($logs[$id]['content'])):?>
                                    <span class="bupShowLogDlg" data-log="<?php echo nl2br($logs[$id]['content'])?>"><?php _e('Show Backup Log', EBBS_LANG_CODE)?></span>
                                <?php else: ?>
                                    <b><?php _e('Log is clear.', EBBS_LANG_CODE) ?></b>
                                <?php endif; ?>
                            </div>
                        </div>
                        <hr/>
                        <!--  DropBox files rendering end    -->

                    <?php endif; ?>
                <?php endforeach;
                else:?>
                    <h3><?php _e('You have no Backups for now.', EBBS_LANG_CODE) ?></h3>
                <?php endif;
                ?>
            </div>

            <!-- Log modal window start  -->
            <div id="bupShowLogDlg" title="<?php _e('Backup Log:', EBBS_LANG_CODE); ?>">
                <p id="bupLogText" style="margin: 0;"></p>
            </div>
            <!-- Log modal window end  -->
        </div>
    </div>
</section>