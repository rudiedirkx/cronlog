<?php

namespace rdx\cronlog\import;

use rdx\imap\IMAPMailbox;

class EmailImporterCollector implements ImporterCollector {
	public $creds;
	public $mbox;

	public function __construct( $server, $user, $pass ) {
		$this->creds = [$server, $user, $pass];
	}

	protected function connect() {
		if ( !$this->mbox ) {
			$this->mbox = new IMAPMailbox($this->creds[0], $this->creds[1], $this->creds[2]);
		}

		return $this->mbox;
	}

	public function collect( ImporterReader $reader ) {
		$messages = $this->connect()->messages(array('seen' => false));
		$ignore = imap_errors();
		$ignore = imap_alerts();
		foreach ( $messages as $message ) {
			$reader->read(new EmailImporter($message));
		}
	}
}
