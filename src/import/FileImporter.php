<?php

namespace rdx\cronlog\import;

use Exception;

class FileImporter implements Importer {

	public string $file;
	public ?string $content = null;

	public function __construct( string $file ) {
		$this->file = $file;
	}

	protected function read() : void {
		if ( !$this->content ) {
			$this->content = file_get_contents($this->file);
		}
	}

	protected function getHeader( string $name ) : ?string {
		$this->read();

		if ( preg_match("#(?:^|\s){$name}:(.+)#i", $this->content, $match) ) {
			return trim($match[1]);
		}
		return null;
	}

	public function delete() : void {
		if ( !@unlink($this->file) ) {
			throw new Exception("`FileImporter` can't delete `{$this->file}`");
		}
	}

	public function getFrom() : ?string {
		if ( $value = $this->getHeader('From') ) {
			if ( preg_match('#([^ ]+@[^ ]+)#', $value, $match) ) {
				return $match[1];
			}
		}
		return null;
	}

	public function getTo() : ?string {
		if ( $value = $this->getHeader('To') ) {
			if ( preg_match('#([^ ]+@[^ ]+)#', $value, $match) ) {
				return $match[1];
			}
		}
		return null;
	}

	public function getSubject() : ?string {
		if ( $value = $this->getHeader('Subject') ) {
			return $value;
		}
		return null;
	}

	public function getSentUtc() : ?int {
		if ( $value = $this->getHeader('Date') ) {
			return strtotime($value);
		}
		return null;
	}

	public function getOutput() : ?string {
		$this->read();

		$pos = strpos($this->content, "\r\n\r\n");
		return trim(substr($this->content, $pos));
	}
}
