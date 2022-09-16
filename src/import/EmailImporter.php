<?php

namespace rdx\cronlog\import;

use rdx\imap\IMAPMessage;

class EmailImporter implements Importer {
	public $message;

	public function __construct( IMAPMessage $message ) {
		$this->message = $message;
	}

	public function delete() {
		$this->message->flag('seen');
	}

	public function getFrom() {
		return $this->extractAddress($this->message->header('From'));
	}

	public function getTo() {
		return $this->extractAddress($this->message->header('To'));
	}

	public function getSubject() {
		return $this->message->header('Subject')[0];
	}

	public function getSentUtc() {
		return strtotime($this->message->header('Date')[0]);
	}

	public function getOutput() {
		return trim($this->message->content());
	}

	protected function extractAddress($header) {
		if (preg_match('#<([^<@]+@[^<@]+)>$#', $header[0], $match)) {
			return $match[1];
		}
		return trim(explode(' ', $header[0])[0], '<>');
	}
}
