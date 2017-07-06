<?php

use rdx\cronlog\data\Result;
use rdx\cronlog\data\Type;

require 'inc.bootstrap.php';

$type = Type::find(@$_GET['type']);

include 'tpl.header.php';

$results = $type ? $type->results : Result::all('1 ORDER BY sent DESC');

?>

<p>
	<a href="index.php">Index</a>
</p>

<h2>
	Results
	<? if ($type): ?>
		for <em><?= html($type->description) ?></em>
	<? endif ?>
	(<?= count($results) ?>)
</h2>

<table>
	<thead>
		<tr>
			<th>Type</th>
			<th>Origin</th>
			<th>Date/time</th>
			<th>Size</th>
			<? if ($type): ?>
				<? foreach ($type->triggers as $trigger): ?>
					<th style="color: <?= html($trigger->color) ?>"><?= html($trigger->description) ?></th>
				<? endforeach ?>
				<td></td>
			<? endif ?>
		</tr>
	</thead>
	<tbody>
		<? foreach ($results as $result): ?>
			<tr>
				<td><?= html($result->type->description) ?></td>
				<td><?= html($result->server ?: '?') ?></td>
				<td><a href="result.php?id=<?= $result->id ?>"><?= html($result->sent) ?></a></td>
				<td><?= number_format(strlen($result->output), 0) ?></td>
				<? if ($type): ?>
					<? foreach ($type->triggers as $trigger):
						$amount = $result->triggeredAmount($trigger->id);
						?>
						<td <? if ($amount > 0): ?>style="font-weight: bold; color: <?= html($trigger->color) ?>"<? endif ?>>
							<?= $amount ?>
						</td>
					<? endforeach ?>
					<td><a href="result.php?id=<?= $result->id ?>&recollate&goto=results.php?type=<?= $type->id ?>">recollate</a></td>
				<? endif ?>
			</tr>
		<? endforeach ?>
	</tbody>
</table>
