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

	static public function presave( &$data ) {
		parent::presave($data);

		$data['handling'] = 0;
		empty($data['handling_delete']) or $data['handling'] += 1;
		empty($data['handling_notify']) or $data['handling'] += 2;
		unset($data['handling_delete'], $data['handling_notify']);
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

	protected function get_handling_delete() {
		return ($this->handling & 1) > 0;
	}

	protected function get_handling_notify() {
		return ($this->handling & 2) > 0;
	}

	protected function get_trigger_ids() {
		return self::$_db->select_fields(Trigger::TYPES_TABLE, 'trigger_id', array('type_id' => $this->id));
	}

	protected function get_num_results() {
		return self::$_db->count(Result::$_table, array('type_id' => $this->id));
	}

	protected function get_results() {
		return Result::all('type_id = ? ORDER BY sent DESC', array($this->id));
	}

	protected function get_triggers() {
		return $this->trigger_ids ? Trigger::all('id IN (?) ORDER BY o, trigger', array($this->trigger_ids)) : array();
	}

	public function __toString() {
		return (string) $this->type;
	}
}

class Trigger extends Model {
	const TYPES_TABLE = 'triggers_types';

	public static $_table = 'triggers';

	protected function get_js_regex() {
		return $this->regex[0] === '/';
	}

	protected function get_type_ids() {
		return self::$_db->select_fields(Trigger::TYPES_TABLE, 'type_id', array('trigger_id' => $this->id));
	}

	static public function presave( &$data ) {
		parent::presave($data);
	}

	static public function validate( array $data ) {
		self::presave($data);
		return !empty($data['trigger']);
	}

	static public function setTypes( $id, $types ) {
		if ( $types !== null ) {
			self::$_db->delete(Trigger::TYPES_TABLE, array('trigger_id' => $id));
			$inserts = array_map(function($type) use ($id) {
				return array('trigger_id' => $id, 'type_id' => $type);
			}, array_filter((array) $types));
			return self::$_db->inserts(Trigger::TYPES_TABLE, $inserts);
		}
	}

	public function evalNominality( $matches ) {
		if ( trim($this->expect) === '' ) return null;

		$expect = $this->expect;
		$num = trim($expect, '<>');

		if ( $expect[0] === '<' ) {
			return $matches < $num;
		}
		elseif ( $expect[0] === '>' ) {
			return $matches > $num;
		}

		return $matches == $num;
	}

	public function update( $data ) {
		$types = null;
		if ( is_array($data) ) {
			$types = @$data['type_ids'];
			unset($data['type_ids']);
		}

		$ok = parent::update($data);
		self::setTypes($this->id, $types);

		return $ok;
	}

	static public function insert( array $data ) {
		$types = @$data['type_ids'];
		unset($data['type_ids']);

		$id = parent::insert($data);
		self::setTypes($id, $types);

		return $id;
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

	protected function get_relevant_subject() {
		return $this->subject_subject;
	}

	protected function get_subject_subject() {
		if ( $this->type->subject_regex && preg_match($this->type->subject_regex, $this->subject, $match) ) {
			if ( isset($match[1]) ) {
				return $match[1];
			}
		}
	}

	protected function get_generic_subject() {
		return trim(preg_replace('#^Cron\s+<.+?>\s+#i', '', $this->subject));
	}

	protected function get_type() {
		return Type::find($this->type_id);
	}

	protected function get_server() {
		if ( $this->server_id ) {
			return Server::find($this->server_id);
		}
	}

	protected function get_is_nominal() {
		return $this->nominal === null ? null : (bool) $this->nominal;
	}

	protected function get_triggers() {
		return self::$_db->select_by_field(self::TRIGGERS_TABLE, 'trigger_id', array('result_id' => $this->id))
			->map(function($record) {
				$record->is_nominal = $record->nominal === null ? null : (bool) $record->nominal;
				return $record;
			})
			->all();
	}

	public function delete() {
		self::$_db->delete(Result::TRIGGERS_TABLE, array('result_id' => $this->id));
		return parent::delete();
	}

	public function collate() {
		if ( !$this->type ) return [0, 0, 0];
		if ( !$this->type->triggers ) return [0, 0, 0];
		if ( !$this->output ) return [0, 0, 0];

		self::$_db->delete(self::TRIGGERS_TABLE, array('result_id' => $this->id));

		$inserts = array();
		$nominals = [0, 0];
		foreach ( $this->type->triggers as $trigger ) {
			$matches = preg_match_all($trigger->regex, $this->output);
			$nominal = $trigger->evalNominality($matches);
			if ( $nominal !== null ) {
				$nominals[(int) $nominal]++;
			}

			$inserts[] = array(
				'trigger_id' => $trigger->id,
				'result_id' => $this->id,
				'amount' => $matches,
				'nominal' => $nominal === null ? null : (int) $nominal,
			);
		}
		self::$_db->inserts(self::TRIGGERS_TABLE, $inserts);

		$nominal = !$nominals[0] && !$nominals[1] ? null : (int) ($nominals[0] == 0);

		$update = [
			'nominal' => $nominal,
		];

		if ( $server = Server::findByFrom($this->from) ) {
			$update['server_id'] = $server->id;
		}

		if ( $nominal === 1 && $this->type->handling_delete ) {
			$update['output'] = '';
		}

		$this->update($update);

		return [count($inserts), $nominals[1], $nominals[0]];
	}

	public function triggered( $triggerId ) {
		$triggers = $this->triggers;
		if ( isset($triggers[$triggerId]) )  {
			$trigger = $triggers[$triggerId];
			return [$trigger->amount, $trigger->is_nominal];
		}

		return ['?', false];
	}
}
