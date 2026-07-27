<?php

namespace Database\Seeders;

use App\Enums\PackageType;
use App\Enums\PaymentStatus;
use App\Models\Booking;
use App\Models\BudgetItem;
use App\Models\Event;
use App\Models\Expense;
use App\Models\Investment;
use App\Models\User;
use App\Support\TicketCodeGenerator;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $admin = User::factory()->admin()->create([
            'name' => 'Admin User',
            'email' => 'admin@rangrez.test',
            'password' => Hash::make('password'),
        ]);

        $finance = User::factory()->finance()->create([
            'name' => 'Finance User',
            'email' => 'finance@rangrez.test',
            'password' => Hash::make('password'),
        ]);

        User::factory()->checkIn()->create([
            'name' => 'Check-in Staff',
            'email' => 'checkin@rangrez.test',
            'password' => Hash::make('password'),
        ]);

        $investor = User::factory()->investor()->create([
            'name' => 'Investor One',
            'email' => 'investor@rangrez.test',
            'password' => Hash::make('password'),
        ]);

        User::factory()->admin()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
        ]);

        $event = Event::factory()->published()->create([
            'title' => 'RangRez Summer Night',
            'slug' => 'rangrez-summer-night',
            'description' => 'An evening of color, music, and community.',
            'venue' => 'Lahore Arts Pavilion',
            'starts_at' => now()->addWeeks(3),
            'ends_at' => now()->addWeeks(3)->addHours(5),
            'capacity' => 150,
            'created_by' => $admin->id,
        ]);

        $earlyBird = $event->ticketPackages()->create([
            'name' => 'Early Bird',
            'type' => PackageType::EarlyBird,
            'price' => 1500,
            'capacity' => 80,
            'sales_start_at' => now()->subWeek(),
            'sales_end_at' => now()->addDays(10),
            'is_active' => true,
        ]);

        $event->ticketPackages()->create([
            'name' => 'Normal',
            'type' => PackageType::Normal,
            'price' => 2500,
            'capacity' => 150,
            'sales_start_at' => now()->addDays(10)->addSecond(),
            'sales_end_at' => $event->starts_at,
            'is_active' => true,
        ]);

        BudgetItem::factory()->for($event)->create([
            'category' => 'Venue',
            'label' => 'Hall rental',
            'expected_amount' => 80000,
        ]);

        BudgetItem::factory()->for($event)->create([
            'category' => 'Catering',
            'label' => 'Food & drinks',
            'expected_amount' => 120000,
        ]);

        Investment::factory()->create([
            'event_id' => $event->id,
            'user_id' => $investor->id,
            'amount' => 100000,
            'share_percent' => 40,
        ]);

        Expense::factory()->create([
            'event_id' => $event->id,
            'spent_by_user_id' => $finance->id,
            'category' => 'Marketing',
            'description' => 'Social ads',
            'amount' => 15000,
        ]);

        $generator = app(TicketCodeGenerator::class);

        Booking::factory()->create([
            'event_id' => $event->id,
            'ticket_package_id' => $earlyBird->id,
            'name' => 'Ayesha Khan',
            'email' => 'ayesha@example.com',
            'ticket_code' => $generator->generate(),
            'payment_status' => PaymentStatus::Paid,
            'price' => $earlyBird->price,
            'amount_paid' => $earlyBird->price,
        ]);

        Booking::factory()->create([
            'event_id' => $event->id,
            'ticket_package_id' => $earlyBird->id,
            'name' => 'Bilal Ahmed',
            'email' => 'bilal@example.com',
            'ticket_code' => $generator->generate(),
            'payment_status' => PaymentStatus::Pending,
            'price' => $earlyBird->price,
            'amount_paid' => 0,
        ]);
    }
}
