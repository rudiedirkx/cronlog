<?php

namespace rdx\cronlog\import;

use Exception;
use rdx\cronlog\UploadedLog;

class UploadedImporter implements Importer {
	public function __construct(protected UploadedLog $upload) {}

	public function delete() {
		$this->upload->delete();
	}

	public function getFrom() {
		return $this->upload->from;
	}

	public function getTo() {
		return 'upload';
	}

	public function getSubject() {
		return $this->upload->subject;
	}

	public function getSentUtc() {
		return strtotime($this->upload->created_on);
	}

	public function getOutput() {
		return $this->upload->body;
	}
}
