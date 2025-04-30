<?php

namespace rdx\cronlog\import;

class FileImporterCollector implements ImporterCollector {

	public string $dir;
	public ?string $mask;

	public function __construct( string $dir, ?string $mask = null ) {
		$this->dir = rtrim($dir, '/\\');
		$this->mask = $mask ?: '*.eml';
	}

	public function collect( ImporterReader $reader ) : void {
		$files = glob("{$this->dir}/{$this->mask}");
		foreach ( $files as $file ) {
			$reader->read(new FileImporter($file));
		}
	}
}
