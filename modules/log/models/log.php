<?php

class logModelEbbs extends modelEbbs {

	/**
	 * Returns all finded log files
	 * @return array
	 */
	public function getFilesList() {
		$path    = frameEbbs::_()->getModule('warehouse')->getPath() . DIRECTORY_SEPARATOR;
		$files   = array();
		$matches = array();

		$nodes = @scandir($path);

        if (!is_array($nodes) || empty($nodes)) {
            return $files;
        }

		foreach ($nodes as $node) {
			if (preg_match('/([\d]+).txt/', $node, $matches)) {

                $backupInfo = $this->getBackupInfoByFilename($node, true);
				$content = htmlspecialchars(file_get_contents($path . $node));
				$linesArray = preg_split('/\n|\r/', $content);
                $settings = !empty($linesArray[0]) ?substr($linesArray[0], strpos($linesArray[0], ']') + 1) : __('Settings not found!', EBBS_LANG_CODE);
				$lines = count($linesArray);

				$files[$backupInfo['id']] = array(
					'filepath'  => $path . $node,
					'filename'  => $node,
					'backup_id' => $matches[1],
					'lines'     => $lines,
					'content'   => $content,
                    'settings'  => $settings,
				);
			}
		}
		krsort($files);
		return $files;
	}

}
