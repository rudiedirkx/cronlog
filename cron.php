<?php

require 'inc.bootstrap.php';

class DbImporterReader implements ImporterReader {
	public function read( Importer $importer ) {
		$from = $importer->getFrom();
		$to = $importer->getTo();
		$subject = $importer->getSubject();
		$output = $importer->getOutput();

		$insert = compact('from', 'to', 'subject', 'output');

		if ( $server = Server::findByFrom($from) ) {
			$insert['server_id'] = $server->id;
		}

		if ( $type = Type::findByTo($to) ) {
			$insert['type_id'] = $type->id;
		}

		Result::insert($insert);
	}
}

header('Content-type: text/plain; charset=utf-8');

$reader = new DbImporterReader;

foreach ( $importers as $importer ) {
	$importer->collect($reader);
}
