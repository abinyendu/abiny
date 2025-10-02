<?php

namespace App\Core;

class Request
{
    public function __construct(
        private array $get,
        private array $post,
        private array $server,
        private array $cookies,
        private array $files,
        private array $headers
    ) {
    }

    public static function capture(): self
    {
        $headers = [];
        foreach ($_SERVER as $key => $value) {
            if (str_starts_with($key, 'HTTP_')) {
                $h = str_replace('_', '-', strtolower(substr($key, 5)));
                $headers[$h] = $value;
            }
        }
        return new self($_GET, $_POST, $_SERVER, $_COOKIE, $_FILES, $headers);
    }

    public function method(): string
    {
        return strtoupper($this->server['REQUEST_METHOD'] ?? 'GET');
    }

    public function path(): string
    {
        $uri = $this->server['REQUEST_URI'] ?? '/';
        $qPos = strpos($uri, '?');
        return $qPos === false ? $uri : substr($uri, 0, $qPos);
    }

    public function input(string $key, mixed $default = null): mixed
    {
        return $this->post[$key] ?? $this->get[$key] ?? $default;
    }

    public function all(): array
    {
        return array_merge($this->get, $this->post);
    }

    public function header(string $key, mixed $default = null): mixed
    {
        $k = strtolower($key);
        return $this->headers[$k] ?? $default;
    }

    public function cookie(string $key, mixed $default = null): mixed
    {
        return $this->cookies[$key] ?? $default;
    }

    public function isAjax(): bool
    {
        return ($this->header('x-requested-with') === 'XMLHttpRequest');
    }
}
