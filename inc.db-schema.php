<?php

return array(
	'version' => 18,
	'tables' => array(
		'types' => array(
			'columns' => array(
				'id' => array('pk' => true),
				'type',
				'description',
				'to_regex',
				'subject_regex',
				'handling' => array('type' => 'int', 'default' => 0),
			),
			'indexes' => array(
				'type' => array(
					'unique' => true,
					'columns' => array('type'),
				),
			),
		),
		'triggers' => array(
			'columns' => array(
				'id' => array('pk' => true),
				'trigger',
				'description',
				'regex',
				'expect',
				'color',
				'o' => array('type' => 'int', 'default' => 0),
			),
			'indexes' => array(
				'trigger' => array(
					'unique' => true,
					'columns' => array('trigger'),
				),
			),
		),
		'triggers_types' => array(
			'columns' => array(
				'trigger_id' => array('type' => 'int', 'references' => array('triggers', 'id', 'cascade')),
				'type_id' => array('type' => 'int', 'references' => array('types', 'id', 'cascade')),
			),
			'indexes' => array(
				'trigger_type' => array(
					'unique' => true,
					'columns' => array('trigger_id', 'type_id'),
				),
			),
		),
		'servers' => array(
			'columns' => array(
				'id' => array('pk' => true),
				'name',
				'from_regex',
			),
		),
		'results' => array(
			'columns' => array(
				'id' => array('pk' => true),
				'type_id' => array('type' => 'int', 'references' => array('types', 'id', 'cascade')),
				'server_id' => array('type' => 'int', 'references' => array('servers', 'id', 'cascade')),
				'sent' => array('type' => 'datetime'),
				'from',
				'to',
				'subject',
				'output',
			),
		),
		'results_triggers' => array(
			'columns' => array(
				'trigger_id' => array('type' => 'int', 'references' => array('triggers', 'id', 'cascade')),
				'result_id' => array('type' => 'int', 'references' => array('results', 'id', 'cascade')),
				'amount' => array('type' => 'int'),
			),
			'indexes' => array(
				'trigger_result' => array(
					'unique' => true,
					'columns' => array('trigger_id', 'result_id'),
				),
			),
		),
	),
);
