<?php

namespace rdx\cronlog;

class Result extends Model {
	const TRIGGERS_TABLE = 'results_triggers';

	public static $_table = 'results';

	protected function get_compare_info() {
		$subject = $this->relevant_subject ? " ({$this->relevant_subject})" : '';
		return "{$this->sent_time}{$subject}";
	}

	protected function get_sent_utc() {
		return strtotime($this->sent);
	}

	protected function get_sent_time() {
		return date('H:i', $this->sent_utc);
	}

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

	public function retype() {
		$type = Type::findByToAndSubject($this->to, $this->subject);
		if ( $type ) {
			$this->update(['type_id' => $type->id]);
			$this->type = $type;
			return true;
		}

		return false;
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
