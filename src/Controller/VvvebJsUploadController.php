<?php

namespace Makraz\VvvebJsBundle\Controller;

use Makraz\VvvebJsBundle\Upload\UploadHandlerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class VvvebJsUploadController
{
    /**
     * @param list<string> $allowedMimeTypes
     */
    public function __construct(
        private readonly UploadHandlerInterface $uploadHandler,
        private readonly int $maxFileSize,
        private readonly array $allowedMimeTypes,
    ) {
    }

    public function upload(Request $request): JsonResponse
    {
        $file = $request->files->get('file');

        if (null === $file) {
            return $this->errorResponse('No file uploaded.');
        }

        if (!$file->isValid()) {
            return $this->errorResponse($file->getErrorMessage());
        }

        if ($file->getSize() > $this->maxFileSize) {
            return $this->errorResponse(\sprintf('File too large. Maximum size: %d MB.', $this->maxFileSize / 1024 / 1024));
        }

        $mimeType = $file->getMimeType();
        if ([] !== $this->allowedMimeTypes && !\in_array($mimeType, $this->allowedMimeTypes, true)) {
            return $this->errorResponse(\sprintf('File type "%s" is not allowed.', $mimeType));
        }

        try {
            $url = $this->uploadHandler->upload($file);

            return new JsonResponse([
                'success' => true,
                'url' => $url,
            ]);
        } catch (\Throwable $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    private function errorResponse(string $message): JsonResponse
    {
        return new JsonResponse([
            'success' => false,
            'message' => $message,
        ], Response::HTTP_BAD_REQUEST);
    }
}
