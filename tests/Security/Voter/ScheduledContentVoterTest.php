<?php
/**
 * @copyright Zicht Online <http://www.zicht.nl>
 */

namespace Zicht\Bundle\PageBundle\Security\Voter;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\AnonymousToken;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Zicht\Bundle\PageBundle\Model\ScheduledContentInterface;

/**
 * Check content against the scheduled dates
 */
class ScheduledContentVoterTest extends TestCase
{
    public function testScheduledContentVoterShouldAbstainWithIncorrectClass()
    {
        $token = new AnonymousToken('key', 'user');
        $voter = new ScheduledContentVoter();
        $vote = $voter->vote($token, new \stdClass(), ['VIEW']);
        $this->assertEquals(VoterInterface::ACCESS_ABSTAIN, $vote);
    }

    /**
     * Check if voter abstains when both dates or NULL
     */
    public function testScheduledContentVoterShouldAbstainWhenDatesOrNull()
    {
        $mock = new MockScheduledObject();
        $token = new AnonymousToken('key', 'user');
        $voter = new ScheduledContentVoter();
        $vote = $voter->vote($token, $mock, ['VIEW']);

        $this->assertEquals(VoterInterface::ACCESS_ABSTAIN, $vote);
    }

    /**
     * Check if voter grants access when from is filled in and is correct
     */
    public function testScheduledContentVoterShouldSayAccessGrantedWhenDateFromIsCorrectAndDateTillIsNull()
    {
        $mock = new MockScheduledObject();
        $mock->from = new \DateTime('yesterday');

        $token = new AnonymousToken('key', 'user');
        $voter = new ScheduledContentVoter();
        $vote = $voter->vote($token, $mock, ['VIEW']);

        $this->assertEquals(VoterInterface::ACCESS_GRANTED, $vote);
    }

    /**
     * Check if voter grants access when till is filled in and is correct
     */
    public function testScheduledContentVoterShouldSayAccessGrantedWhenDateTillIsCorrectAndDateFromIsNull()
    {
        $mock = new MockScheduledObject();
        $mock->from = null;
        $mock->till = new \DateTime('tomorrow');

        $token = new AnonymousToken('key', 'user');
        $voter = new ScheduledContentVoter();
        $vote = $voter->vote($token, $mock, ['VIEW']);

        $this->assertEquals(VoterInterface::ACCESS_GRANTED, $vote);
    }

    /**
     * Check if voter grants access when from and till are both within current date
     */
    public function testScheduledContentVoterShouldSayAccessGrantedWhenDateBetweenFromAndTill()
    {
        $mock = new MockScheduledObject();
        $mock->from = new \DateTime('yesterday');
        $mock->till = new \DateTime('tomorrow');

        $token = new AnonymousToken('key', 'user');
        $voter = new ScheduledContentVoter();
        $vote = $voter->vote($token, $mock, ['VIEW']);

        $this->assertEquals(VoterInterface::ACCESS_GRANTED, $vote);
    }

    /**
     * Check access denied when till value is expired
     */
    public function testScheduledContentVoterShouldSayAccessDeniedWhenDateIsNotBetweenFromAndTillWhereTillHasPassed()
    {
        $mock = new MockScheduledObject();
        $mock->from = new \DateTime('yesterday');
        $mock->till = new \DateTime('3 hours ago');

        $token = new AnonymousToken('key', 'user');
        $voter = new ScheduledContentVoter();
        $vote = $voter->vote($token, $mock, ['VIEW']);

        $this->assertEquals(VoterInterface::ACCESS_DENIED, $vote);
    }

    /**
     * Check access denied when from is not now
     */
    public function testScheduledContentVoterShouldSayAccessDeniedWhenDateIsNotBetweenFromAndTillWhereFromIsNotNow()
    {
        $mock = new MockScheduledObject();
        $mock->from = new \DateTime('+3 hours');
        $mock->till = new \DateTime('tomorrow');

        $token = new AnonymousToken('key', 'user');
        $voter = new ScheduledContentVoter();
        $vote = $voter->vote($token, $mock, ['VIEW']);

        $this->assertEquals(VoterInterface::ACCESS_DENIED, $vote);
    }
}

class MockScheduledObject implements ScheduledContentInterface
{
    public $till = null;

    public $from = null;

    /**
     * @return \DateTime|null
     */
    public function isScheduledFrom()
    {
        return $this->from;
    }

    /**
     * @return \DateTime|null
     */
    public function isScheduledTill()
    {
        return $this->till;
    }

    /**
     * @return bool
     */
    public function isPublic()
    {
        return true;
    }
}
