<?php

class Model extends db_generic_model {

	static public function _updates( array $datas ) {
		foreach ( $datas as $id => $data ) {
			if ( static::validate($data) ) {
				if ( $id ) {
					static::find($id)->update($data);
				}
				else {
					static::insert(static::$_table, $data);
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

	public static $_all;

	static public function validate( array $data ) {
		self::presave($data);
		return !empty($data['type']);
	}

	static public function findByTo( $address ) {
		if ( self::$_all === null ) {
			self::$_all = self::all('1');
		}

		foreach ( self::$_all as $type ) {
			if ( $type->to_regex && preg_match($type->to_regex, $address) ) {
				return $type;
			}
		}
	}

	protected function get_num_results() {
		return self::$_db->count(Result::$_table, array('type_id' => $this->id));
	}

	protected function get_results() {
		return Result::all('type_id = ? ORDER BY id DESC', array($this->id));
	}

	public function __toString() {
		return (string) $this->type;
	}
}

class Trigger extends Model {
	public static $_table = 'triggers';

	static public function validate( array $data ) {
		self::presave($data);
		return !empty($data['trigger']);
	}
}

class Server extends Model {
	public static $_table = 'servers';

	public static $_all;

	static public function validate( array $data ) {
		self::presave($data);
		return !empty($data['name']);
	}

	static public function findByFrom( $address ) {
		if ( self::$_all === null ) {
			self::$_all = self::all('1');
		}

		foreach ( self::$_all as $server ) {
			if ( $server->from_regex && preg_match($server->from_regex, $address) ) {
				return $server;
			}
		}
	}
}

class Result extends Model {
	public static $_table = 'results';
}
