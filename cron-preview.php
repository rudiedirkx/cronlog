<?php

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

		$type = Type::findByToAndSubject($to, $subject);
		if ( !$type ) {
			$this->ignored[] = [
				'to' => $to,
				'subject' => $subject,
			];
			return;
		}

		$this->results[] = [
			'type' => $type->description,
			'when' => $sent,
			'from' => $from,
			'to' => $to,
			'subject' => $subject,
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
