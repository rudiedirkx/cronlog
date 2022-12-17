<?php

use rdx\cronlog\Server;
use rdx\cronlog\Type;
use rdx\cronlog\import\Importer;
use rdx\cronlog\import\ImporterReader;

require 'inc.bootstrap.php';

class PreviewImporterReader implements ImporterReader {

	public $results = [];
	public $ignored = [];

	public function read( Importer $importer ) {
		$from = $importer->getFrom();
		$to = $importer->getTo();
		$subject = $importer->getSubject();
		$sent = date('Y-m-d H:i:s', $importer->getSentUtc());

		$server = Server::findByFrom($from);

		$type = Type::findBySubject($subject);
		if ( !$type ) {
			$this->ignored[] = [
				'when' => $sent,
				'from' => $from,
				'to' => $to,
				'subject' => $subject,
				'server' => (string) $server,
			];
			return;
		}

		$this->results[] = [
			'type' => $type->description,
			'when' => $sent,
			'from' => $from,
			'to' => $to,
			'subject' => $subject,
			'server' => (string) $server,
		];
	}

}

header('Content-type: text/plain; charset=utf-8');

$reader = new PreviewImporterReader;
foreach ( $importers as $importer ) {
	$importer->collect($reader);
}

print_r($reader->results);
print_r($reader->ignored);
