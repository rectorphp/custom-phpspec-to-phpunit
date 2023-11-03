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

class TrackingRulesSpec extends ObjectBehavior
{
    /**
     * @var int
     */
    private const USER_ID = 'userId';

    public function let(
        CurveCardRepositoryInterface $curveCardRepository,
        TrackingRulesRepository $trackingRulesRepository,
        LoggerInterface $logger
    ) {
        $this->beConstructedWith($curveCardRepository, $trackingRulesRepository, $logger);
    }

    public function it_updates_the_tracking_rules_for_a_user(
        Transaction $transaction,
        IssuerRequest $issuerRequest,
        CurveCardRepositoryInterface $curveCardRepository,
        CurveCard $curveCard,
        UserInterface $user,
        TrackingRulesRepository $trackingRulesRepository
    ) {
        $curveCard->getUser()->willReturn($user);
        $curveCardRepository->findOneByGlobalId('curveCardGlobalId')->willReturn($curveCard);
        $transaction->getIssuerRequests()->willReturn([$issuerRequest]);
        $issuerRequest->getCurveCardId()->willReturn('curveCardGlobalId');
        $trackingRulesRepository->findForUser(self::USER_ID)->willReturn();
        $user->getId()->willReturn(self::USER_ID);
        $trackingRulesRepository->save(Argument::cetera())->shouldBeCalled();
        $this->updateTrackingRules($transaction);
    }

    public function it_decides_if_we_Should_track_a_given_transaction(
        TrackingRulesRepository $trackingRulesRepository,
        TrackingRule $trackingRule
    ) {
        $trackingRulesRepository->findForUser(self::USER_ID)->willReturn($trackingRule);
        $trackingRule->getDaysSinceFirstTransaction()->willReturn(10);

        $this->transactionShouldBeTracked(
            self::USER_ID,
            true,
            new MonetaryAmount(600, 'GBP'),
            null
        )->shouldBe(true);
    }

    public function it_should_not_track_a_transaction_because_old_customer_and_small_value(
        TrackingRulesRepository $trackingRulesRepository,
        TrackingRule $trackingRule
    ) {
        $trackingRulesRepository->findForUser(self::USER_ID)->willReturn($trackingRule);
        $trackingRule->getDaysSinceFirstTransaction()->willReturn(40);

        $this->transactionShouldBeTracked(
            self::USER_ID,
            false,
            new MonetaryAmount(15000, 'GBP'),
            null
        )->shouldBe(false);
    }

    public function it_should_track_a_transaction_even_for_old_customer_because_atm_high_value(
        TrackingRulesRepository $trackingRulesRepository,
        TrackingRule $trackingRule
    ) {
        $trackingRulesRepository->findForUser(self::USER_ID)->willReturn($trackingRule);
        $trackingRule->getDaysSinceFirstTransaction()->willReturn(40);

        $this->transactionShouldBeTracked(
            self::USER_ID,
            true,
            new MonetaryAmount(16000, 'GBP'),
            null
        )->shouldBe(true);
    }

    public function it_should_not_track_a_transaction_because_old_customer_and_atm_small_value(
        TrackingRulesRepository $trackingRulesRepository,
        TrackingRule $trackingRule
    ) {
        $trackingRulesRepository->findForUser(self::USER_ID)->willReturn($trackingRule);
        $trackingRule->getDaysSinceFirstTransaction()->willReturn(40);

        $this->transactionShouldBeTracked(
            self::USER_ID,
            true,
            new MonetaryAmount(12000, 'GBP'),
            null
        )->shouldBe(false);
    }

    public function it_should_track_a_transaction_because_old_customer_but_high_value(
        TrackingRulesRepository $trackingRulesRepository,
        TrackingRule $trackingRule
    ) {
        $trackingRulesRepository->findForUser(self::USER_ID)->willReturn($trackingRule);
        $trackingRule->getDaysSinceFirstTransaction()->willReturn(40);

        $this->transactionShouldBeTracked(
            self::USER_ID,
            false,
            new MonetaryAmount(25000, 'GBP'),
            null
        )->shouldBe(true);
    }

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

final class TrackingRulesTest extends TestCase
{
    /**
     * @var int
     */
    private const USER_ID = 'userId';

    /**
     * @var MockObject<TrackingRulesRepository>
     */
    private MockObject $trackingRulesRepositoryMock;

    /**
     * @var MockObject<CurveCardRepositoryInterface>
     */
    private MockObject $curveCardRepositoryMock;

    private TrackingRules $trackingRules;

    protected function setUp(): void
    {
        $this->trackingRulesRepositoryMock = $this->createMock(TrackingRulesRepository::class);
        $this->curveCardRepositoryMock = $this->createMock(CurveCardRepositoryInterface::class);

        $this->trackingRules = new TrackingRules($this->curveCardRepositoryMock, $this->trackingRulesRepositoryMock);
    }

