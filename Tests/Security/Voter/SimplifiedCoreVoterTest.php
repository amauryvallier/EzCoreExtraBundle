<?php

/*
 * This file is part of the EzCoreExtraBundle package.
 *
 * (c) Jérôme Vieilledent <jerome@vieilledent.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Lolautruche\EzCoreExtraBundle\Tests\Security\Voter;

use eZ\Publish\API\Repository\Values\ValueObject;
use eZ\Publish\Core\MVC\Symfony\Security\Authorization\Attribute;
use Lolautruche\EzCoreExtraBundle\Security\Voter\SimplifiedCoreVoter;
use PHPUnit_Framework_TestCase;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class SimplifiedCoreVoterTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|VoterInterface
     */
    private $coreVoter;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|VoterInterface
     */
    private $valueObjectVoter;

    /**
     * @var SimplifiedCoreVoter
     */
    private $voter;

    protected function setUp()
    {
        parent::setUp();
        $this->coreVoter = $this->createMock(VoterInterface::class);
        $this->valueObjectVoter = $this->createMock(VoterInterface::class);
        $this->voter = new SimplifiedCoreVoter($this->coreVoter, $this->valueObjectVoter);
    }

    /**
     * @dataProvider supportsAttributeProvider
     */
    public function testSupportsAttribute($attribute, $expectedResult)
    {
        $this->assertSame($expectedResult, $this->voter->supportsAttribute($attribute));
    }

    public function supportsAttributeProvider()
    {
        return [
            ['foo', false],
            ['bar', false],
            [SimplifiedCoreVoter::EZ_ROLE_PREFIX.'foo:bar', true],
            [new \stdClass(), false],
            [[], false]
        ];
    }

    public function testVoteNotSupportedAttribute()
    {
        $this->assertSame(
            VoterInterface::ACCESS_ABSTAIN,
            $this->voter->vote($this->createMock(TokenInterface::class), null, ['foo'])
        );
    }

    public function testVoteGrantedNoValueObject()
    {
        $token = $this->createMock(TokenInterface::class);
        $object = null;
        $attribute = 'ez:foo:bar';
        $attributeObject = new Attribute('foo', 'bar');
        $this->coreVoter
            ->expects($this->once())
            ->method('vote')
            ->with($token, $object, [$attributeObject])
            ->willReturn(VoterInterface::ACCESS_GRANTED);
        $this->valueObjectVoter
            ->expects($this->never())
            ->method('vote');

        $this->assertSame(
            VoterInterface::ACCESS_GRANTED,
            $this->voter->vote($token, $object, [$attribute])
        );
    }

    public function testVoteDeniedNoValueObject()
    {
        $token = $this->createMock(TokenInterface::class);
        $object = null;
        $attribute = 'ez:foo:bar';
        $attributeObject = new Attribute('foo', 'bar');
        $this->coreVoter
            ->expects($this->once())
            ->method('vote')
            ->with($token, $object, [$attributeObject])
            ->willReturn(VoterInterface::ACCESS_DENIED);
        $this->valueObjectVoter
            ->expects($this->never())
            ->method('vote');

        $this->assertSame(
            VoterInterface::ACCESS_DENIED,
            $this->voter->vote($token, $object, [$attribute])
        );
    }

    public function testVoteGrantedWithValueObject()
    {
        $token = $this->createMock(TokenInterface::class);
        $object = $this->createMock(ValueObject::class);
        $attribute = 'ez:foo:bar';
        $attributeObject = new Attribute('foo', 'bar', ['valueObject' => $object]);
        $this->valueObjectVoter
            ->expects($this->once())
            ->method('vote')
            ->with($token, $object, [$attributeObject])
            ->willReturn(VoterInterface::ACCESS_GRANTED);
        $this->coreVoter
            ->expects($this->never())
            ->method('vote');

        $this->assertSame(
            VoterInterface::ACCESS_GRANTED,
            $this->voter->vote($token, $object, [$attribute])
        );
    }

    public function testVoteDeniedWithValueObject()
    {
        $token = $this->createMock(TokenInterface::class);
        $object = $this->createMock(ValueObject::class);
        $attribute = 'ez:foo:bar';
        $attributeObject = new Attribute('foo', 'bar', ['valueObject' => $object]);
        $this->valueObjectVoter
            ->expects($this->once())
            ->method('vote')
            ->with($token, $object, [$attributeObject])
            ->willReturn(VoterInterface::ACCESS_DENIED);
        $this->coreVoter
            ->expects($this->never())
            ->method('vote');

        $this->assertSame(
            VoterInterface::ACCESS_DENIED,
            $this->voter->vote($token, $object, [$attribute])
        );
    }
}
