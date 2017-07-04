<?php

require 'inc.bootstrap.php';

if ( isset($_POST['type']) ) {
	Type::_updates($_POST['type']);
	return do_redirect();
}

if ( isset($_POST['trigger']) ) {
	Trigger::_updates($_POST['trigger']);
	return do_redirect();
}

include 'tpl.header.php';

$types = Type::all('1 ORDER BY type');
$types[0] = new Type;

$triggers = Trigger::all('1 ORDER BY trigger');
$triggers[0] = new Trigger;

?>

<h2>Types</h2>

<form method="post" action>
	<table border="1">
		<thead>
			<tr>
				<th>#</th>
				<th>Type</th>
				<th>Description</th>
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
				</tr>
			<? endforeach ?>
		</tbody>
	</table>

	<p><button>Save</button></p>
</form>

<h2>Triggers</h2>

<form method="post" action>
	<table border="1">
		<thead>
			<tr>
				<th>#</th>
				<th>Type</th>
				<th>Trigger</th>
				<th>Description</th>
				<th>Regex</th>
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
						<input name="trigger[<?= $id ?>][trigger]" value="<?= html($trigger->trigger) ?>" />
					</td>
					<td>
						<input name="trigger[<?= $id ?>][description]" value="<?= html($trigger->description) ?>" />
					</td>
					<td>
						<input name="trigger[<?= $id ?>][regex]" value="<?= html($trigger->regex) ?>" style="font-family: monospace" />
					</td>
				</tr>
			<? endforeach ?>
		</tbody>
	</table>

	<p><button>Save</button></p>
</form>
