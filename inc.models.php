<?php

namespace rdx\cronlog\data;

class Model extends \db_generic_model {

	static public function _updates( array $datas ) {
		foreach ( $datas as $id => $data ) {
			if ( static::validate($data) ) {
				if ( $id ) {
					static::find($id)->update($data);
				}
				else {
					static::insert($data);
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

	static public function findByToAndSubject( $address, $subject ) {
		if ( self::$_all === null ) {
			self::$_all = self::all('1');
		}

		foreach ( self::$_all as $type ) {
			if ( $type->matchesToAndSubject($address, $subject) ) {
				return $type;
			}
		}
	}

	public function matchesToAndSubject( $to, $subject ) {
		if ( !$this->to_regex && !$this->subject_regex ) {
			return  false;
		}

		$to = !$this->to_regex || preg_match($this->to_regex, $to);
		$subject = !$this->subject_regex || preg_match($this->subject_regex, $subject);

		return $to &&  $subject;
	}

	protected function get_num_results() {
		return self::$_db->count(Result::$_table, array('type_id' => $this->id));
	}

	protected function get_results() {
		return Result::all('type_id = ? ORDER BY sent DESC', array($this->id));
	}

	protected function get_triggers() {
		return Trigger::all('type_id = ? ORDER BY trigger ASC', array($this->id));
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

	public function __toString() {
		return $this->name;
	}
}

class Result extends Model {
	const TRIGGERS_TABLE = 'results_triggers';

	public static $_table = 'results';

	protected function get_type() {
		if ( $this->type_id ) {
			return Type::find($this->type_id);
		}
	}

	protected function get_server() {
		if ( $this->server_id ) {
			return Server::find($this->server_id);
		}
	}

	protected function get_triggers() {
		return self::$_db->select_by_field(self::TRIGGERS_TABLE, 'trigger_id', array('result_id' => $this->id))->all();
	}

	public function collate() {
		if ( !$this->type ) return;

		if ( !$this->type->triggers ) return;

		self::$_db->delete(self::TRIGGERS_TABLE, array('result_id' => $this->id));

		$inserts = array();
		foreach ( $this->type->triggers as $trigger ) {
			$matches = preg_match_all($trigger->regex, $this->output);
			$inserts[] = array(
				'trigger_id' => $trigger->id,
				'result_id' => $this->id,
				'amount' => $matches,
			);
		}
		self::$_db->inserts(self::TRIGGERS_TABLE, $inserts);
	}

	public function triggeredAmount( $triggerId ) {
		$triggers = $this->triggers;
		if ( isset($triggers[$triggerId]) )  {
			return $triggers[$triggerId]->amount;
		}

		return '?';
	}
}
