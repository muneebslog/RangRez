<?php

use App\Models\BudgetItem;
use App\Models\Event;
use App\Models\Expense;
use App\Models\User;
use App\Services\EventFinancialSummary;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('finance can add budget and expenses with repayment tracking', function () {
    Storage::fake('public');

    $finance = User::factory()->finance()->create();
    $event = Event::factory()->published()->create();

    Livewire::actingAs($finance)
        ->test('pages::events.budget', ['event' => $event])
        ->set('category', 'Venue')
        ->set('label', 'Hall')
        ->set('expected_amount', '50000')
        ->call('save')
        ->assertHasNoErrors();

    expect(BudgetItem::query()->count())->toBe(1);

    Livewire::actingAs($finance)
        ->test('pages::events.expenses', ['event' => $event])
        ->set('paid_by', 'company')
        ->set('category', 'Decor')
        ->set('description', 'Flowers')
        ->set('amount', '8000')
        ->set('spent_at', now()->format('Y-m-d\TH:i'))
        ->set('receipt', UploadedFile::fake()->image('receipt.jpg'))
        ->call('save')
        ->assertHasNoErrors();

    Livewire::actingAs($finance)
        ->test('pages::events.expenses', ['event' => $event])
        ->set('paid_by', 'person')
        ->set('spent_by_user_id', $finance->id)
        ->set('category', 'Transport')
        ->set('description', 'Van hire')
        ->set('amount', '2500')
        ->set('spent_at', now()->format('Y-m-d\TH:i'))
        ->call('save')
        ->assertHasNoErrors();

    expect(Expense::query()->count())->toBe(2);

    $summary = app(EventFinancialSummary::class)->for($event);

    expect((float) $summary['budget'])->toBe(50000.0)
        ->and((float) $summary['expenses'])->toBe(10500.0)
        ->and((float) $summary['owed'])->toBe(2500.0)
        ->and((float) $summary['budget_variance'])->toBe(39500.0);

    $personalExpense = Expense::query()->where('paid_by_company', false)->first();

    Livewire::actingAs($finance)
        ->test('pages::events.expenses', ['event' => $event])
        ->call('markRepaid', $personalExpense->id)
        ->assertHasNoErrors();

    expect($personalExpense->fresh()->repaid_at)->not->toBeNull()
        ->and((float) app(EventFinancialSummary::class)->for($event)['owed'])->toBe(0.0);
});

test('person-paid expense requires spent by user', function () {
    $finance = User::factory()->finance()->create();
    $event = Event::factory()->published()->create();

    Livewire::actingAs($finance)
        ->test('pages::events.expenses', ['event' => $event])
        ->set('paid_by', 'person')
        ->set('spent_by_user_id', null)
        ->set('category', 'Misc')
        ->set('description', 'Supplies')
        ->set('amount', '100')
        ->set('spent_at', now()->format('Y-m-d\TH:i'))
        ->call('save')
        ->assertHasErrors(['spent_by_user_id']);
});

test('finance can export bookings csv', function () {
    $finance = User::factory()->finance()->create();
    $event = Event::factory()->published()->create();

    $this->actingAs($finance)
        ->get(route('events.bookings.export', $event))
        ->assertOk()
        ->assertHeader('content-disposition');
});
