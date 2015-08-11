!function(a){var b={},c={};a.ajaxq=function(d,e){function i(a){if(b[d])b[d].push(a);else{b[d]=[];var e=a();c[d]=e}}function j(){if(b[d]){var a=b[d].shift();if(a){var e=a();c[d]=e}else delete b[d]}}if("undefined"==typeof e)throw"AjaxQ: queue name is not provided";var f=a.Deferred(),g=f.promise();g.success=g.done,g.error=g.fail,g.complete=g.always;var h=a.extend(!0,{},e);return i(function(){var b=a.ajax.apply(window,[h]);return b.done(function(){f.resolve.apply(this,arguments)}),b.fail(function(){f.reject.apply(this,arguments)}),b.always(j),b}),g},a.each(["getq","postq"],function(b,c){a[c]=function(b,d,e,f,g){return a.isFunction(e)&&(g=g||f,f=e,e=void 0),a.ajaxq(b,{type:"postq"===c?"post":"get",url:d,data:e,success:f,dataType:g})}});var d=function(a){return b.hasOwnProperty(a)},e=function(){for(var a in b)if(d(a))return!0;return!1};a.ajaxq.isRunning=function(a){return a?d(a):e()},a.ajaxq.getActiveRequest=function(a){if(!a)throw"AjaxQ: queue name is required";return c[a]},a.ajaxq.abort=function(b){if(!b)throw"AjaxQ: queue name is required";a.ajaxq.clear(b);var c=a.ajaxq.getActiveRequest(b);c&&c.abort()},a.ajaxq.clear=function(a){if(a)b[a]&&delete b[a];else for(var c in b)b.hasOwnProperty(c)&&delete b[c]}}(jQuery);

jQuery(document).ready(function($) {
	var j = jQuery.noConflict();
	bupBackupsShowLogDlg();
	checkBackupType(j);

	j('.bupShowBackupAdvancedOptions').click(function() {
		j('.bupBackupAdvancedSettings').toggle(500);
		j('.bupSelectedOptionsWrapper').toggle(500);
	});

	j('.bupShowBackupAdvancedOptions').toggle(
		function(){
			j(this).html('Hide Advanced Options');
		},
		function(){
			j(this).html('Show Advanced Options');
		}
	);

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
		jQuery('.cspAdminOptionRow').hide(500);
		jQuery('#bupInfo').hide();
		var backupLog = jQuery('.bupBackupLogContentText');

		jQuery('.bupBackupLog').show(500);
		backupLog.html('');
		jQuery('#bupBackupStatusPercent').html('');

		jQuery('#EBBS_MESS_MAIN').addClass('bupSuccessMsg').html('Backup in process!');

		jQuery(form).sendFormEbbs({
			onSuccess: function(response) {
				if(response.data.backupLog != undefined) {
					backupLog.html(response.data.backupLog);
				}

				if(response.data.backupComplete) {
					BackupModule.onBackupComplete();
					return;
				}

				var backupId = response.data.backupId;
				var refreshLog = setInterval(function () {
					jQuery.post(ajaxurl, {
						pl: 'ebbs',
						reqType: 'ajax',
						page: 'backup',
						action: 'getBackupLog',
						backupId: backupId
					}).success(function (response) {
						response = jQuery.parseJSON(response);

						if(response.data.backupLog != undefined) {
							backupLog.html(response.data.backupLog);
						}

						if(response.data.backupMessage != undefined && response.data.backupMessage) {
							jQuery('#EBBS_MESS_MAIN').addClass('bupSuccessMsg').html(response.data.backupMessage);
						}

						//if(response.data.uploadedPercent != undefined && response.data.uploadedPercent) {
						//	jQuery('#bupBackupStatusPercent').html(response.data.uploadedPercent + '%');
						//}

						if (response.data.backupComplete) {
							clearInterval(refreshLog);
							BackupModule.onBackupComplete();
						}
					});
				}, 2000);
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
	},
	onBackupComplete: function(){
		jQuery('#EBBS_MESS_MAIN').removeClass('bupErrorMsg').addClass('bupSuccessMsg').html('Backup complete');
		setTimeout(function(){
			//jQuery('.bupBackupLog').hide(500);
			//jQuery('.cspAdminOptionRow').show(500);
			//backupLog.html('');

			location.reload();
		}, 5000);


		//jQuery.sendFormEbbs({
		//	data: {
		//		'page':    'backup', // Module
		//		'action':  'getBackupsListContentAjax', // Action
		//		'reqType': 'ajax'         // Request type
		//	},
		//	onSuccess: function(response) {
		//		if(response.data.content)
		//			jQuery('#bupBackupWrapper').html(response.data.content);
		//	}
		//});
	}
};

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