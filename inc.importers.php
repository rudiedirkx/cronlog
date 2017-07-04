<?php

interface ImporterReader {
	public function read( Importer $importer );
}

interface ImporterCollector {
	public function collect( ImporterReader $reader );
	public function createImporter( $data );
}

class FileImporterCollector implements ImporterCollector {
	public $dir;

	public function __construct( $dir ) {
		$this->dir = rtrim($dir, '/\\');
	}

	public function collect( ImporterReader $reader ) {
		foreach ( glob("{$this->dir}/*.eml") as $file ) {
			$importer = $this->createImporter($file);
			$reader->read($importer);
		}
	}

	public function createImporter( $file ) {
		return new FileImporter($file);
	}
}

interface Importer {
	public function getFrom();
	public function getTo();
	public function getSubject();
	public function getOutput();
}

class FileImporter implements Importer {
	public $file;
	public $content;

	public function __construct( $file ) {
		$this->file = $file;
	}

	protected function read() {
		if ( !$this->content ) {
			$this->content = file_get_contents($this->file);
		}
	}

	public function getFrom() {
		$this->read();

		if ( preg_match('#(?:^|\s)From: (.+)#', $this->content, $match) ) {
			$value = trim($match[1]);

			if ( preg_match('#([^ ]+@[^ ]+)#', $value, $match) ) {
				return $match[1];
			}
		}
	}

	public function getTo() {
		$this->read();

		if ( preg_match('#(?:^|\s)To: (.+)#', $this->content, $match) ) {
			$value = trim($match[1]);

			if ( preg_match('#([^ ]+@[^ ]+)#', $value, $match) ) {
				return $match[1];
			}
		}
	}

	public function getSubject() {
		$this->read();

		if ( preg_match('#Subject:(.+)#', $this->content, $match) ) {
			return trim($match[1]);
		}
	}

	public function getOutput() {
		$this->read();

		$pos = strpos($this->content, "\r\n\r\n");
		return trim(substr($this->content, $pos));
	}
}
