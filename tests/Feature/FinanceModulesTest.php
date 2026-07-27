<?php

use App\Models\BudgetItem;
use App\Models\Event;
use App\Models\Expense;
use App\Models\Investment;
use App\Models\User;
use App\Services\EventFinancialSummary;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

uses(RefreshDatabase::class);

test('finance can add budget investment and expense', function () {
    Storage::fake('public');

    $finance = User::factory()->finance()->create();
    $investor = User::factory()->investor()->create();
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
        ->test('pages::events.investments', ['event' => $event])
        ->set('user_id', $investor->id)
        ->set('amount', '75000')
        ->set('share_percent', '30')
        ->set('invested_at', now()->format('Y-m-d\TH:i'))
        ->set('proof', UploadedFile::fake()->create('proof.pdf', 100, 'application/pdf'))
        ->call('save')
        ->assertHasNoErrors();

    expect(Investment::query()->count())->toBe(1);

    Livewire::actingAs($finance)
        ->test('pages::events.expenses', ['event' => $event])
        ->set('spent_by_user_id', $finance->id)
        ->set('category', 'Decor')
        ->set('description', 'Flowers')
        ->set('amount', '8000')
        ->set('spent_at', now()->format('Y-m-d\TH:i'))
        ->set('receipt', UploadedFile::fake()->image('receipt.jpg'))
        ->call('save')
        ->assertHasNoErrors();

    expect(Expense::query()->count())->toBe(1);

    $summary = app(EventFinancialSummary::class)->for($event);

    expect((float) $summary['budget'])->toBe(50000.0)
        ->and((float) $summary['invested'])->toBe(75000.0)
        ->and((float) $summary['expenses'])->toBe(8000.0)
        ->and((float) $summary['budget_variance'])->toBe(42000.0);
});
