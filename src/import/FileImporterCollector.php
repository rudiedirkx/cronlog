<?php

namespace rdx\cronlog\import;

class FileImporterCollector implements ImporterCollector {
	public $dir;
	public $mask;

	public function __construct( $dir, $mask = null ) {
		$this->dir = rtrim($dir, '/\\');
		$this->mask = $mask ?: '*.eml';
	}

	public function collect( ImporterReader $reader ) {
		$files = glob("{$this->dir}/{$this->mask}");
		foreach ( $files as $file ) {
			$reader->read(new FileImporter($file));
		}
	}
}
