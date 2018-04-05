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
		if ( $header = $this->message->header('From') ) {
			return $header[0]->mailbox . '@' . $header[0]->host;
		}
	}

	public function getTo() {
		if ( $header = $this->message->header('To') ) {
			return $header[0]->mailbox . '@' . $header[0]->host;
		}
	}

	public function getSubject() {
		return $this->message->header('Subject');
	}

	public function getSentUtc() {
		return strtotime($this->message->header('Date'));
	}

	public function getOutput() {
		return trim($this->message->content());
	}
}
