<?php


namespace App\Database;


use App\Database\Client\ClientRepository;
use Nextras\Orm\Model\Model as NextrasOrmModel;

/**
 * @property-read ClientRepository $clients
 */
final class Model extends NextrasOrmModel
{

}