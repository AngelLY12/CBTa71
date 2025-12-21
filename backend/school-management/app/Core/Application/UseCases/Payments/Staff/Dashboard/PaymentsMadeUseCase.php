<?php

namespace App\Core\Application\UseCases\Payments\Staff\Dashboard;

use App\Core\Application\DTO\Response\Payment\FinancialSummaryResponse;
use App\Core\Application\Mappers\PaymentMapper;
use App\Core\Domain\Repositories\Query\Payments\PaymentQueryRepInterface;
use App\Core\Domain\Repositories\Query\Stripe\StripeGatewayQueryInterface;

class PaymentsMadeUseCase{
 public function __construct(
        private PaymentQueryRepInterface $pqRepo,
        private StripeGatewayQueryInterface $stripeGateway,
    )
    {
    }
    public function execute(bool $onlyThisYear): FinancialSummaryResponse
    {
        $grossData= $this->pqRepo->getAllPaymentsMade($onlyThisYear);
        $balanceData= $this->stripeGateway->getBalanceFromStripe();
        $payoutsData= $this->stripeGateway->getPayoutsFromStripe($onlyThisYear);

        $totalPayments = $grossData['total'];
        $paymentsBySemester = $this->groupBySemester($grossData['by_month']);

        $totalPayouts = $payoutsData['total'];
        $totalFees    = $payoutsData['total_fee'];
        $payoutsBySemester = $this->groupPayoutsBySemester($payoutsData['by_month']);

        [$totalAvailable,$totalPending, $availableBySource, $pendingBySource]=$this->formattBalance($balanceData);
        [$availablePercentage, $pendingPercentage, $netReceivedPercentage, $feePercentage]=$this->calculatePercentages($totalPayments, $totalAvailable, $totalPending, $totalPayouts, $totalFees);

        return PaymentMapper::toFinancialSummaryResponse(
            $totalPayments,
            $paymentsBySemester,
            $totalPayouts,
            $totalFees,
            $payoutsBySemester,
            $totalAvailable,
            $totalPending,
            $availableBySource,
            $pendingBySource,
            $availablePercentage,
            $pendingPercentage,
            $netReceivedPercentage,
            $feePercentage
        );


    }

    private function formattBalance(array $balanceData): array
    {
        $available = '0';
        $availableBySource = [];
        $pending = '0';
        $pendingBySource = [];
        foreach ($balanceData['available'] as $b) {
            $available = bcadd($available, $b['amount'], 2);
            $this->aggregateBySource($b['source_types'], $availableBySource);
        }

        foreach ($balanceData['pending'] as $b) {
            $pending = bcadd($pending, $b['amount'], 2);
            $this->aggregateBySource($b['source_types'], $pendingBySource);
        }
        return [$available,$pending, $availableBySource, $pendingBySource];
    }

    private function aggregateBySource(array $sourceTypes, array &$target): void
    {
        foreach ($sourceTypes as $type => $amount) {
            $target[$type] = bcadd(
                $target[$type] ?? '0',
                bcdiv($amount, '100', 2),
                2
            );
        }
    }

    private function groupPayoutsBySemester(array $payoutsByMonth): array
    {
        ksort($payoutsByMonth);
        $result = [];
        foreach ($payoutsByMonth as $month => $data) {
            $key = $this->getSemesterKey($month);
            if(!isset($result[$key])) {
                $result[$key] = [
                    'total' => '0.00',
                    'total_fee' => '0.00',
                    'months' =>[]
                ];
            }
            $result[$key]['months'][] = [
                'amount' => $data['amount'],
                'fee'    => $data['fee'],
            ];

            $result[$key]['total'] = bcadd($result[$key]['total'], $data['amount'], 2);
            $result[$key]['total_fee'] = bcadd($result[$key]['total_fee'], $data['fee'], 2);

        }
        return $result;
    }

    private function groupBySemester(array $byMonth): array
    {
        ksort($byMonth);
        $result = [];

        foreach ($byMonth as $month => $amount) {
            $key = $this->getSemesterKey($month);

            if (!isset($result[$key])) {
                $result[$key] = [
                    'total'  => '0.00',
                    'months' => []
                ];
            }

            $result[$key]['months'][] = $amount;
            $result[$key]['total'] = bcadd($result[$key]['total'], $amount, 2);
        }

        return $result;
    }

    private function getSemesterKey(string $month): string
    {
        [$year, $monthNum] = explode('-', $month);
        $semester = (int)$monthNum <= 6 ? 'H1' : 'H2';
        return "$year-$semester";
    }


    private function calculatePercentages(
        string $totalPayments, string $totalAvailable,
        string $totalPending, string $totalPayouts,
        string $totalFees): array
    {
        if ($totalPayments === '0') {
            return ['0.00', '0.00', '0.00', '0.00'];
        }

        return [
            $this->calculatePercentage($totalAvailable, $totalPayments),
            $this->calculatePercentage($totalPending, $totalPayments),
            $this->calculatePercentage($totalPayouts, $totalPayments),
            $this->calculatePercentage($totalFees, $totalPayments),
        ];
    }
    private function calculatePercentage(string $part, string $total): string
    {
        return bcdiv(bcmul($part, '100', 2), $total, 2);
    }

}
