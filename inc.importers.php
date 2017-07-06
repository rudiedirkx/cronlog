<?php

namespace rdx\cronlog\import;

use Exception;

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
	public function delete();

	public function getFrom();
	public function getTo();
	public function getSubject();
	public function getSentUtc();
	public function getOutput();
}

// @todo EMAIL IMPORTER

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

	protected function getHeader( $name ) {
		$this->read();

		if ( preg_match("#(?:^|\s){$name}:(.+)#i", $this->content, $match) ) {
			return trim($match[1]);
		}
	}

	public function delete() {
		if ( !@unlink($this->file) ) {
			throw new Exception("`FileImporter` can't delete `{$this->file}`");
		}
	}

	public function getFrom() {
		if ( $value = $this->getHeader('From') ) {
			if ( preg_match('#([^ ]+@[^ ]+)#', $value, $match) ) {
				return $match[1];
			}
		}
	}

	public function getTo() {
		if ( $value = $this->getHeader('To') ) {
			if ( preg_match('#([^ ]+@[^ ]+)#', $value, $match) ) {
				return $match[1];
			}
		}
	}

	public function getSubject() {
		if ( $value = $this->getHeader('Subject') ) {
			return $value;
		}
	}

	public function getSentUtc() {
		if ( $value = $this->getHeader('Date') ) {
			return strtotime($value);
		}
	}

	public function getOutput() {
		$this->read();

		$pos = strpos($this->content, "\r\n\r\n");
		return trim(substr($this->content, $pos));
	}
}
