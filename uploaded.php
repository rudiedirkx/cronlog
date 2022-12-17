<?php

use rdx\cronlog\UploadedLog;

require 'inc.bootstrap.php';

if (isset($_POST['from'], $_POST['subject'], $_FILES['body']) && empty($_FILES['body']['error'])) {
	$body = file_get_contents($_FILES['body']['tmp_name']);
	$id = UploadedLog::insert([
		'created_on' => date('Y-m-d H:i:s'),
		'from' => (string) $_POST['from'],
		'subject' => (string) $_POST['subject'],
		'body' => $body,
	]);
	var_dump($id);
	exit;
}

header('HTTP/1.1 400 Invalid request');
echo "Invalid request\n";
exit;
