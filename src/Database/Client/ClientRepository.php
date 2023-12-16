<?php


namespace App\Database\Client;


use Nextras\Orm\Repository\Repository;

final class ClientRepository extends Repository
{

	public static function getEntityClassNames(): array
	{
		return [Client::class];
	}

}