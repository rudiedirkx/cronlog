<?php

use rdx\cronlog\Result;

require 'inc.bootstrap.php';

if ( !($result = Result::find(@$_GET['id'])) ) {
	include 'tpl.header.php';
	echo '<p class="error">Need ?id</p>';
	exit;
}

if ( isset($_GET['recollate']) ) {
	$result->retype() && $result->collate();

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
			<th>Subject</th>
			<td colspan="2" title="<?= html($result->subject) ?>"><code><?= html($result->relevant_subject) ?></code></td>
		</tr>
		<tr>
			<th>Server</th>
			<td colspan="2"><?= html($result->server ?: '?') ?> (<?= html($result->from) ?>)</td>
		</tr>
		<tr>
			<th>Date/time</th>
			<td colspan="2">
				<? if ($result->nominal === false): ?>
					<img src="warning.png" title="Does not meet the expected values" />
				<? endif ?>
				<?= html($result->sent) ?>
				<a href="?id=<?= $result->id ?>&recollate">recollate</a>
			</td>
		</tr>
		<? $first = 1;
		foreach ($result->type->triggers as $trigger):
			list($amount, $nominal) = $result->triggered($trigger->id);
			?>
			<tr>
				<? if ($first-- > 0): ?>
					<th valign="top" rowspan="<?= count($result->type->triggers) ?>">Triggers</th>
				<? endif ?>
				<td style="color: <?= html($trigger->color) ?>" title="<?= html($trigger->regex) ?>" nowrap width="10%">
					<label>
						<input type="checkbox" name="hilite" value="<?= html("{$trigger->color},{$trigger->regex}") ?>" <?= $trigger->js_regex ? 'checked' : 'disabled' ?> />
						<?= html($trigger->description) ?>
					</label>
				</td>
				<td <? if ($amount > 0): ?>style="font-weight: bold; color: <?= html($trigger->color) ?>"<? endif ?> width="90%">
					<? if ($nominal === false): ?>
						<img src="warning.png" title="Does not meet the expected value `<?= $trigger->expect ?>`" />
					<? endif ?>
					<span title="<?= $trigger->pretty_expect ?>"><?= $amount ?></span>
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
		var regexes = [].map.call(document.querySelectorAll('[name="hilite"]:checked'), function(el) {
			return el.value;
		});
		regexes = regexes.map(function(attr) {
			var x = attr.split(',');
			var color = x.shift();
			var regex = x.join(',');
			var m = regex.match(/\/(.+)\/(i?)/);
			return [color, new RegExp(m[1], 'g' + m[2])];
		});

		var hilitedOutput = output;
		regexes.forEach(function(trigger) {
			hilitedOutput = hilitedOutput.replace(trigger[1], function(m) {
				return '<strong style="color: ' + trigger[0] + '">' + m + '</strong>';
			});
		});
		$output.innerHTML = hilitedOutput;
	};

	var onChange = function(e) {
		hilite();
	};

	[].forEach.call(document.querySelectorAll('[name="hilite"]'), function(el) {
		el.addEventListener('change', onChange);
	});

	hilite();
})();
</script>

<?php

include 'tpl.footer.php';
