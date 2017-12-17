<?php

namespace rdx\cronlog\import;

use Exception;
use rdx\imap\IMAPMailbox;
use rdx\imap\IMAPMessage;

interface ImporterReader {
	public function read( Importer $importer );
}

interface ImporterCollector {
	public function collect( ImporterReader $reader );
	public function createImporter( $data );
}

class EmailImporterCollector implements ImporterCollector {
	public $creds;
	public $mbox;

	public function __construct( $server, $user, $pass ) {
		$this->creds = [$server, $user, $pass];
	}

	protected function connect() {
		if ( !$this->mbox ) {
			$this->mbox = new IMAPMailbox($this->creds[0], $this->creds[1], $this->creds[2], null, ['norsh']);
		}

		return $this->mbox;
	}

	public function collect( ImporterReader $reader ) {
		$messages = $this->connect()->messages(array('seen' => false, 'limit' => 50));
		foreach ( $messages as $message ) {
			$importer = $this->createImporter($message);
			$reader->read($importer);
		}
	}

	public function createImporter( $message ) {
		return new EmailImporter($message);
	}
}

class FileImporterCollector implements ImporterCollector {
	public $dir;
	public $mask;

	public function __construct( $dir, $mask = null ) {
		$this->dir = rtrim($dir, '/\\');
		$this->mask = $mask ?: '*.eml';
	}

	public function collect( ImporterReader $reader ) {
		$files = glob("{$this->dir}/{$this->mask}");
		foreach ( $files as $file ) {
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
