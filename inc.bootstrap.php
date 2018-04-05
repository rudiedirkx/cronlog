<?php

use rdx\cronlog\import\EmailImporterCollector;
use rdx\cronlog\import\FileImporterCollector;

chdir(__DIR__);

require 'env.php';
require 'vendor/autoload.php';

$db = db_sqlite::open(array('database' => CRONLOG_DB_DIR . '/cronlog.sqlite3'));

require 'inc.ensure-db-schema.php';

db_generic_model::$_db = $db;

$importers = array(
	// new FileImporterCollector(__DIR__ . '/input', 'y-*.eml'),
	new EmailImporterCollector(CRONLOG_MAIL_SERVER, CRONLOG_MAIL_USER, CRONLOG_MAIL_PASS),
);
