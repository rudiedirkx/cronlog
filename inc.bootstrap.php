<?php

use rdx\cronlog\import\EmailImporterCollector;
use rdx\cronlog\import\FileImporterCollector;
use rdx\cronlog\import\ImporterCollector;

chdir(__DIR__);

require 'env.php';
require 'vendor/autoload.php';

header('Content-type: text/plain; charset=utf-8');

$db = db_sqlite::open(array('database' => CRONLOG_DB_DIR . '/cronlog.sqlite3'));

$db->ensureSchema(require 'inc.db-schema.php');

db_generic_model::$_db = $db;

/** @var ImporterCollector[] $importers */
$importers = array_map(function(array $args) {
	return new EmailImporterCollector(...$args);
}, CRONLOG_MAIL_IMPORTERS);
