<?php

use rdx\cronlog\data\Server;
use rdx\cronlog\data\Trigger;
use rdx\cronlog\data\Type;

require 'inc.bootstrap.php';

if ( isset($_POST['type']) ) {
	Type::_updates($_POST['type']);
	return do_redirect();
}

if ( isset($_POST['server']) ) {
	Server::_updates($_POST['server']);
	return do_redirect();
}

if ( isset($_POST['trigger']) ) {
	Trigger::_updates($_POST['trigger']);
	return do_redirect();
}

include 'tpl.header.php';

$types = Type::all('1 ORDER BY type');
$types[0] = new Type;

$triggers = Trigger::all('1 ORDER BY type_id, o, trigger');
$triggers[0] = new Trigger;

$servers = Server::all('1 ORDER BY name');
$servers[0] = new Server;

?>

<p>
	<a href="cron.php">Execute</a> |
	<a href="results.php">All results</a>
</p>

<h2>Types</h2>

<form method="post" action>
	<table>
		<thead>
			<tr>
				<th>#</th>
				<th>Type</th>
				<th>Description</th>
				<th><code>To</code> regex</th>
				<th><code>Subject</code> regex</th>
			</tr>
		</thead>
		<tbody>
			<? foreach ($types as $id => $type): ?>
				<tr>
					<th><?= $id ?: '' ?></th>
					<td>
						<input name="type[<?= $id ?>][type]" value="<?= html($type->type) ?>" />
					</td>
					<td>
						<input name="type[<?= $id ?>][description]" value="<?= html($type->description) ?>" />
					</td>
					<td>
						<input name="type[<?= $id ?>][to_regex]" value="<?= html($type->to_regex) ?>" />
					</td>
					<td>
						<input name="type[<?= $id ?>][subject_regex]" value="<?= html($type->subject_regex) ?>" />
					</td>
					<td>
						<? if ($id): ?>
							<a href="results.php?type=<?= $id ?>"><?= $type->num_results ?> results</a>
						<? endif ?>
					</td>
				</tr>
			<? endforeach ?>
		</tbody>
	</table>

	<p><button>Save</button></p>
</form>

<h2>Triggers</h2>

<form method="post" action>
	<table>
		<thead>
			<tr>
				<th>#</th>
				<th>Type</th>
				<th>O</th>
				<th>Trigger</th>
				<th>Description</th>
				<th>Regex</th>
				<th>Color</th>
			</tr>
		</thead>
		<tbody>
			<? foreach ($triggers as $id => $trigger): ?>
				<tr>
					<th><?= $id ?: '' ?></th>
					<td>
						<select name="trigger[<?= $id ?>][type_id]">
							<?= html_options(array_diff_key($types, [0]), $trigger->type_id, '--') ?>
						</select>
					</td>
					<td>
						<input name="trigger[<?= $id ?>][o]" value="<?= html($trigger->o) ?>" style="width: 1.5em" />
					</td>
					<td>
						<input name="trigger[<?= $id ?>][trigger]" value="<?= html($trigger->trigger) ?>" />
					</td>
					<td>
						<input name="trigger[<?= $id ?>][description]" value="<?= html($trigger->description) ?>" />
					</td>
					<td>
						<input name="trigger[<?= $id ?>][regex]" value="<?= html($trigger->regex) ?>" style="font-family: monospace" />
					</td>
					<td>
						<input name="trigger[<?= $id ?>][color]" value="<?= html($trigger->color) ?>" />
					</td>
				</tr>
			<? endforeach ?>
		</tbody>
	</table>

	<p><button>Save</button></p>
</form>

<h2>Servers</h2>

<form method="post" action>
	<table>
		<thead>
			<tr>
				<th>#</th>
				<th>Name</th>
				<th><code>From</code> regex</th>
			</tr>
		</thead>
		<tbody>
			<? foreach ($servers as $id => $server): ?>
				<tr>
					<th><?= $id ?: '' ?></th>
					<td>
						<input name="server[<?= $id ?>][name]" value="<?= html($server->name) ?>" />
					</td>
					<td>
						<input name="server[<?= $id ?>][from_regex]" value="<?= html($server->from_regex) ?>" />
					</td>
				</tr>
			<? endforeach ?>
		</tbody>
	</table>

	<p><button>Save</button></p>
</form>
