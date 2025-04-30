<?php

namespace rdx\cronlog;

use Exception;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use rdx\cronlog\Result;
use rdx\cronlog\Type;
use rdx\cronlog\import\Importer;
use rdx\cronlog\import\ImporterReader;

class DbImporterReader implements ImporterReader {
	public int $batch;

	/** @var list<string> */
	public array $skipped = [];
	public int $results = 0;
	public int $anominals = 0;
	public int $notifications = 0;
	public int $triggers = 0;

	public function __construct() {
		$this->batch = time();
	}

	public function read( Importer $importer ) : void {
		$from = $importer->getFrom();
		$to = $importer->getTo();
		$subject = $importer->getSubject();
		$sent = date('Y-m-d H:i:s', $importer->getSentUtc());
// echo "[$sent] $subject\n";
		$output = trim($importer->getOutput());

		$insert = compact('from', 'to', 'subject', 'sent', 'output');

		$type = Type::findBySubject($subject);
		if ( !$type ) {
			$this->skipped[] = $subject;
			return;
		}
		$insert['type_id'] = $type->id;

		$insert['batch'] = $this->batch;
		$insert['output_size'] = strlen($output);

		$id = Result::insert($insert);
		$result = Result::find($id);

		list($triggers, , $anominals) = $result->collate();

		$this->results++;
		if ( $anominals > 0 ) {
			$this->anominals++;

			if ( $type->handling_notify ) {
				$this->notifications++;
			}
		}
		$this->triggers += $triggers;

		if ( CRONLOG_DELETE_IMPORTS ) {
			try {
				$importer->delete();
			}
			catch ( Exception $ex ) {
				return;
			}
		}

		return;
	}

	static public function sendMail(string $subject, string $body) : void {
		if ( CRONLOG_EMAIL_SMTP ) { // @phpstan-ignore if.alwaysTrue
			$dsn = sprintf('smtp://%s:%s@%s', CRONLOG_EMAIL_SMTP[1], CRONLOG_EMAIL_SMTP[2], CRONLOG_EMAIL_SMTP[0]);
			$transport = Transport::fromDsn($dsn);
			$mailer = new Mailer($transport);

			$email = (new Email())
				->from(new Address(CRONLOG_EMAIL_SMTP[1], 'Cronlog'))
				// ->sender(MAIL_SENDER)
				// ->replyTo(sprintf('u%d@d%d.com', rand(), rand()))
				->to(CRONLOG_EMAIL_RESULTS)
				->subject($subject)
				->text($body)
				// ->html('<p>Through <b>SMTP</b> <u>mailbox</u>.</p>')
			;
// dd($mailer);
			$mailer->send($email);
		}
		else {
			mail(CRONLOG_EMAIL_RESULTS, $subject, $body, implode("\r\n", [
				"From: Cronlog <cronlog@hotblocks.nl>",
				"Return-path: cronlog@hotblocks.nl",
			]));
		}
	}
}
