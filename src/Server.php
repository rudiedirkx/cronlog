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

	protected function relate_num_results() {
		return $this->to_count(Result::$_table, 'server_id');
	}

	protected function get_results() {
		return Result::all('server_id = ? ORDER BY sent DESC', [$this->id]);
	}

	protected function get_anominal_results() {
		return Result::all("nominal = '0' AND server_id = ? ORDER BY sent DESC", [$this->id]);
	}

	public function __toString() {
		return $this->name;
	}
}
