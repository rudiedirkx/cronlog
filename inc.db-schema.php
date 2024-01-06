<?php

return array(
	'version' => 28,
	'tables' => array(
		'types' => array(
			'columns' => array(
				'id' => array('pk' => true),
				'description',
				'subject_regex',
				'handling' => array('type' => 'int', 'default' => 0),
				'enabled' => array('type' => 'int', 'default' => 1),
			),
		),
		'triggers' => array(
			'columns' => array(
				'id' => array('pk' => true),
				'description',
				'regex',
				'expect',
				'color',
				'o' => array('type' => 'int', 'default' => 0),
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
				'output_size' => array('type' => 'int'),
				'nominal' => array('type' => 'int', 'default' => null),
				'batch' => array('type' => 'int'),
				'timing' => array('type' => 'int', 'default' => 0),
			),
			'indexes' => array(
				'type_id' => ['type_id'],
				'server_id' => ['server_id'],
				'sent' => ['sent'],
				'batch' => ['batch'],
				'type_sent' => ['type_id', 'sent'],
			),
		),
		'results_triggers' => array(
			'columns' => array(
				'trigger_id' => array('type' => 'int', 'references' => array('triggers', 'id', 'cascade')),
				'result_id' => array('type' => 'int', 'references' => array('results', 'id', 'cascade')),
				'amount' => array('type' => 'int'),
				'nominal' => array('type' => 'int', 'default' => null),
			),
			'indexes' => array(
				'result_id' => ['result_id'],
				'trigger_result' => array(
					'unique' => true,
					'columns' => array('trigger_id', 'result_id'),
				),
			),
		),
		'uploaded_logs' => array(
			'columns' => array(
				'id' => array('pk' => true),
				'created_on' => array('type' => 'datetime'),
				'from',
				'subject',
				'body',
			),
		),
	),
);
