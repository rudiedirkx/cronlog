<?php

$db->execute('PRAGMA foreign_keys = ON');

if ( $db->needsSchemaUpdate($schema) ) {
	try {
		$db->schema($schema);
		$db->setSchemaVersion($schema['version']);
	}
	catch (db_exception $ex) {
		echo '<pre>';
		echo "ERROR: " . $ex->getMessage() . "\n\n";
		echo "QUERY: " . $ex->query . "\n\n";
		exit((string) $ex);
	}
}
