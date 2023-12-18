<?php


namespace App\Application\Actions\Orm;


use App\Application\Actions\Action;
use App\Database\Client\ClientRepository;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;

final class RepositoryDependencyAction extends Action
{
	public function __construct(
		LoggerInterface $logger,
		private readonly ClientRepository $repository,
	)
	{
		parent::__construct($logger);
	}


	protected function action(): Response
	{
		$id = $this->request->getAttribute('id');
		$client = $this->repository->getById($id);
		$name = $client?->name ?? 'Nikdo takový zde není';

		$this->response->getBody()->write("Client: <pre>{$name}</pre>");

		return $this->response;
	}

}