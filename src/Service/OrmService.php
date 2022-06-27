<?php

namespace App\Service;

use Doctrine\Persistence\ManagerRegistry;
use Exception;
use Symfony\Component\HttpFoundation\Response;

/**
 * @codeCoverageIgnore
 */
class OrmService
{
    private HTTPErrorHandler $httpErrorHandler;
    private ManagerRegistry $orm;
    public function __construct(ManagerRegistry $orm, HTTPErrorHandler $httpErrorHandler)
    {
        $this->httpErrorHandler = $httpErrorHandler;
        $this->orm = $orm;
    }

    public function find(string $key, string $entityClass): object|null
    {
        try {
            $db = $this->orm->getRepository($entityClass);
            return $db->find($key);
        } catch (Exception $e){
            $this->httpErrorHandler->addError(
                "Ha habido un error al acceder a la base de datos",
                Response::HTTP_INTERNAL_SERVER_ERROR,
                $e->getMessage()
            );
            return null;
        }
    }

    public function findOneBy(array $key_value, string $entityClass): object|null
    {
        try {
            $db = $this->orm->getRepository($entityClass);
            return $db->findOneBy($key_value);
        } catch (Exception $e){
            $this->httpErrorHandler->addError(
                "Ha habido un error al acceder a la base de datos",
                Response::HTTP_INTERNAL_SERVER_ERROR,
                $e->getMessage()
            );
            return null;
        }
    }

    public function findAll(string $entityClass): array
    {
        try {
            $db = $this->orm->getRepository($entityClass);
            return $db->findAll();
        }catch (Exception $e){
            $this->httpErrorHandler->addError(
                "Ha habido un error al buscar en la base de datos. ",
                Response::HTTP_INTERNAL_SERVER_ERROR,
                $e->getMessage()
            );
            return [];
        }
    }

    public function persist($object): void
    {
        try {
            $db = $this->orm->getRepository($object::class);
            $db->add($object, true);
        } catch (Exception $e){
            $this->httpErrorHandler->addError(
                "Ha habido un error al guardar en la base de datos. ",
                Response::HTTP_INTERNAL_SERVER_ERROR,
                $e->getMessage()
            );
        }
    }

    public function remove(mixed $object): void{
        try {
            $db = $this->orm->getRepository($object::class);
            $db->remove($object, true);
        } catch (Exception $e){
            $this->httpErrorHandler->addError(
                "Ha habido un error al eliminar de la base de datos. ",
                Response::HTTP_INTERNAL_SERVER_ERROR,
                $e->getMessage()
            );
        }
    }
}