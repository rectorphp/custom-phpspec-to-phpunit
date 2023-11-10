<?php

namespace spec\Sift;

use Core\Entities\Card\CurveCard;
use Core\Entities\Card\CurveCardRepositoryInterface;
use Core\Models\MonetaryAmount;
use Core\Models\UserInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Psr\Log\LoggerInterface;
use Sift\TrackingRule;
use Sift\TrackingRulesRepository;
use Transaction\IssuerRequest;
use Transaction\Transaction;

class TrackingRulesPart2Spec extends ObjectBehavior
{
    /**
     * @var int
     */
    private const USER_ID = 'userId';

    public function it_should_not_track_a_transaction_because_not_reaching_minimum_trackable_amount(
        TrackingRulesRepository $trackingRulesRepository,
        TrackingRule $trackingRule
    ) {
        $trackingRulesRepository->findForUser(self::USER_ID)->willReturn($trackingRule);
        $trackingRule->getDaysSinceFirstTransaction()->willReturn(10);

        $this->transactionShouldBeTracked(
            self::USER_ID,
            false,
            new MonetaryAmount(300, 'GBP'),
            null
        )->shouldBe(false);
    }

    public function it_should_not_track_a_transaction_because_not_reaching_minimum_trackable_amount_using_initial_issuer_request(
        TrackingRulesRepository $trackingRulesRepository,
        TrackingRule $trackingRule
    ) {
        $trackingRulesRepository->findForUser(self::USER_ID)->willReturn($trackingRule);
        $trackingRule->getDaysSinceFirstTransaction()->willReturn(10);

        $this->transactionShouldBeTracked(
            self::USER_ID,
            false,
            new MonetaryAmount(0, 'GBP'),
            new MonetaryAmount(300, 'GBP')
        )->shouldBe(false);
    }
}

?>
-----
<?php

namespace Tests\Sift;

use Core\Entities\Card\CurveCard;
use Core\Entities\Card\CurveCardRepositoryInterface;
use Core\Models\MonetaryAmount;
use Core\Models\UserInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sift\TrackingRule;
use Sift\TrackingRules;
use Sift\TrackingRulesRepository;
use Transaction\IssuerRequest;
use Transaction\Transaction;

final class TrackingRulesPart2Test extends \PHPUnit\Framework\TestCase
{
    private \Sift\TrackingRules $trackingRules;
    /**
     * @var int
     */
    private const USER_ID = 'userId';

    public function testNotTrackATransactionBecauseNotReachingMinimumTrackableAmount(): void
    {
        /** @var TrackingRule|MockObject $trackingRule */
        $trackingRule = $this->createMock(TrackingRule::class);

        // these will never be called
        $this->trackingRulesRepositoryMock->expects($this->never())->method('findForUser');
        $trackingRule->expects($this->never())->method('getDaysSinceFirstTransaction');

        $this->assertFalse($this->trackingRules->transactionShouldBeTracked(
            self::USER_ID,
            false,
            new MonetaryAmount(300, 'GBP'),
            null
        ));
    }

    public function testNotTrackATransactionBecauseNotReachingMinimumTrackableAmountUsingInitialIssuerRequest(): void
    {
        /** @var TrackingRule|MockObject $trackingRule */
        $trackingRule = $this->createMock(TrackingRule::class);

        $this->trackingRulesRepositoryMock
            ->expects($this->never())
            ->method('findForUser');

        $trackingRule->expects($this->never())
            ->method('getDaysSinceFirstTransaction');

        $this->assertFalse($this->trackingRules->transactionShouldBeTracked(
            self::USER_ID,
            false,
            new MonetaryAmount(0, 'GBP'),
            new MonetaryAmount(300, 'GBP')
        ));
    }
}

?>
