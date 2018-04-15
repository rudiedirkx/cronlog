<?php

namespace rdx\cronlog;

class Result extends Model {
	public static $_table = 'results';

	public function init() {
		if ( $this->nominal !== null ) {
			$this->nominal = (bool) $this->nominal;
		}
	}

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

	protected function get_triggers() {
		return ResultTrigger::all(['result_id' => $this->id], [], ['id' => 'trigger_id']);
	}

	public function delete() {
		ResultTrigger::deleteAll(array('result_id' => $this->id));
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
		$none = [0, 0, 0];
		if ( !$this->type ) return $none;
		if ( !$this->type->triggers ) return $none;
		if ( !$this->output ) return $none;

		self::$_db->begin();

		ResultTrigger::deleteAll(array('result_id' => $this->id));
		$this->triggers = [];

		$inserts = array();
		$nominals = [0, 0];
		foreach ( $this->type->triggers as $trigger ) {
			$matches = preg_match_all($trigger->regex, $this->output);
			$nominal = $trigger->evalNominality($matches, $this);
			if ( $nominal !== null ) {
				$nominals[(int) $nominal]++;
			}

			$inserts[] = array(
				'trigger_id' => $trigger->id,
				'result_id' => $this->id,
				'amount' => $matches,
				'nominal' => $nominal === null ? null : (int) $nominal,
			);
			$this->triggers[$trigger->id] = (object) end($inserts);
		}
		ResultTrigger::insertAll($inserts);

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

		self::$_db->commit();

		return [count($inserts), $nominals[1], $nominals[0]];
	}

	public function triggered( $triggerId ) {
		$triggers = $this->triggers;
		if ( isset($triggers[$triggerId]) )  {
			$trigger = $triggers[$triggerId];
			return [$trigger->amount, $trigger->nominal];
		}

		return ['?', false];
	}
}
