<?php

use rdx\cronlog\DbImporterReader;

if ( php_sapi_name() !== 'cli' ) {
	echo "Must be run on CLI.\n";
	exit(1);
}

require 'inc.bootstrap.php';

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
$log .= "{$reader->anominals} anominal,\n";
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
	$subject = "{$reader->results} cron results imported, {$reader->anominals} anominal";
	DbImporterReader::sendMail($subject, $log);
}

// echo "\n";
echo "$log\n\n";
