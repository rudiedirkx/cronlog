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

$triggers = Trigger::all('1 ORDER BY o, trigger');
$triggers[0] = new Trigger;

$servers = Server::all('1 ORDER BY name');
$servers[0] = new Server;

?>

<h2 id="types">Types</h2>

<form method="post" action="#types">
	<table>
		<thead>
			<tr>
				<th>#</th>
				<th>Type</th>
				<th>Description</th>
				<th><code>To</code> regex</th>
				<th><code>Subject</code> regex</th>
				<th title="Delete output for nominal results">D</th>
				<th title="Notify admin for anominal results">N</th>
				<th></th>
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
						<input name="type[<?= $id ?>][to_regex]" value="<?= html($type->to_regex) ?>" class="regex" />
					</td>
					<td>
						<input name="type[<?= $id ?>][subject_regex]" value="<?= html($type->subject_regex) ?>" class="regex" />
					</td>
					<td title="Delete output for nominal results">
						<input type="checkbox" name="type[<?= $id ?>][handling_delete]" <? if ($type->handling_delete): ?>checked<? endif ?> />
					</td>
					<td title="Notify admin for anominal results">
						<input type="checkbox" name="type[<?= $id ?>][handling_notify]" <? if ($type->handling_notify): ?>checked<? endif ?> />
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

<h2 id="triggers">Triggers</h2>

<form method="post" action="#triggers">
	<table>
		<thead>
			<tr>
				<th>#</th>
				<th>Types</th>
				<th>O</th>
				<th>Trigger</th>
				<th>Description</th>
				<th>Regex</th>
				<th>Nominal</th>
				<th>Color</th>
			</tr>
		</thead>
		<tbody>
			<? foreach ($triggers as $id => $trigger): ?>
				<tr>
					<th><?= $id ?: '' ?></th>
					<td>
						<select multiple size="1" name="trigger[<?= $id ?>][type_ids][]">
							<?= html_options(array_diff_key($types, [0]), $trigger->type_ids) ?>
						</select>
						<? if ($id): ?>
							(<span><?= count($trigger->type_ids) ?></span>)
						<? endif ?>
					</td>
					<td>
						<input name="trigger[<?= $id ?>][o]" value="<?= html($trigger->o) ?>" class="o" />
					</td>
					<td>
						<input name="trigger[<?= $id ?>][trigger]" value="<?= html($trigger->trigger) ?>" />
					</td>
					<td>
						<input name="trigger[<?= $id ?>][description]" value="<?= html($trigger->description) ?>" />
					</td>
					<td>
						<input name="trigger[<?= $id ?>][regex]" value="<?= html($trigger->regex) ?>" class="regex" />
					</td>
					<td>
						<input name="trigger[<?= $id ?>][expect]" value="<?= html($trigger->expect) ?>" class="expect" pattern="[<>]?-?\d+" title=">0, 2, <9999 etc" />
					</td>
					<td>
						<input name="trigger[<?= $id ?>][color]" value="<?= html($trigger->color) ?>" class="color" />
					</td>
				</tr>
			<? endforeach ?>
		</tbody>
	</table>

	<p><button>Save</button></p>
</form>

<h2 id="servers">Servers</h2>

<form method="post" action="#servers">
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
						<input name="server[<?= $id ?>][from_regex]" value="<?= html($server->from_regex) ?>" class="regex" />
					</td>
				</tr>
			<? endforeach ?>
		</tbody>
	</table>

	<p><button>Save</button></p>
</form>

<script>
(function() {
	var onFocus = function(e) {
		setTimeout(() => this.size = this.options.length, 111);
	};
	var onBlur = function(e) {
		setTimeout(() => this.size = 1, 111);
	};
	var onChange = function(e) {
		this.nextElementSibling.textContent = this.selectedOptions.length;
	};

	document.querySelectorAll('select[multiple][size="1"]').forEach(function(el) {
		el.addEventListener('focus', onFocus);
		el.addEventListener('blur', onBlur);
		el.addEventListener('change', onChange);
	});
})();
</script>
