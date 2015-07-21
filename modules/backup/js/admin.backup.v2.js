!function(a){var b={},c={};a.ajaxq=function(d,e){function i(a){if(b[d])b[d].push(a);else{b[d]=[];var e=a();c[d]=e}}function j(){if(b[d]){var a=b[d].shift();if(a){var e=a();c[d]=e}else delete b[d]}}if("undefined"==typeof e)throw"AjaxQ: queue name is not provided";var f=a.Deferred(),g=f.promise();g.success=g.done,g.error=g.fail,g.complete=g.always;var h=a.extend(!0,{},e);return i(function(){var b=a.ajax.apply(window,[h]);return b.done(function(){f.resolve.apply(this,arguments)}),b.fail(function(){f.reject.apply(this,arguments)}),b.always(j),b}),g},a.each(["getq","postq"],function(b,c){a[c]=function(b,d,e,f,g){return a.isFunction(e)&&(g=g||f,f=e,e=void 0),a.ajaxq(b,{type:"postq"===c?"post":"get",url:d,data:e,success:f,dataType:g})}});var d=function(a){return b.hasOwnProperty(a)},e=function(){for(var a in b)if(d(a))return!0;return!1};a.ajaxq.isRunning=function(a){return a?d(a):e()},a.ajaxq.getActiveRequest=function(a){if(!a)throw"AjaxQ: queue name is required";return c[a]},a.ajaxq.abort=function(b){if(!b)throw"AjaxQ: queue name is required";a.ajaxq.clear(b);var c=a.ajaxq.getActiveRequest(b);c&&c.abort()},a.ajaxq.clear=function(a){if(a)b[a]&&delete b[a];else for(var c in b)b.hasOwnProperty(c)&&delete b[c]}}(jQuery);

jQuery(document).ready(function($) {
	var j = jQuery.noConflict();
	var getBackupLogInterval;
	var backupWasStarted = false;

	//if(lastBackupId !== undefined ){
	//	j(".bupBackupIdObj_" + lastBackupId).highlight("highlight");
	//}

	bupBackupsShowLogDlg();
	checkBackupType(j);

	var inProcessMessage = j('#inProcessMessage');
	if (inProcessMessage.length) {
		var refreshId = setInterval(function () {
			j.post(ajaxurl, {
				pl: 'ebbs',
				reqType: 'ajax',
				page: 'backup',
				action: 'checkProcessAction'
			}).success(function (response) {
				response = j.parseJSON(response);

				if (response.data.in_process) {
					inProcessMessage.show();
					backupWasStarted = true;
				} else {
					inProcessMessage.hide();
					if(backupWasStarted) {
						clearInterval(refreshId);
						jQuery('#EBBS_MESS_MAIN').removeClass('bupErrorMsg').addClass('bupSuccessMsg').html('Backup complete');
						clearBackupLogAfterComplete();
					}
				}
			});
		}, 15000);
	}

	j('.bupShowBackupAdvancedOptions').click(function() {
		j('.bupBackupAdvancedSettings').toggle(500);
		j('.bupSelectedOptionsWrapper').toggle(500);
	});

	j('.bupBackupAdvancedSettings .bupCheckbox').on('click', function () {
		checkBackupType(j);
	});

	// Download
	j('.bupDownload').on('click', function() {
		var filename = j(this).attr('data-filename');

		BackupModule.download(filename);
	});

	// Delete
	j('.bupDelete').on('click', function() {
		if (confirm('Are you sure?')) {
			var filename  = j(this).attr('data-filename'),
				id        = j(this).attr('data-id'),
				deleteLog = 1,
				backupFilesCount = j('#bup_id_' + id).val();

			//If two backup files(DB & Filesystem) exist - don't remove backup log
			if(backupFilesCount == '2'){
				deleteLog = 0;
			}

			BackupModule.remove(id, filename, this, deleteLog);
		}
	});

	// Restore
	jQuery(document).on('click', '.bupRestore', function(){
		if (confirm('Are you sure?')) {
			var filename = j(this).attr('data-filename'),
					id   = j(this).attr('data-id');
			BackupModule.restore(id, filename);
		}
	});

	// Create
	j('#bupAdminMainForm').submit(function(event) {
		jQuery('#EBBS_SHOW_LOG').show();
		jQuery("#bupOptions").clone().prependTo("#bupAdminMainForm");
		jQuery("#bupAdminMainForm #bupOptions").hide();

		event.preventDefault();
		BackupModule.create(this);

		jQuery("#bupAdminMainForm #bupOptions").remove();
	});

	j('.bupStartLocalBackup').click(function() {
		j('#bupBackupDestination').val('ftp');
	});

	j('.bupStartDropBoxBackup').click(function($this) {
		j('#bupBackupDestination').val('dropbox');
	});

	j('.bupSaveRestoreSetting').on('click', function(clickEvent) {
		BackupModule.saveRestoreSetting(clickEvent);
	});
});

/**
 * Backup Module
 */
