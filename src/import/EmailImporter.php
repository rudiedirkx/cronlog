<?php

namespace rdx\cronlog\import;

use rdx\imap\IMAPMessage;

class EmailImporter implements Importer {

	public IMAPMessage $message;

	public function __construct( IMAPMessage $message ) {
		$this->message = $message;
	}

	public function delete() : void {
		$this->message->flag('seen');
	}

	public function getFrom() : ?string {
		return $this->extractAddress($this->message->header('From'));
	}

	public function getTo() : ?string {
		return $this->extractAddress($this->message->header('To'));
	}

	public function getSubject() : ?string {
		return $this->message->header('Subject')[0];
	}

	public function getSentUtc() : ?int {
		return strtotime($this->message->header('Date')[0]);
	}

	public function getOutput() : ?string {
		return trim($this->message->content());
	}

	/**
	 * @param list<string> $header
	 */
	protected function extractAddress( array $header ) : string {
		if (preg_match('#<([^<@]+@[^<@]+)>$#', $header[0], $match)) {
			return $match[1];
		}
		return trim(explode(' ', $header[0])[0], '<>');
	}
}
