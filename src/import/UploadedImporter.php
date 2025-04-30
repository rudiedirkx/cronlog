<?php

namespace rdx\cronlog\import;

use Exception;
use rdx\cronlog\UploadedLog;

class UploadedImporter implements Importer {

	public function __construct(
		protected UploadedLog $upload,
	) {}

	public function delete() : void {
		$this->upload->delete();
	}

	public function getFrom() : ?string {
		return $this->upload->from;
	}

	public function getTo() : ?string {
		return 'upload';
	}

	public function getSubject() : ?string {
		return $this->upload->subject;
	}

	public function getSentUtc() : ?int {
		return strtotime($this->upload->created_on);
	}

	public function getOutput() : ?string {
		return $this->upload->body;
	}
}
