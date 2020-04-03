<?php

use Relationships\Relationship;
use PHPUnit\Framework\TestCase;
use Relationships\Exceptions\InvalidRelationshipItem;
use Relationships\Exceptions\InvalidRelationshipObject;
use Relationships\Exceptions\MissingProperty;

class Product
{
    public $uuid = '123';
}

class Post
{
    // public $product_uuid = '123';
}

class MissingPropertyRelationship extends Relationship
{
    protected $item = Product::class;

    protected $relationships = [
        'uuid' => [Post::class => 'product_uuid'],
    ];
}

final class MissingPropertyTest extends TestCase
{
    public function test_missing_property()
    {
        $this->expectException(MissingProperty::class);

        $this->relationship = new MissingPropertyRelationship(new Product());

        $this->relation = new Post();

        $this->relationship->add($this->relation);
    }
}
