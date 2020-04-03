<?php

use Relationships\Relationship;
use PHPUnit\Framework\TestCase;

class User
{
    public $uuid = 'user123';
}

class Account
{
    public $uuid = 'account456';
    public $user_uuid = 'user123';
}

class Movement
{
    public $uuid = 'movement789';
    public $account_uuid = 'account456';
}


class UserAccountRelationship extends Relationship
{
    protected $item = User::class;

    protected $relationships = [
        'uuid' => [AccountMovementRelationship::class => 'user_uuid'],
    ];
}

class AccountMovementRelationship extends Relationship
{
    protected $item = Account::class;

    protected $relationships = [
        'uuid' => [Movement::class => 'account_uuid'],
    ];

    protected $user_uuid;

    public function setUserUuid(User $user)
    {
        $this->user_uuid = $user->uuid;
    }
}

final class RelationshipsInsideRelationshipsTest extends TestCase
{
    public function test_relationship_contains_relationship()
    {
        $this->user = new User();
        $this->account = new Account();
        $this->movement = new Movement();

        $this->accountMovement = new AccountMovementRelationship($this->account);
        $this->accountMovement->add($this->movement);
        $this->accountMovement->setUserUuid($this->user);

        $this->userAccount = new UserAccountRelationship($this->user);
        $this->userAccount->add($this->accountMovement);

        $this->assertEquals(
            $this->userAccount->first(AccountMovementRelationship::class)->first(Movement::class)->uuid,
            $this->movement->uuid
        );
    }
}
