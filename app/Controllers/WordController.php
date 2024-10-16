<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Entities\Response;
use App\Enumerators\ResponseCode;
use App\Exception\NotFoundException;
use App\Exception\UnprocessableEntityException;
use App\Repositories\WordRepository;

readonly class WordController implements ControllerInterface
{
    public function __construct(
        private WordRepository $wordRepository,
    ) {
    }

    public function getAll(): Response
    {
        $result = $this->wordRepository->getAllWords();

        return new Response(ResponseCode::OK, json_encode($result));
    }

    public function getById(int $id): Response
    {
        $wordEntity = $this->wordRepository->getWordById($id);

        if($wordEntity === null) {
            throw new NotFoundException("Word with Id '{$id}' not found");
        }

        return new Response(ResponseCode::OK, json_encode($wordEntity));
    }

    public function create(): Response
    {
        $input = (array) json_decode(file_get_contents('php://input', true));

        if (empty($input['text'])) {
            throw new UnprocessableEntityException('Unprocessable Entity');
        }

        $this->wordRepository->insertWord($input['text']);

        return new Response(ResponseCode::CREATED, null);
    }

    public function update(int $id): Response
    {
        $wordEntity = $this->wordRepository->getWordById($id);

        if ($wordEntity === null) {
            throw new NotFoundException("Word with Id '{$id}' not found");
        }

        $input = (array) json_decode(file_get_contents('php://input', true));

        if (!isset($input['text'])) {
            throw new UnprocessableEntityException('Unprocessable Entity');
        }

        $this->wordRepository->updateWord($id, $input['text']);

        return new Response(ResponseCode::OK, null);
    }

    public function delete(int $id): Response
    {
        $wordEntity = $this->wordRepository->getWordById($id);

        if ($wordEntity === null) {
            throw new NotFoundException("Word with Id '{$id}' not found");
        }

        $this->wordRepository->deleteWord($id);

        return new Response(ResponseCode::OK, null);
    }
}
