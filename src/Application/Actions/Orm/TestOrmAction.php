<?php


namespace App\Application\Actions\Orm;


use App\Application\Actions\Action;
use App\Database\Client\Client;
use App\Database\Client\ClientRepository;
use Nextras\Orm\Model\IModel;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;

final class TestOrmAction extends Action
{

	public function __construct(
		LoggerInterface $logger,
		private readonly IModel $model,
	)
	{
		parent::__construct($logger);
	}


	protected function action(): Response
	{
		$repository = $this->model->getRepository(ClientRepository::class);

		$new = new Client();
		$new->name = 'John Doe';
		$repository->persistAndFlush($new);

		$clientMap = [];

		foreach ($repository->findAll() as $client) {
			$clientMap[] = [
				'id' => $client->id,
				'name' => $client->name,
			];
		}

		$json = json_encode($clientMap, JSON_PRETTY_PRINT);

		$this->response->getBody()->write("Registered clients: <pre>{$json}</pre>");

		return $this->response;
	}

}