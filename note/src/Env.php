<?php

declare(strict_types=1);

namespace Note\Framework;

class Env
{
    /**
     * Variáveis de ambiente
     *
     * @var array
     */
    protected array $env = [];

    /**
     * Carrega variáveis de ambiente do arquivo .env
     *
     * @param string $envFile
     *
     * @return void
     */
    public function __construct(string $envFile)
    {
        if (!file_exists($envFile)) {
            return;
        }

        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        foreach ($lines as $line) {
            if (strpos(trim($line), '#') === 0) {
                continue;
            }

            if (strpos($line, '=') !== false) {
                list($name, $value) = explode('=', $line, 2);
                $name = trim($name);
                $value = trim($value);

                if (preg_match('/^"(.*)"$/', $value, $matches)) {
                    $value = $matches[1];
                } elseif (preg_match("/^'(.*)'$/", $value, $matches)) {
                    $value = $matches[1];
                }

                $this->env[$name] = $value;
            }
        }
    }

    /**
     * Obtém uma variável de ambiente
     *
     * @param string $key
     * @param mixed $default
     *
     * @return mixed
     */
    public function get(string $key, mixed $default = null): mixed
    {
        $key = $this->normalizeKey($key);

        if (array_key_exists($key, $this->env)) {
            return $this->parseEnvValue($this->env[$key]);
        }

        return $default;
    }

    /**
     * Transforma dot.notation em UPPER_CASE
     *
     * @param string $key
     *
     * @return string
     */
    protected function normalizeKey(string $key): string
    {
        $key = strtoupper($key);

        if (str_contains($key, '.')) {
            $key = str_replace('.', '_', $key);
        }

        return $key;
    }

    /**
     * Processa valores especiais do .env (true, false, null, int, float)
     *
     * @param mixed $value
     *
     * @return mixed
     */
    protected function parseEnvValue($value): mixed
    {
        if ($value === null) {
            return null;
        }

        $value = trim($value);

        switch (strtolower($value)) {
            case 'true':
            case '(true)':
                return true;
            case 'false':
            case '(false)':
                return false;
            case 'null':
            case '(null)':
                return null;
            case 'empty':
            case '(empty)':
                return '';
        }

        if (is_numeric($value)) {
            return strpos($value, '.') !== false ? (float) $value : (int) $value;
        }

        return $value;
    }
}
