<?php

namespace App\Service;

use Doctrine\Persistence\ManagerRegistry;
use Exception;
use Symfony\Component\HttpFoundation\Response;

class OrmService
{
    private HTTPResponseHandler $httpHandler;
    private ManagerRegistry $orm;
    public function __construct(ManagerRegistry $orm, HTTPResponseHandler $httpHandler)
    {
        $this->httpHandler = $httpHandler;
        $this->orm = $orm;
    }

    public function find(string $key, string $entityClass){
        try {
            $db = $this->orm->getRepository($entityClass);
            return $db->find($key);
        } catch (Exception){
            $this->httpHandler->addError(
                Response::HTTP_INTERNAL_SERVER_ERROR,
                "Ha habido un error al acceder a la base de datos"
            );
            return null;
        }
    }

    public function findOneBy(array $key_value, string $entityClass){
        try {
            $db = $this->orm->getRepository($entityClass);
            return $db->findOneBy($key_value);
        } catch (Exception){
            $this->httpHandler->addError(
                Response::HTTP_INTERNAL_SERVER_ERROR,
                "Ha habido un error al acceder a la base de datos"
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
            $this->httpHandler->addError(
                Response::HTTP_INTERNAL_SERVER_ERROR,
                "Ha habido un error al buscar en la base de datos. " . $e->getMessage()
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
            $this->httpHandler->addError(
                Response::HTTP_INTERNAL_SERVER_ERROR,
                "Ha habido un error al guardar en la base de datos. " . $e->getMessage()
            );
        }

    }
}