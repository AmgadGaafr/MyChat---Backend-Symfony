<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ValidatorService
{
    public function __construct(private ValidatorInterface $validator)
    {
        
    }

    public function validate($object)
    {
        $errors = $this->validator->validate($object);
        
        if (count($errors) > 0) {
            $dataErrors = [];
            foreach ($errors as $error) {
                
                $dataErrors[$error->getPropertyPath()][] = $error->getMessage();
            }
            return new JsonResponse($dataErrors, Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }
}