<?php

function html( $text ) {
	return htmlspecialchars((string)$text, ENT_QUOTES, 'UTF-8') ?: htmlspecialchars((string)$text, ENT_QUOTES, 'ISO-8859-1');
}

function do_redirect( $uri = null ) {
	$uri or $uri = get_url();
	header("Location: " . $uri);
	exit;
}

function get_url() {
	$scheme = @$_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
	$host = $_SERVER['HTTP_HOST'];
	$uri = $_SERVER['REQUEST_URI'];
	return $scheme . $host . $uri;
}

function html_options( $options, $selected = null, $empty = '' ) {
	$html = '';
	$empty && $html .= '<option value="">' . $empty;
	foreach ( $options AS $value => $label ) {
		$isSelected = $value == $selected ? ' selected' : '';
		$html .= '<option value="' . html($value) . '"' . $isSelected . '>' . html($label) . '</option>';
	}
	return $html;
}
