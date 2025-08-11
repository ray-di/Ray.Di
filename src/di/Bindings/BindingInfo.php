<?php

declare(strict_types=1);

namespace Ray\Di\Bindings;

use JsonSerializable;

/**
 * Value object representing a dependency injection binding
 */
final class BindingInfo implements JsonSerializable
{
    /** @var string */
    public $interface;

    /** @var string|null */
    public $named;

    /** @var string */
    public $type;

    /** @var mixed */
    public $target;

    /** @var array<string, list<string>>|null */
    public $aop;

    /**
     * @param string $interface
     * @param string|null $named
     * @param string $type
     * @param mixed $target
     * @param array<string, list<string>>|null $aop
     */
    public function __construct(
        string $interface,
        ?string $named,
        string $type,
        $target,
        ?array $aop = null
    ) {
        $this->interface = $interface;
        $this->named = $named;
        $this->type = $type;
        $this->target = $target;
        $this->aop = $aop;
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        $data = [
            'interface' => $this->interface,
            'named' => $this->named,
            'type' => $this->type,
            'target' => $this->target,
        ];

        if ($this->aop !== null) {
            $data['aop'] = $this->aop;
        }

        return $data;
    }
}