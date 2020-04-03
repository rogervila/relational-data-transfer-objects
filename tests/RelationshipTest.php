<?php

use Relationships\Relationship;
use PHPUnit\Framework\TestCase;
use Relationships\Exceptions\InvalidRelationshipItem;
use Relationships\Exceptions\InvalidRelationshipObject;
use Relationships\Exceptions\PropertyMatching;
use Relationships\Exceptions\UndefinedRelationship;

class StripeProduct
{
    public $uuid = '123';
}

class WordpressPost
{
    public function __construct($id)
    {
        $this->id = $id;
    }

    public $product_uuid = '123';
}

class Tag
{
    public $product_uuid = '123';
}

class ProductRelationship extends Relationship
{
    protected $item = StripeProduct::class;

    protected $relationships = [
        'uuid' => [WordpressPost::class => 'product_uuid'],
    ];
}

final class RelationshipTest extends TestCase
{
    public function test_relationship()
    {
        $this->product = new StripeProduct();
        $this->relationship = new ProductRelationship($this->product);
        $this->relationId = 'lol';
        $this->relation = new WordpressPost($this->relationId);

        $this->relationship->add($this->relation);

        $this->assertTrue(is_array($this->relationship->get(WordpressPost::class)));
        $this->assertCount(1, $this->relationship->get(WordpressPost::class));
        $this->assertEquals($this->relationship->first(WordpressPost::class)->id, $this->relationId);
    }

    public function test_relationships_can_be_passed_as_array()
    {
        $this->product = new StripeProduct();
        $this->relationship = new ProductRelationship($this->product);

        $this->relationId1 = 'lol';
        $this->relation1 = new WordpressPost($this->relationId1);

        $this->relationId2 = 'siuu';
        $this->relation2 = new WordpressPost($this->relationId2);

        $this->relationship->add([$this->relation1, $this->relation2]);

        $this->assertCount(2, $this->relationship->get(WordpressPost::class));
        $this->assertEquals($this->relationship->get(WordpressPost::class)[0]->id, $this->relationId1);
        $this->assertEquals($this->relationship->get(WordpressPost::class)[1]->id, $this->relationId2);
    }

    public function test_first_method_returns_null_if_empty_relations()
    {
        $this->relationship = new ProductRelationship(new StripeProduct());

        $this->assertNull($this->relationship->first(WordpressPost::class));
    }

    public function test_invalid_relationship_item()
    {
        $this->expectException(InvalidRelationshipItem::class);

        new ProductRelationship(new WordpressPost('lol'));
    }

    public function test_invalid_relationship_object()
    {
        $this->expectException(InvalidRelationshipObject::class);

        $this->relationship = new ProductRelationship(new StripeProduct());

        $this->relationship->add('invalid');
    }

    public function test_undefined_relationship()
    {
        $this->expectException(UndefinedRelationship::class);

        $this->relationship = new ProductRelationship(new StripeProduct());

        $this->relationship->add(new Tag());
    }

    public function test_property_matching()
    {
        $this->expectException(PropertyMatching::class);

        $this->relation = new WordpressPost('lol');
        $this->relation->product_uuid = 'does not match';

        $this->relationship = new ProductRelationship(new StripeProduct());

        $this->relationship->add($this->relation);
    }
}
