<?php

namespace rdx\cronlog;

class Server extends Model {
	public static $_table = 'servers';

	/** @var list<self> */
	protected static array $_all;

	static public function validate( array $data ) : bool {
		self::presave($data);
		return !empty($data['name']);
	}

	static public function findByFrom( string $address ) : ?self {
		self::$_all ??= self::all('1');

		foreach ( self::$_all as $server ) {
			if ( $server->from_regex && preg_match($server->from_regex, $address) ) {
				return $server;
			}
		}
		return null;
	}

	protected function relate_num_results() {
		return $this->to_count(Result::$_table, 'server_id');
	}

	/**
	 * @return Result[]
	 */
	protected function get_results() {
		return Result::all('server_id = ? ORDER BY sent DESC', [$this->id]);
	}

	/**
	 * @return Result[]
	 */
	protected function get_anominal_results() {
		return Result::all("nominal = '0' AND server_id = ? ORDER BY sent DESC", [$this->id]);
	}

	public function __toString() {
		return $this->name;
	}
}
