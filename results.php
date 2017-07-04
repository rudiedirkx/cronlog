<?php

require 'inc.bootstrap.php';

if ( !($type = Type::find($_GET['type'])) ) {
	exit('Need `type`.');
}

include 'tpl.header.php';

?>

<h2>Results for <em><?= html($type->description) ?></em></h2>

<pre><?= html(print_r($type->results, 1)) ?></pre>
