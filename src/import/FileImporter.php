<?php

namespace rdx\cronlog\import;

use Exception;

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