var BackupModule = {
	download: function(filename) {
		document.location.href = document.location.href + '&download=' + filename;
	},
	remove: function(id, filename, button, deleteLog) {
		jQuery.sendFormEbbs({
			msgElID: 'MSG_EL_ID_',
			data: {
				'reqType':  'ajax',
				'page':     'backup',
				'action':   'removeAction',
				'filename': filename,
				'deleteLog': deleteLog
			},
			onSuccess: function(response) {
				if (response.error === false) {
					jQuery(button).parent().parent().remove();
					location.reload();
				}
			}
		});
	},
	restore: function(id, filename) {
		var secretKey = jQuery('input.bupSecretKeyForCryptDB').val();
		jQuery.sendFormEbbs({
			msgElID: 'MSG_EL_ID_',
			data: {
				'reqType':  'ajax',
				'page':     'backup',
				'action':   'restoreAction',
				'filename': filename,
				'encryptDBSecretKey': secretKey
			},
			onSuccess: function(response) {
				if (response.error === false && !response.data.need) {
					jQuery('#bupEncryptingModalWindow').dialog('close');
					location.reload(true);
				} else if(response.data.need) {
					requestSecretKeyToRestoreEncryptedDb('bupRestore', {'id': id, 'filename': filename}); // open modal window to request secret key for decrypt DB dump
				} else if(response.error) {
					jQuery('input.bupSecretKeyForCryptDB').val(''); // clear input value, because user earlier entered secret key
					jQuery('#bupEncryptingModalWindow').dialog('close');
				}
			}
		});
	},
	create: function(form) {
		jQuery('#bupInfo').hide();
		var backupLog = jQuery('.bupBackupLogContentText');


		jQuery('.bupBackupLog').show(500);
		backupLog.html('');

		getBackupLogInterval = setInterval(function () {
			getBackupLog();
		}, 3000);

		jQuery(form).sendFormEbbs({
			msgElID: 'EBBS_MESS_MAIN',
			onSuccess: function(response) {

                if (response.data.files === undefined) {
					if(response.data.tables !== undefined && response.data.tables.length > 0) {
						createDatabaseBackupPerQuery(response.data.dbDumpFileName, response.data.tables, response.data.per_stack, false);
						return;
					}
					onBackupSuccessfulComplete(response);
                    return;
                }

                var files = response.data.files;
				var perStack = response.data.per_stack;
				var stacks = bupGetFilesStacks(files, perStack);
				var total = stacks.length;
				var i = 0;

                if ( stacks.length < 1) {
					jQuery('#EBBS_MESS_MAIN')
						.addClass('bupSuccessMsg')
						.text('Could not find any files to backup based on your settings.');
				}


				jQuery.each(stacks, function (index, stack) {
					bupGetTemporaryArchive(stack, function () {
						var percent;

						i++;

						percent = (i / total) * 100;

						jQuery('#EBBS_MESS_MAIN').addClass('bupSuccessMsg').text('Please wait while the plugin gathers information  (' + Math.round(percent) + '%)');
						jQuery('#bupBackupStatusPercent').text(Math.round(percent) + '%');

						if (percent === 100) {
							setTimeout(function () {
								sendCompleteRequest();
								jQuery('#EBBS_MESS_MAIN').addClass('bupSuccessMsg').text('Processing file stacks, please wait. It may take some time (depending on the number and size of files)');
							}, 3000);
						}
					});
				});
			}
		});
	},
	upload: function(providerModule, providerAction, files, identifier) {
		jQuery.sendFormEbbs({
			msgElID: 'MSG_EL_ID_',
			data: {
				page:    providerModule, // Module
				action:  providerAction, // Action
				reqType: 'ajax',         // Request type
				sendArr: files           // Files
			}
		});
	},
	saveRestoreSetting: function(clickEvent){
		var key = j(clickEvent.currentTarget).data('setting-key');
		var value = j(clickEvent.currentTarget).attr('checked');

		jQuery.sendFormEbbs({
			msgElID: 'bupRestorePresetsMsg',
			data: {
				'page':    'backup', // Module
				'action':  'saveRestoreSettingAction', // Action
				'reqType': 'ajax',         // Request type
				'setting-key': key,
				'value': value

				},
			onSuccess: function(response) {
					if(response.error)
						document.location.reload();
			}
		});
	}
};

function bupGetFilesStacks(files, num) {
	if(files) {
		var stack = [],
			parts = Math.ceil(files.length / num);

		for (var i = 0; i < parts; i++) {
			stack.push(bupGetStack(files, num));
		}

		return stack;
	}
}

function bupGetStack(files, num) {

    var stack = [];

    if (files.length < num) {
        num = files.length;
    }

    for(var j = 0; j < num; j++) {
        stack.push(files.pop());
    }

    return stack;
}

