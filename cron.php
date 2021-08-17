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

	public $skipped = [];
	public $results = 0;
	public $anominals = 0;
	public $notifications = 0;
	public $triggers = 0;

	public function __construct() {
		$this->batch = time();
	}

	public function read( Importer $importer ) {
		$from = $importer->getFrom();
		$to = $importer->getTo();
		$subject = $importer->getSubject();
		$sent = date('Y-m-d H:i:s', $importer->getSentUtc());
// echo "[$sent] $subject\n";
		$output = $importer->getOutput();

		$insert = compact('from', 'to', 'subject', 'sent', 'output');

		$type = Type::findByToAndSubject($to, $subject);
		if ( !$type ) {
			$this->skipped[] = $subject;
			return false;
		}
		$insert['type_id'] = $type->id;

		$insert['batch'] = $this->batch;
		$insert['output_size'] = strlen($output);

		$id = Result::insert($insert);
		$result = Result::find($id);

		list($triggers, , $anominals) = $result->collate();

		$this->results++;
		if ( $anominals > 0 ) {
			$this->anominals++;

			if ( $type->handling_notify ) {
				$this->notifications++;
			}
		}
		$this->triggers += $triggers;

		if ( CRONLOG_DELETE_IMPORTS ) {
			try {
				$importer->delete();
			}
			catch ( Exception $ex ) {
				return false;
			}
		}

		return true;
	}
}

header('Content-type: text/plain; charset=utf-8');

set_time_limit(0);

$reader = new DbImporterReader;
foreach ( $importers as $importer ) {
	$importer->collect($reader);
}

$skipped = count($reader->skipped) ? " (" . count($reader->skipped) . " skipped)" : '';

$yesterday = $db->select_fields('results', 'batch, count(1)', 'batch < ? group by batch order by batch desc limit 1', $reader->batch);
[$yesterdayBatch, $yesterdayNum] = [key($yesterday), current($yesterday)];
$ydiff = $reader->results - $yesterdayNum;

$log  = "";
$log .= "{$reader->results} results{$skipped},\n";
$log .= "{$reader->notifications}/{$reader->anominals} anominal,\n";
$log .= ($ydiff == 0 ? 'same as' : ($ydiff > 0 ? '+' : '-') . abs($ydiff) . ' from') . " yesterday\n\n";
$log .= CRONLOG_URI . "/results.php?batch=" . $reader->batch . ($reader->anominals ? '&anominal=1' : '') . "\n\n";
$log .= CRONLOG_URI . "/results-compare.php?source=batch&date1=" . $reader->batch . '&date2=' . $yesterdayBatch . "\n\n";
$log .= "{$reader->triggers} triggers,\n";
$log .= count($db->queries) . " queries\n";

if ( count($reader->skipped) ) {
	$log .= "\n";
	foreach ( $reader->skipped as $subject ) {
		$log .= "- $subject\n";
	}
}

if ( CRONLOG_EMAIL_RESULTS ) {
	$subject = "{$reader->results} cron results imported, {$reader->notifications}/{$reader->anominals} anominal";
	mail(CRONLOG_EMAIL_RESULTS, $subject, $log, "From: Devver <devver@hotblocks.nl>");
}

// echo "\n";
echo "$log\n\n";
