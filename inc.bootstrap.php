<?php

require 'env.php';

define('CRONLOG_DB_DIR', __DIR__ . '/db');

require WHERE_DB_GENERIC_AT . '/db_sqlite.php';

$db = db_sqlite::open(array('database' => CRONLOG_DB_DIR . '/cronlog.sqlite3'));

$schema = require 'inc.db-schema.php';
require 'inc.ensure-db-schema.php';

require 'inc.functions.php';

db_generic_model::$_db = $db;

class Model extends db_generic_model {

	static public function _updates( array $datas ) {
		foreach ( $datas as $id => $data ) {
			if ( static::validate($data) ) {
				if ( $id ) {
					static::find($id)->update($data);
				}
				else {
					static::$_db->insert(static::$_table, $data);
				}
			}
			else {
				if ( $id ) {
					static::find($id)->delete($data);
				}
			}
		}
	}

	static public function validate( array $data ) {
		return true;
	}

}

class Type extends Model {
	public static $_table = 'types';

	static public function validate( array $data ) {
		self::presave($data);
		return !empty($data['type']);
	}

	public function __toString() {
		return (string) $this->type;
	}
}

class Trigger extends Model {
	public static $_table = 'triggers';

	static public function validate( array $data ) {
		self::presave($data);
		return !empty($data['trigger']) && !empty($data['regex']);
	}
}
