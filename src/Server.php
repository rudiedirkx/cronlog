<?php

namespace rdx\cronlog;

class Server extends Model {
	public static $_table = 'servers';

	protected static $_all;

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

	protected function get_num_results() {
		return self::$_db->count(Result::$_table, array('server_id' => $this->id));
	}

	protected function get_results() {
		return Result::all('server_id = ? ORDER BY sent DESC', array($this->id));
	}

	public function __toString() {
		return $this->name;
	}
}
