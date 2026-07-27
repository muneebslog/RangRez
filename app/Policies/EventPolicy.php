<?php

namespace App\Policies;

use App\Models\Event;
use App\Models\User;

class EventPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isStaff() || $user->isInvestor();
    }

    public function view(User $user, Event $event): bool
    {
        if ($user->isStaff()) {
            return true;
        }

        if ($user->isInvestor()) {
            return $event->investments()->where('user_id', $user->id)->exists();
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->canManageEvents();
    }

    public function update(User $user, Event $event): bool
    {
        return $user->canManageEvents();
    }

    public function delete(User $user, Event $event): bool
    {
        return $user->canManageEvents();
    }

    public function manageFinance(User $user, Event $event): bool
    {
        return $user->canManageFinance();
    }

    public function manageBookings(User $user, Event $event): bool
    {
        return $user->canManageFinance() || $user->canManageEvents();
    }

    public function checkIn(User $user, Event $event): bool
    {
        return $user->canCheckIn();
    }

    public function viewReports(User $user, Event $event): bool
    {
        if ($user->canManageFinance() || $user->canManageEvents()) {
            return true;
        }

        if ($user->isInvestor()) {
            return $event->investments()->where('user_id', $user->id)->exists();
        }

        return false;
    }
}
