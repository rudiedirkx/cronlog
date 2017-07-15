<?php

use rdx\cronlog\data\Result;

require 'inc.bootstrap.php';

if ( !($result = Result::find(@$_GET['id'])) ) {
	include 'tpl.header.php';
	echo '<p class="error">Need ?id</p>';
	exit;
}

if ( isset($_GET['recollate']) ) {
	$result->collate();

	$goto = @$_GET['goto'] ?: 'result.php?id=' . $result->id;
	return do_redirect($goto);
}

include 'tpl.header.php';

?>

<h2><em><?= html($result->type->description) ?></em> from <?= $result->sent ?></h2>

<table>
	<tbody>
		<tr>
			<th>Type</th>
			<td colspan="2"><a href="results.php?type=<?= $result->type_id ?>"><?= html($result->type->description) ?></a></td>
		</tr>
		<tr>
			<th>Origin</th>
			<td colspan="2"><?= html($result->server ?: '?') ?></td>
		</tr>
		<tr>
			<th>Date/time</th>
			<td colspan="2"><?= html($result->sent) ?></td>
		</tr>
		<? $first = 1;
		foreach ($result->type->triggers as $trigger):
			$amount = $result->triggeredAmount($trigger->id);
			?>
			<tr>
				<? if ($first-- > 0): ?>
					<th valign="top" rowspan="<?= count($result->type->triggers) ?>">Triggers</th>
				<? endif ?>
				<td style="color: <?= html($trigger->color) ?>" title="<?= html($trigger->regex) ?>">
					<label>
						<input type="checkbox" name="hilite" value="<?= html("{$trigger->color},{$trigger->regex}") ?>" <?= $trigger->js_regex ? 'checked' : 'disabled' ?> />
						<?= html($trigger->description) ?>
					</label>
				</td>
				<td <? if ($amount > 0): ?>style="font-weight: bold; color: <?= html($trigger->color) ?>"<? endif ?>>
					<?= $amount ?>
				</td>
			</tr>
		<? endforeach ?>
		<tr>
			<th valign="top">Output</th>
			<td class="output" colspan="2" style="background: white"><?= html($result->output) ?></td>
		</tr>
	</tbody>
</table>

<script>
(function() {
	var $output = document.querySelector('.output');
	var output = $output.innerHTML;

	var hilite = function() {
		var regexes = [].map.call(document.querySelectorAll('[name="hilite"]:checked'), (el) => el.value);
		regexes = regexes.map(function(attr) {
			var x = attr.split(',');
			var color = x.shift();
			var regex = x.join(',');
			var m = regex.match(/\/(.+)\/(i?)/);
			return [color, new RegExp(m[1], 'g' + m[2])];
		});
		console.log(regexes);

		var hilitedOutput = output;
		regexes.forEach(function([color, regex]) {
			hilitedOutput = hilitedOutput.replace(regex, function(m) {
				return `<strong style="color: ${color}">${m}</strong>`;
			});
		});
		$output.innerHTML = hilitedOutput;
	};

	var onChange = function(e) {
		hilite();
	};

	document.querySelectorAll('[name="hilite"]').forEach(function(el) {
		el.addEventListener('change', onChange);
	});

	hilite();
})();
</script>
