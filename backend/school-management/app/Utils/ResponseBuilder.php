<?php

namespace App\Utils;

class ResponseBuilder {
    private bool $success = false;
    private string $message = '';
    private array $data = [];

    public function success(bool $success = true): self {
        $this->success = $success;
        return $this;
    }

    public function message(string $message): self {
        $this->message = $message;
        return $this;
    }

    public function data(array $data): self {
        $this->data = $data;
        return $this;
    }

    public function build(): array {
        return [
            'success' => $this->success,
            'message' => $this->message,
            'data'    => $this->data
        ];
    }
}
