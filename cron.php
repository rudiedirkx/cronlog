<?php

use rdx\cronlog\Result;
use rdx\cronlog\Type;
use rdx\cronlog\import\Importer;
use rdx\cronlog\import\ImporterReader;

if ( php_sapi_name() !== 'cli' ) {
	echo "Must be run on CLI.\n";
	exit(1);
}

require 'inc.bootstrap.php';

class DbImporterReader implements ImporterReader {
	public $batch;

	public $skipped = 0;
	public $results = 0;
	public $anominals = 0;
	public $triggers = 0;

	public function __construct() {
		$this->batch = time();
	}

	public function read( Importer $importer ) {
		$from = $importer->getFrom();
		$to = $importer->getTo();
		$subject = $importer->getSubject();
		$sent = date('Y-m-d H:i:s', $importer->getSentUtc());
		$output = $importer->getOutput();

		$insert = compact('from', 'to', 'subject', 'sent', 'output');

		$type = Type::findByToAndSubject($to, $subject);
		if ( !$type ) {
			$this->skipped++;
			return false;
		}
		$insert['type_id'] = $type->id;

		if ( CRONLOG_DELETE_IMPORTS ) {
			try {
				$importer->delete();
			}
			catch ( Exception $ex ) {
				return false;
			}
		}

		$insert['batch'] = $this->batch;
		$insert['output_size'] = strlen($output);

		$id = Result::insert($insert);
		$result = Result::find($id);

		list($triggers, , $anominals) = $result->collate();

		$this->results++;
		if ( $anominals > 0 ) {
			$this->anominals++;
		}
		$this->triggers += $triggers;

		return true;
	}
}

header('Content-type: text/plain; charset=utf-8');

set_time_limit(0);

$reader = new DbImporterReader;
foreach ( $importers as $importer ) {
	$importer->collect($reader);
}

$skipped = $reader->skipped ? " ({$reader->skipped} skipped)" : '';

$log  = "";
$log .= "{$reader->results} results{$skipped}\n";
$log .= "{$reader->anominals} of which are anominal\n\n";
$log .= CRONLOG_URI . "results.php?batch=" . $reader->batch . "\n\n";
$log .= "{$reader->triggers} triggers\n";
$log .= count($db->queries) . " queries\n";

if ( CRONLOG_EMAIL_RESULTS ) {
	$subject = "{$reader->results} cron results imported, {$reader->anominals} anominal";
	mail(CRONLOG_EMAIL_RESULTS, $subject, $log, "From: Devver <devver@hotblocks.nl>");
}

echo "$log\n\n";
