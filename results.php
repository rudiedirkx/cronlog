<?php

use rdx\cronlog\data\Result;
use rdx\cronlog\data\Type;

require 'inc.bootstrap.php';

$type = Type::find(@$_GET['type']);

$results = $type ? $type->results : Result::all('1 ORDER BY sent DESC');

if ( isset($_GET['recollate']) ) {
	foreach ( $results as $result ) {
		$result->collate();
	}

	$query = $type ? "?type={$type->id}" : '';
	return do_redirect('results.php' . $query);
}

include 'tpl.header.php';

?>

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
			<th>#</th>
			<? if (!$type): ?>
				<th>Type</th>
			<? endif ?>
			<th>Subject</th>
			<th>Origin</th>
			<th>Date/time</th>
			<th>Size</th>
			<? if ($type): ?>
				<? foreach ($type->triggers as $trigger): ?>
					<th style="color: <?= html($trigger->color) ?>" title="<?= html($trigger->regex) ?>"><?= html($trigger->description) ?></th>
				<? endforeach ?>
			<? endif ?>
			<th><a href="?type=<?= @$type->id ?>&recollate">recollate</a></th>
		</tr>
	</thead>
	<tbody>
		<? $prevDate = null;
		foreach ($results as $result):
			$newSection = $prevDate && substr($result->sent, 0, 10) != $prevDate;
			$prevDate = substr($result->sent, 0, 10);
			?>
			<tr class="<?= $newSection ? 'next-section' : '' ?>">
				<th><?= $result->id ?></th>
				<? if (!$type): ?>
					<td><a href="?type=<?= $result->type_id ?>"><?= html($result->type->description) ?></a></td>
				<? endif ?>
				<td><?= html($result->relevant_subject) ?></td>
				<td><?= html($result->server ?: '?') ?></td>
				<td><a href="result.php?id=<?= $result->id ?>"><?= get_datetime($result->sent) ?></a></td>
				<td><?= number_format(strlen($result->output), 0) ?></td>
				<? if ($type): ?>
					<? foreach ($type->triggers as $trigger):
						$amount = $result->triggeredAmount($trigger->id);
						?>
						<td <? if ($amount > 0): ?>style="font-weight: bold; color: <?= html($trigger->color) ?>"<? endif ?>>
							<?= $amount ?>
						</td>
					<? endforeach ?>
				<? endif ?>
				<td><a href="result.php?id=<?= $result->id ?>&recollate&goto=results.php?type=<?= $type->id ?>">recollate</a></td>
			</tr>
		<? endforeach ?>
	</tbody>
</table>
