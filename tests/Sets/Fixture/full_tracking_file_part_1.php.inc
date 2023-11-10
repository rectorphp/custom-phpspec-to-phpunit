<?php

namespace spec\Sift;

use Core\Entities\Card\CurveCard;
use Core\Entities\Card\CurveCardRepositoryInterface;
use Core\Models\MonetaryAmount;
use Core\Models\UserInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Sift\TrackingRule;
use Sift\TrackingRulesRepository;
use Transaction\IssuerRequest;
use Transaction\Transaction;

class TrackingRulesPart1Spec extends ObjectBehavior
{
    /**
     * @var int
     */
    private const USER_ID = 'userId';

    public function let(
        CurveCardRepositoryInterface $curveCardRepository,
        TrackingRulesRepository $trackingRulesRepository
    ) {
        $this->beConstructedWith($curveCardRepository, $trackingRulesRepository);
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
}

?>
-----
<?php

namespace Tests\Sift;

use Core\Entities\Card\CurveCard;
use Core\Entities\Card\CurveCardRepositoryInterface;
use Core\Models\MonetaryAmount;
use Core\Models\UserInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Sift\TrackingRule;
use Sift\TrackingRulesRepository;
use Transaction\IssuerRequest;
use Transaction\Transaction;

final class TrackingRulesPart1Test extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Core\Entities\Card\CurveCardRepositoryInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private \PHPUnit\Framework\MockObject\Generator\MockType $curveCardRepositoryMock;
    /**
     * @var \Sift\TrackingRulesRepository|\PHPUnit\Framework\MockObject\MockObject
     */
    private \PHPUnit\Framework\MockObject\Generator\MockType $trackingRulesRepositoryMock;
    private \Sift\TrackingRulesPart1 $trackingRulesPart1;
    /**
     * @var int
     */
    private const USER_ID = 'userId';

    protected function setUp(): void
    {
        $this->curveCardRepositoryMock = $this->createMock(\Core\Entities\Card\CurveCardRepositoryInterface::class);
        $this->trackingRulesRepositoryMock = $this->createMock(\Sift\TrackingRulesRepository::class);
        $this->trackingRulesPart1 = new \Sift\TrackingRulesPart1($this->curveCardRepositoryMock, $this->trackingRulesRepositoryMock);
    }

    public function testUpdatesTheTrackingRulesForAUser(): void
    {
        /** @var \Transaction\Transaction|\PHPUnit\Framework\MockObject\MockObject $transactionMock */
        $transactionMock = $this->createMock(Transaction::class);
        /** @var IssuerRequest|MockObject $issuerRequestMock */
        $issuerRequestMock = $this->createMock(IssuerRequest::class);
        /** @var MockObject|UserInterface $userMock */
        $userMock = $this->createMock(UserInterface::class);
        /** @var MockObject|CurveCard $curveCardMock */
        $curveCardMock = $this->createMock(CurveCard::class);
        $curveCardMock->expects($this->once())->method('getUser')->willReturn($userMock);
        $this->curveCardRepositoryMock->expects($this->once())->method('findOneByGlobalId')->with('curveCardGlobalId')->willReturn($curveCardMock);
        $transactionMock->expects($this->once())->method('getIssuerRequests')->willReturn([$issuerRequestMock]);
        $issuerRequestMock->expects($this->once())->method('getCurveCardId')->willReturn('curveCardGlobalId');
        $this->trackingRulesRepositoryMock->expects($this->once())->method('findForUser')->with(self::USER_ID);
        $userMock->expects($this->once())->method('getId')->willReturn(self::USER_ID);
        $this->trackingRulesRepositoryMock->expects($this->once())->method('save');
        $this->trackingRulesPart1->updateTrackingRules($transactionMock);
    }

    public function testNotTrackATransactionBecauseOldCustomerAndSmallValue(): void
    {
        /** @var \Rector\PhpSpecToPHPUnit\Tests\Sets\Fixture\Tracking\TrackingRule|MockObject $trackingRuleMock */
        $trackingRuleMock = $this->createMock(\Rector\PhpSpecToPHPUnit\Tests\Sets\Fixture\Tracking\TrackingRule::class);
        $this->trackingRulesRepositoryMock->expects($this->once())->method('findForUser')->with(self::USER_ID)->willReturn($trackingRuleMock);
        $trackingRuleMock->expects($this->once())->method('getDaysSinceFirstTransaction')->willReturn(40);
        $this->assertFalse($this->trackingRulesPart1->transactionShouldBeTracked(
            self::USER_ID,
            false,
            new MonetaryAmount(15000, 'GBP'),
            null
        ));
    }

    public function testTrackATransactionEvenForOldCustomerBecauseAtmHighValue(): void
    {
        /** @var \Rector\PhpSpecToPHPUnit\Tests\Sets\Fixture\Tracking\TrackingRule|MockObject $trackingRuleMock */
        $trackingRuleMock = $this->createMock(\Rector\PhpSpecToPHPUnit\Tests\Sets\Fixture\Tracking\TrackingRule::class);
        $this->trackingRulesRepositoryMock->expects($this->once())->method('findForUser')->with(self::USER_ID)->willReturn($trackingRuleMock);
        $trackingRuleMock->expects($this->once())->method('getDaysSinceFirstTransaction')->willReturn(40);
        $this->assertTrue($this->trackingRulesPart1->transactionShouldBeTracked(
            self::USER_ID,
            true,
            new MonetaryAmount(16000, 'GBP'),
            null
        ));
    }

    public function testNotTrackATransactionBecauseOldCustomerAndAtmSmallValue(): void
    {
        /** @var \Rector\PhpSpecToPHPUnit\Tests\Sets\Fixture\Tracking\TrackingRule|MockObject $trackingRuleMock */
        $trackingRuleMock = $this->createMock(\Rector\PhpSpecToPHPUnit\Tests\Sets\Fixture\Tracking\TrackingRule::class);
        $this->trackingRulesRepositoryMock->expects($this->once())->method('findForUser')->with(self::USER_ID)->willReturn($trackingRuleMock);
        $trackingRuleMock->expects($this->once())->method('getDaysSinceFirstTransaction')->willReturn(40);
        $this->assertFalse($this->trackingRulesPart1->transactionShouldBeTracked(
            self::USER_ID,
            true,
            new MonetaryAmount(12000, 'GBP'),
            null
        ));
    }

    public function testTrackATransactionBecauseOldCustomerButHighValue(): void
    {
        /** @var \Rector\PhpSpecToPHPUnit\Tests\Sets\Fixture\Tracking\TrackingRule|MockObject $trackingRuleMock */
        $trackingRuleMock = $this->createMock(\Rector\PhpSpecToPHPUnit\Tests\Sets\Fixture\Tracking\TrackingRule::class);
        $this->trackingRulesRepositoryMock->expects($this->once())->method('findForUser')->with(self::USER_ID)->willReturn($trackingRuleMock);
        $trackingRuleMock->expects($this->once())->method('getDaysSinceFirstTransaction')->willReturn(40);
        $this->assertTrue($this->trackingRulesPart1->transactionShouldBeTracked(
            self::USER_ID,
            false,
            new MonetaryAmount(25000, 'GBP'),
            null
        ));
    }
}

?>
