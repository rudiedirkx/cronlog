<?php

namespace rdx\cronlog\import;

use rdx\cronlog\UploadedLog;

class UploadedImporterCollector implements ImporterCollector {
	public function collect( ImporterReader $reader ) {
		$uploaded = UploadedLog::all('1=1');
		foreach ( $uploaded as $upload ) {
			$reader->read(new UploadedImporter($upload));
		}
	}
}