    public function testItUpdatesTheTrackingRulesForAUser(): void
    {
        /** @var Transaction|MockObject $transactionMock */
        $transactionMock = $this->createMock(Transaction::class);

        /** @var IssuerRequest|MockObject $issuerRequestMock */
        $issuerRequestMock = $this->createMock(IssuerRequest::class);

        /** @var MockObject<UserInterface> $userMock */
        $userMock = $this->createMock(UserInterface::class);

        /** @var MockObject<CurveCard> $curveCardMock */
        $curveCardMock = $this->createMock(CurveCard::class);
        $curveCardMock->expects($this->once())->method('getUser')->willReturn($userMock);

        $this->curveCardRepositoryMock->expects($this->once())
            ->method('findOneByGlobalId')
            ->with('curveCardGlobalId')
            ->willReturn($curveCardMock);

        $transactionMock->expects($this->once())->method('getIssuerRequests')->willReturn([$issuerRequestMock]);
        $issuerRequestMock->expects($this->once())->method('getCurveCardId')->willReturn('curveCardGlobalId');

        $this->trackingRulesRepositoryMock->expects($this->once())->method('findForUser')->with(self::USER_ID);

        $userMock->expects($this->once())->method('getId')->willReturn(self::USER_ID);
        $this->trackingRulesRepositoryMock->expects($this->once())->method('save');

        $this->trackingRules->updateTrackingRules($transactionMock);
    }

    public function testItDecidesIfWeShouldTrackAGivenTransaction(): void
    {
        /** @var TrackingRule|MockObject $trackingRuleMock */
        $trackingRuleMock = $this->createMock(TrackingRule::class);

        $this->trackingRulesRepositoryMock->expects($this->once())->method('findForUser')->with(self::USER_ID)->willReturn($trackingRuleMock);

        $trackingRuleMock->method('getDaysSinceFirstTransaction')->willReturn(10);

        $this->assertTrue($this->trackingRules->transactionShouldBeTracked(
            self::USER_ID,
            true,
            new MonetaryAmount(600, 'GBP'),
            null
        ));
    }

    public function testNotTrackATransactionBecauseOldCustomerAndSmallValue()
    {
        /** @var TrackingRule|MockObject $trackingRule */
        $trackingRule = $this->createMock(TrackingRule::class);

        $this->trackingRulesRepositoryMock->expects($this->once())->method('findForUser')->with(self::USER_ID)->willReturn($trackingRule);

        $trackingRule->expects($this->once())->method('getDaysSinceFirstTransaction')->willReturn(40);

        $this->assertFalse($this->trackingRules->transactionShouldBeTracked(
            self::USER_ID,
            false,
            new MonetaryAmount(15000, 'GBP'),
            null
        ));
    }

    public function testTrackATransactionEvenForOldCustomerBecauseAtmHighValue(): void
    {
        /** @var TrackingRule|MockObject $trackingRule */
        $trackingRule = $this->createMock(TrackingRule::class);

        $this->trackingRulesRepositoryMock->expects($this->once())->method('findForUser')
            ->with(self::USER_ID)
            ->willReturn($trackingRule);

        $trackingRule->expects($this->once())->method('getDaysSinceFirstTransaction')->willReturn(40);

        $this->assertTrue($this->trackingRules->transactionShouldBeTracked(
            self::USER_ID,
            true,
            new MonetaryAmount(16000, 'GBP'),
            null
        ));
    }

    public function testNotTrackATransactionBecauseOldCustomerAndAtmSmallValue(): void
    {
        /** @var TrackingRule|MockObject $trackingRule */
        $trackingRule = $this->createMock(TrackingRule::class);
        $this->trackingRulesRepositoryMock->expects($this->once())->method('findForUser')->with(self::USER_ID)->willReturn($trackingRule);
        $trackingRule->expects($this->once())->method('getDaysSinceFirstTransaction')->willReturn(40);

        $this->assertFalse($this->trackingRules->transactionShouldBeTracked(
            self::USER_ID,
            true,
            new MonetaryAmount(12000, 'GBP'),
            null
        ));
    }

    public function testTrackATransactionBecauseOldCustomerButHighValue(): void
    {
        /** @var TrackingRule|MockObject $trackingRule */
        $trackingRule = $this->createMock(TrackingRule::class);
        $this->trackingRulesRepositoryMock->expects($this->once())->method('findForUser')->with(self::USER_ID)->willReturn($trackingRule);
        $trackingRule->expects($this->once())->method('getDaysSinceFirstTransaction')->willReturn(40);

        $this->assertTrue($this->trackingRules->transactionShouldBeTracked(
            self::USER_ID,
            false,
            new MonetaryAmount(25000, 'GBP'),
            null
        ));
    }

    public function testNotTrackATransactionBecauseNotReachingMinimumTrackableAmount()
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
