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
			<td colspan="2"><?= html($result->type->description) ?></td>
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
					<?= html($trigger->description) ?>
				</td>
				<td <? if ($amount > 0): ?>style="font-weight: bold; color: <?= html($trigger->color) ?>"<? endif ?>>
					<?= $amount ?>
				</td>
			</tr>
		<? endforeach ?>
		<tr>
			<th valign="top">Output</th>
			<td colspan="2" style="font-family: monospace; white-space: pre-line"><?= html($result->output) ?></td>
		</tr>
	</tbody>
</table>
