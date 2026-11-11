<?php

use rdx\cronlog\import\EmailImporterCollector;
use rdx\cronlog\import\ImporterCollector;
use rdx\cronlog\import\UploadedImporterCollector;

function html( mixed $text ) : string {
	return htmlspecialchars((string) $text, ENT_QUOTES, 'UTF-8') ?: htmlspecialchars((string) $text, ENT_QUOTES, 'ISO-8859-1');
}

function get_datetime( string $datetime ) : string {
	$date = substr($datetime, 0, 10);
	$time = substr($datetime, 11);
	if ($date == date('Y-m-d')) {
		return date('l j') . ' &nbsp; ' . $time;
	}
	elseif ($date >= date('Y-m-d', strtotime('-3 days'))) {
		return date('l j', strtotime($date)) . ' &nbsp; ' . $time;
	}

	return "$date &nbsp; $time";
}

function do_redirect( ?string $uri = null ) : never {
	$uri or $uri = get_url();
	header("Location: " . $uri);
	exit;
}

function get_url() : string {
	$scheme = ($_SERVER['HTTPS'] ?? '') === 'on' ? 'https://' : 'http://';
	$host = $_SERVER['HTTP_HOST'];
	$uri = $_SERVER['REQUEST_URI'];
	return $scheme . $host . $uri;
}

/**
 * @param array<int|string, string|int|float|Stringable|null> $options
 * @param int|string|bool|list<int|string>|null $selected
 */
function html_options( array $options, int|string|bool|array|null $selected = null, string $empty = '' ) : string {
	$selected = (array) $selected;

	$html = '';
	$empty && $html .= '<option value="">' . $empty;
	foreach ( $options AS $value => $label ) {
		$isSelected = in_array($value, $selected) ? ' selected' : '';
		$html .= '<option value="' . html($value) . '"' . $isSelected . '>' . html($label) . '</option>';
	}
	return $html;
}

function html_number( int|float $number ) : string {
	return number_format($number, 0, '.', '_');
}

function db() : db_generic {
	return $GLOBALS['db'];
}

/**
 * @return list<ImporterCollector>
 */
function importers() : array {
	$importers = array_map(function(array $args) : EmailImporterCollector {
		return new EmailImporterCollector(...$args);
	}, CRONLOG_MAIL_IMPORTERS);
	$importers[] = new UploadedImporterCollector();
	return $importers;
}
