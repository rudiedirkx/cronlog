<?php

namespace rdx\cronlog\import;

use rdx\imap\IMAPMailbox;

class EmailImporterCollector implements ImporterCollector {

	/** @var array{string, string, string} */
	public array $creds;
	public IMAPMailbox $mbox;

	public function __construct( string $server, string $user, string $pass ) {
		$this->creds = [$server, $user, $pass];
	}

	protected function connect() : IMAPMailbox {
		if ( !isset($this->mbox) ) {
			$this->mbox = new IMAPMailbox($this->creds[0], $this->creds[1], $this->creds[2]);
		}

		return $this->mbox;
	}

	public function collect( ImporterReader $reader ) : void {
		$messages = $this->connect()->messages(array('seen' => false));
		imap_errors();
		imap_alerts();
		foreach ( $messages as $message ) {
			$reader->read(new EmailImporter($message));
		}
	}
}
