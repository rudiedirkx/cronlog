<?php

chdir(__DIR__);

require 'env.php';
require 'vendor/autoload.php';

// header('Content-type: text/plain; charset=utf-8');

$db = new db_sqlite(CRONLOG_DB_DIR . '/cronlog.sqlite3');

$db->ensureSchema(require 'inc.db-schema.php');

db_generic_model::$_db = $db;