function bupGetTemporaryArchive(files, cb) {
    jQuery.postq('bupTempArchive', ajaxurl,{
        reqType: 'ajax',
        page:    'backup',
        action:  'createStackAction',
        files:   files,
        pl:      'ebbs'
    }, function (response) {

        response = jQuery.parseJSON(response);

        cb();
        if (!response.error) {
            jQuery.postq('bupWriteTempArchive', ajaxurl, {
                reqType: 'ajax',
                page: 'backup',
                action: 'writeTmpDbAction',
                tmp: response.data.filename,
                pl: 'ebbs'
            });
        }
    });
}

function createDatabaseBackupPerQuery(dumpFileName, tables, perStack, zipBackupExist){
	perStack = parseInt(perStack);
	var i          = 0,
		generalI   = 0,
		stack      = [],
		subStack   = [],
		firstQuery = 1;

	jQuery.each(tables, function(index, element) {
		subStack.push(element);
		i++;
		generalI++;

		if(i === perStack || generalI === tables.length){
			stack.push(subStack);
			i = 0;
			subStack = [];
		}
	});

	var totalStacksNum = stack.length;
	i = 0;

	jQuery.each(stack, function(index, element) {
		jQuery.postq('bupCreateDbPerQuery', ajaxurl,{
			reqType:  'ajax',
			page:     'backup',
			action:   'createDBDumpPerStack',
			filename: jQuery.parseJSON( dumpFileName ),
			stack: element,
			firstQuery: firstQuery,
			pl: 'ebbs'
		}, function (response) {
			response = jQuery.parseJSON(response);
			i++;

			if(i === totalStacksNum && !response.error){
				jQuery.sendFormEbbs({
					msgElID: 'BUP_MESS_MAIN',
					data: {
						reqType:  'ajax',
						page:     'backup',
						action:   'createAction',
						filesBackupComplete: true,
						databaseBackupComplete: true
					},
					onSuccess: function(response){
						onBackupSuccessfulComplete(response);
					}
				});
			} else if(response.error){
				jQuery('#BUP_MESS_MAIN').addClass('bupErrorMsg').html(response.errors.join('<br>'));
			}
		});

		firstQuery = 0;
	});
}

function sendCompleteRequest() {
    jQuery.sendFormEbbs({
        msgElID: 'EBBS_MESS_MAIN',
        data: {
            reqType:  'ajax',
            page:     'backup',
            action:   'createAction',
            complete: true
        },
        onSuccess: function(response) {
			if(response.data.dbDumpFileName !== undefined && response.data.tables !== undefined && response.data.per_stack !== undefined) {
				createDatabaseBackupPerQuery(response.data.dbDumpFileName, response.data.tables, response.data.per_stack, true);
				return;
			}
			onBackupSuccessfulComplete(response);
        }
    });
}

function onBackupSuccessfulComplete(response){
	if(response.data.backupLog) {
		var logText = response.data.backupLog.join('<br>');
		jQuery('.bupBackupLogContentText').html(logText);
	}

	clearBackupLogAfterComplete();
}

function clearBackupLogAfterComplete(){
	clearInterval(getBackupLogInterval);

	setTimeout(function() {
		jQuery('.bupBackupLog').hide(500);
		jQuery('.bupBackupLogContentText').html('');
		location.reload();
	}, 3000);

	jQuery('#EBBS_SHOW_LOG').hide();
	jQuery('#bupInfo').show();
}

function getBackupLog(){
	jQuery.post(ajaxurl, {
		pl: 'ebbs',
		reqType: 'ajax',
		page: 'backup',
		action: 'getBackupLog'
	}).success(function (response) {
		response = jQuery.parseJSON(response);

		if(response.data.backupLog) {
			var logText = response.data.backupLog.join('<br>');
			jQuery('.bupBackupLogContentText').html(logText);
		}
	});
}

function bupBackupsShowLogDlg() {
	var $container = jQuery('#bupShowLogDlg').dialog({
		modal:    true,
		autoOpen: false,
		width: 1000,
		height: 400
	});
	var j = jQuery.noConflict();

	jQuery('.bupShowLogDlg').click(function($this){
		j('#bupLogText').html('');
		var logContent = j($this.currentTarget).data('log');
		j('#bupLogText').html(logContent);
		$container.dialog('open');
	});
}

function checkBackupType(j) {
	var backupOptions = j('.bupBackupAdvancedSettings .bupCheckbox');
	var fullBackupType = true;
	var backupTargetEmpty = true;
	var backupTypeLabel = [];
	var i = 0;

	j(backupOptions).each(function(index, element){
		var checked = (j(element).attr('checked')) ? true : false;
		var optionLabel = j(j(element).closest('tr')[0]).text();
		if(!checked) {
			fullBackupType = false;
		} else {
			backupTypeLabel[i] = j.trim(optionLabel);
			backupTargetEmpty = false;
			i++;
		}
	});

	backupTypeLabel = backupTypeLabel.join(', ');

	if(!fullBackupType) {
		j('#bupBackupType').html('Partial Backup');
	} else {
		j('#bupBackupType').html('Full Backup');
		backupTypeLabel = 'All files and database';
	}

	if(backupTargetEmpty){
		j('#bupBackupType').html('Empty');
	}

	j('#bupSelectedOptions').html(backupTypeLabel);
}