<?php
/**
 * @author Rik van der Kemp <rik@zicht.nl>
 * @copyright Zicht Online <http://www.zicht.nl>
 */

namespace Zicht\Bundle\PageBundle\Security\Voter;

use PHPUnit\Framework\TestCase;
use \Symfony\Component\Security\Core\Authentication\Token\AnonymousToken;
use \Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use \Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use \Zicht\Bundle\PageBundle\Model\ScheduledContentInterface;

/**
 * Check content against the scheduled dates
 *
 * @package Zicht\Bundle\PageBundle\Security\Voter
 */
class ScheduledContentVoterTest extends TestCase
{

    /**
     * Run voter with incorrect class, should abstain
     */
    function testScheduledContentVoterShouldAbstainWithIncorrectClass()
    {
        $token = new AnonymousToken('key', 'user');
        $voter = new ScheduledContentVoter();
        $vote = $voter->vote($token, new \stdClass(), array('VIEW'));
        $this->assertEquals(VoterInterface::ACCESS_ABSTAIN, $vote);
    }

    /**
     * Check if voter abstains when both dates or NULL
     */
    function testScheduledContentVoterShouldAbstainWhenDatesOrNull() {
        $mock = new MockScheduledObject();
        $token = new AnonymousToken('key', 'user');
        $voter = new ScheduledContentVoter();
        $vote = $voter->vote($token, $mock, array('VIEW'));

        $this->assertEquals(VoterInterface::ACCESS_ABSTAIN, $vote);
    }

    /**
     * Check if voter grants access when from is filled in and is correct
     */
    function testScheduledContentVoterShouldSayAccessGrantedWhenDateFromIsCorrectAndDateTillIsNull() {
        $mock = new MockScheduledObject();
        $mock->from = new \DateTime('yesterday');

        $token = new AnonymousToken('key', 'user');
        $voter = new ScheduledContentVoter();
        $vote = $voter->vote($token, $mock, array('VIEW'));

        $this->assertEquals(VoterInterface::ACCESS_GRANTED, $vote);
    }

    /**
     * Check if voter grants access when till is filled in and is correct
     */
    function testScheduledContentVoterShouldSayAccessGrantedWhenDateTillIsCorrectAndDateFromIsNull() {
        $mock = new MockScheduledObject();
        $mock->from = null;
        $mock->till = new \DateTime('tomorrow');

        $token = new AnonymousToken('key', 'user');
        $voter = new ScheduledContentVoter();
        $vote = $voter->vote($token, $mock, array('VIEW'));

        $this->assertEquals(VoterInterface::ACCESS_GRANTED, $vote);
    }

    /**
     * Check if voter grants access when from and till are both within current date
     */
    function testScheduledContentVoterShouldSayAccessGrantedWhenDateBetweenFromAndTill() {
        $mock = new MockScheduledObject();
        $mock->from = new \DateTime('yesterday');
        $mock->till = new \DateTime('tomorrow');

        $token = new AnonymousToken('key', 'user');
        $voter = new ScheduledContentVoter();
        $vote = $voter->vote($token, $mock, array('VIEW'));

        $this->assertEquals(VoterInterface::ACCESS_GRANTED, $vote);
    }

    /**
     * Check access denied when till value is expired
     */
    function testScheduledContentVoterShouldSayAccessDeniedWhenDateIsNotBetweenFromAndTillWhereTillHasPassed() {
        $mock = new MockScheduledObject();
        $mock->from = new \DateTime('yesterday');
        $mock->till = new \DateTime('3 hours ago');

        $token = new AnonymousToken('key', 'user');
        $voter = new ScheduledContentVoter();
        $vote = $voter->vote($token, $mock, array('VIEW'));

        $this->assertEquals(VoterInterface::ACCESS_DENIED, $vote);
    }

    /**
     * Check access denied when from is not now
     */
    function testScheduledContentVoterShouldSayAccessDeniedWhenDateIsNotBetweenFromAndTillWhereFromIsNotNow() {
        $mock = new MockScheduledObject();
        $mock->from = new \DateTime('+3 hours');
        $mock->till = new \DateTime('tomorrow');

        $token = new AnonymousToken('key', 'user');
        $voter = new ScheduledContentVoter();
        $vote = $voter->vote($token, $mock, array('VIEW'));

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