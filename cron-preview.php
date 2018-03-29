<?php

use rdx\cronlog\import\Importer;
use rdx\cronlog\import\ImporterReader;

require 'inc.bootstrap.php';

class PreviewImporterReader implements ImporterReader {

	public $results = [];

	public function read( Importer $importer ) {
		$from = $importer->getFrom();
		$to = $importer->getTo();
		$subject = $importer->getSubject();
		$sent = date('Y-m-d H:i:s', $importer->getSentUtc());

		$type = Type::findByToAndSubject($to, $subject);
		if ( !$type ) {
			return;
		}

		$this->results[] = [$sent, $from, $to, $subject, $type];
	}

}

$reader = new PreviewImporterReader;
foreach ( $importers as $importer ) {
	$importer->collect($reader);
}

print_r($reader->results);
