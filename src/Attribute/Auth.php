<?php

namespace Elenyum\Authorization\Attribute;

use OpenApi\Annotations\AbstractAnnotation;

#[\Attribute(\Attribute::IS_REPEATABLE | \Attribute::TARGET_CLASS | \Attribute::TARGET_METHOD | \Attribute::TARGET_FUNCTION)]
class Auth extends AbstractAnnotation
{
    /** {@inheritdoc} */
    public static $_types = [
        'name' => 'string',
        'scopes' => '[string]',
    ];

    public static $_required = ['name'];

    public function __construct(
        array $properties = [],
        public string $name = '',
        public string $model = '',
        public array $scopes = [],
        public ?string $type = null
    ) {
        parent::__construct(
            $properties + [
                'name' => $name,
                'scopes' => $scopes,
            ]
        );
    }
}
