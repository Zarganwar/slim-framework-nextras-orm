<?php

declare(strict_types=1);

namespace App\Application\Actions;

use JsonSerializable;
use ReturnTypeWillChange;

class ActionPayload implements JsonSerializable
{

	public function __construct(
		private readonly int $statusCode = 200,
		private readonly array|object|null $data = null,
		private readonly ?ActionError $error = null
	) {}


	public function getStatusCode(): int
	{
		return $this->statusCode;
	}


	public function getData(): array|null|object
	{
		return $this->data;
	}


	public function getError(): ?ActionError
	{
		return $this->error;
	}


	#[ReturnTypeWillChange]
	public function jsonSerialize(): array
	{
		$payload = [
			'statusCode' => $this->statusCode,
		];

		if ($this->data !== null) {
			$payload['data'] = $this->data;
		} elseif ($this->error !== null) {
			$payload['error'] = $this->error;
		}

		return $payload;
	}

}
