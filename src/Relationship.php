<?php

namespace Relationships;

use Relationships\Exceptions\InvalidRelationshipItem;
use Relationships\Exceptions\InvalidRelationshipObject;
use Relationships\Exceptions\MissingProperty;
use Relationships\Exceptions\PropertyMatching;
use Relationships\Exceptions\UndefinedRelationship;

abstract class Relationship
{
    /** @var object */
    protected $rootItem;

    /** @var array */
    protected $relations = [];

    public function __construct(object $rootItem)
    {
        if (!is_a($rootItem, $this->item)) {
            throw new InvalidRelationshipItem('item is not an instance of ' . $this->item);
        }

        $this->rootItem = $rootItem;
    }

    public function add($relationshipObject): void
    {
        if (is_array($relationshipObject)) {
            foreach ($relationshipObject as $current) {
                $this->addRelation($current);
            }
            return;
        }

        if (is_object($relationshipObject)) {
            $this->addRelation($relationshipObject);
            return;
        }

        throw new InvalidRelationshipObject('relation must be an array or an object');
    }

    private function addRelation(object $relationshipObject): void
    {
        foreach ($this->relationships as $rootItemProperty => $relationshipConfiguration) {
            if (!property_exists($this->rootItem, $rootItemProperty)) {
                throw new MissingProperty('Object ' . $this->item . ' does not have property ' . $rootItemProperty);
            }

            if (!isset($relationshipConfiguration[get_class($relationshipObject)])) {
                throw new UndefinedRelationship(get_class($relationshipObject) . ' relationship has not been defined');
            }

            $relationProperty = $relationshipConfiguration[get_class($relationshipObject)];

            if (!property_exists($relationshipObject, $relationProperty)) {
                throw new MissingProperty('Object ' . get_class($relationshipObject) . ' does not have property ' . $relationProperty);
            }

            if ($relationshipObject->{$relationProperty} !== $this->rootItem->{$rootItemProperty}) {
                throw new PropertyMatching('Relation property ' . $relationProperty . ' does not match Object property' . $rootItemProperty);
            }

            array_push($this->relations, $relationshipObject);
        }
    }


    public function get(string $relationClass): array
    {
        return array_filter($this->relations, function ($relation) use ($relationClass) {
            return get_class($relation) == $relationClass;
        });
    }

    public function first(string $relationClass): ?object
    {
        $items = $this->get($relationClass);

        return isset($items[0]) ? $items[0] : null;
    }
}
