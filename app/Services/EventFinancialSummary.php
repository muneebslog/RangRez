<?php

namespace App\Services;

use App\Enums\PaymentStatus;
use App\Models\Event;

class EventFinancialSummary
{
    /**
     * @return array{
     *     booked: int,
     *     paid: int,
     *     pending: int,
     *     checked_in: int,
     *     revenue: string,
     *     invested: string,
     *     expenses: string,
     *     budget: string,
     *     budget_variance: string,
     *     profit_loss: string
     * }
     */
    public function for(Event $event): array
    {
        $booked = $event->bookings()->count();
        $paid = $event->bookings()->where('payment_status', PaymentStatus::Paid)->count();
        $pending = $event->bookings()->where('payment_status', PaymentStatus::Pending)->count();
        $checkedIn = $event->bookings()->whereNotNull('checked_in_at')->count();

        $revenue = number_format((float) $event->bookings()
            ->where('payment_status', PaymentStatus::Paid)
            ->sum('amount_paid'), 2, '.', '');
        $invested = number_format((float) $event->investments()->sum('amount'), 2, '.', '');
        $expenses = number_format((float) $event->expenses()->sum('amount'), 2, '.', '');
        $budget = number_format((float) $event->budgetItems()->sum('expected_amount'), 2, '.', '');

        return [
            'booked' => $booked,
            'paid' => $paid,
            'pending' => $pending,
            'checked_in' => $checkedIn,
            'revenue' => $revenue,
            'invested' => $invested,
            'expenses' => $expenses,
            'budget' => $budget,
            'budget_variance' => bcsub($budget, $expenses, 2),
            'profit_loss' => bcsub($revenue, $expenses, 2),
        ];
    }
}
