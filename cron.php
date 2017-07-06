<?php

use rdx\cronlog\data\Result;
use rdx\cronlog\data\Server;
use rdx\cronlog\data\Type;
use rdx\cronlog\import\Importer;
use rdx\cronlog\import\ImporterReader;

require 'inc.bootstrap.php';

class DbImporterReader implements ImporterReader {
	public function read( Importer $importer ) {
		$from = $importer->getFrom();
		$to = $importer->getTo();
		$subject = $importer->getSubject();
		$sent = date('Y-m-d H:i:s', $importer->getSentUtc());
		$output = $importer->getOutput();

		$insert = compact('from', 'to', 'subject', 'sent', 'output');

		if ( $server = Server::findByFrom($from) ) {
			$insert['server_id'] = $server->id;
		}

		if ( $type = Type::findByToAndSubject($to, $subject) ) {
			$insert['type_id'] = $type->id;
		}

		if ( CRONLOG_DELETE_IMPORTS ) {
			$importer->delete();
		}

		$id = Result::insert($insert);
		$result = Result::find($id);

		$result->collate();
	}
}

header('Content-type: text/plain; charset=utf-8');

$reader = new DbImporterReader;
foreach ( $importers as $importer ) {
	$importer->collect($reader);
}

print_r($db);
