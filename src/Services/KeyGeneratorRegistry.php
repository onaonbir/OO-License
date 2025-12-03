<?php

namespace OnaOnbir\OOLicense\Services;

use InvalidArgumentException;
use OnaOnbir\OOLicense\Models\Project;
use OnaOnbir\OOLicense\Services\KeyGenerators\AbstractKeyGenerator;
use RuntimeException;

class KeyGeneratorRegistry
{
    /**
     * Registered generators
     *
     * @var array<string, string>
     */
    protected array $generators = [];

    /**
     * Register a key generator
     *
     * @param  string  $identifier  Unique identifier (e.g., 'bfb.v1', 'crypto-bot.v1')
     * @param  string  $className  Fully qualified class name
     *
     * @throws InvalidArgumentException
     */
    public function register(string $identifier, string $className): void
    {
        if (! class_exists($className)) {
            throw new InvalidArgumentException("Class {$className} does not exist");
        }

        if (! is_subclass_of($className, AbstractKeyGenerator::class)) {
            throw new InvalidArgumentException(
                "Class {$className} must extend ".AbstractKeyGenerator::class
            );
        }

        $this->generators[$identifier] = $className;
    }

    /**
     * Get registered generator class name
     */
    public function get(string $identifier): ?string
    {
        return $this->generators[$identifier] ?? null;
    }

    /**
     * Check if generator is registered
     */
    public function has(string $identifier): bool
    {
        return isset($this->generators[$identifier]);
    }

    /**
     * Create generator instance
     *
     * @throws RuntimeException
     */
    public function make(string $identifier, Project $project, array $options = []): AbstractKeyGenerator
    {
        $className = $this->get($identifier);

        if (! $className) {
            throw new RuntimeException("Generator '{$identifier}' is not registered");
        }

        return new $className($project, $options);
    }

    /**
     * Get all registered generators
     *
     * @return array<string, string>
     */
    public function all(): array
    {
        return $this->generators;
    }

    /**
     * Get list of available generator identifiers
     *
     * @return array<string>
     */
    public function available(): array
    {
        return array_keys($this->generators);
    }

    /**
     * Unregister a generator
     */
    public function unregister(string $identifier): void
    {
        unset($this->generators[$identifier]);
    }

    /**
     * Clear all registered generators
     */
    public function clear(): void
    {
        $this->generators = [];
    }

    /**
     * Get generator information
     */
    public function getInfo(string $identifier, Project $project): ?array
    {
        if (! $this->has($identifier)) {
            return null;
        }

        $generator = $this->make($identifier, $project);

        return [
            'identifier' => $identifier,
            'class' => get_class($generator),
            'version' => $generator->getVersion(),
            'format' => $generator->getKeyFormat(),
        ];
    }
}
