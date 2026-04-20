<?php

namespace Tests\Concerns;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

trait EnsuresStaffProfileSchema
{
    use EnsuresPdpPhaseTwoSchema;

    protected function ensureStaffProfileSchema(): void
    {
        $this->ensurePdpPhaseTwoSchema();
        $this->ensureDepartmentsTable();
        $this->ensureNationalitiesTable();
        $this->ensureAreaOfWorkTable();
        $this->ensureUserPositionsTable();
        $this->ensureUserStatusesTable();
        $this->ensureUserFiltersTable();
        $this->ensureEarningBandsTable();
        $this->ensureRolesTables();
        $this->ensureLoggingsTable();
        $this->ensureEmailsTable();
        $this->ensureMessagesTable();
        $this->ensureQualificationsTables();
        $this->ensureWorkHistoriesTable();
        $this->ensureStaffProfileSettingsTable();
    }

    private function ensureStaffProfileSettingsTable(): void
    {
        if (Schema::hasTable('staff_profile_settings')) {
            return;
        }

        Schema::create('staff_profile_settings', function ($table): void {
            $table->id();
            $table->string('key')->unique();
            $table->json('value')->nullable();
            $table->text('description')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }

    private function ensureDepartmentsTable(): void
    {
        if (Schema::hasTable('departments')) {
            return;
        }

        Schema::create('departments', function ($table): void {
            $table->id();
            $table->string('name');
            $table->unsignedBigInteger('department_head')->nullable();
            $table->unsignedBigInteger('assistant')->nullable();
            $table->timestamps();
        });
    }

    private function ensureNationalitiesTable(): void
    {
        if (Schema::hasTable('nationalities')) {
            return;
        }

        Schema::create('nationalities', function ($table): void {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });
    }

    private function ensureAreaOfWorkTable(): void
    {
        if (Schema::hasTable('area_of_work')) {
            return;
        }

        Schema::create('area_of_work', function ($table): void {
            $table->id();
            $table->string('category')->default('');
            $table->string('name');
            $table->timestamps();
        });
    }

    private function ensureUserPositionsTable(): void
    {
        if (Schema::hasTable('user_positions')) {
            return;
        }

        Schema::create('user_positions', function ($table): void {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });
    }

    private function ensureUserStatusesTable(): void
    {
        if (Schema::hasTable('users_status')) {
            return;
        }

        Schema::create('users_status', function ($table): void {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });
    }

    private function ensureUserFiltersTable(): void
    {
        if (Schema::hasTable('user_filters')) {
            return;
        }

        Schema::create('user_filters', function ($table): void {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });
    }

    private function ensureEarningBandsTable(): void
    {
        if (!Schema::hasTable('earning_bands')) {
            $migration = require database_path('migrations/2026_03_12_000019_create_earning_bands_table.php');
            $migration->up();
        }

        if (DB::table('earning_bands')->count() > 0) {
            return;
        }

        $timestamp = now();
        $bands = [
            'A1',
            'A2',
            'A3',
            'B5',
            'B4',
            'B3',
            'B2',
            'B1',
            'B5/3',
            'C4',
            'C3',
            'C2',
            'C1',
            'C4/3',
            'D4',
            'D3',
            'D2',
            'D1',
            'E2',
            'E1',
        ];

        DB::table('earning_bands')->insert(
            array_map(
                static fn (string $band, int $index): array => [
                    'name' => $band,
                    'sort_order' => $index + 1,
                    'created_at' => $timestamp,
                    'updated_at' => $timestamp,
                ],
                $bands,
                array_keys($bands),
            ),
        );
    }

    private function ensureRolesTables(): void
    {
        if (!Schema::hasTable('roles')) {
            Schema::create('roles', function ($table): void {
                $table->id();
                $table->string('name');
                $table->string('description')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('role_users')) {
            Schema::create('role_users', function ($table): void {
                $table->id();
                $table->unsignedBigInteger('user_id');
                $table->unsignedBigInteger('role_id');
                $table->timestamps();
            });
        }
    }

    private function ensureLoggingsTable(): void
    {
        if (Schema::hasTable('loggings')) {
            return;
        }

        Schema::create('loggings', function ($table): void {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('location')->nullable();
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->text('url')->nullable();
            $table->string('method')->nullable();
            $table->longText('input')->nullable();
            $table->text('changes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    private function ensureQualificationsTables(): void
    {
        if (!Schema::hasTable('qualifications')) {
            Schema::create('qualifications', function ($table): void {
                $table->id();
                $table->string('qualification');
                $table->string('qualification_code')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (!Schema::hasTable('qualification_user')) {
            Schema::create('qualification_user', function ($table): void {
                $table->id();
                $table->unsignedBigInteger('user_id');
                $table->unsignedBigInteger('qualification_id');
                $table->string('level')->nullable();
                $table->string('college')->nullable();
                $table->date('start_date')->nullable();
                $table->date('completion_date')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }
    }

    private function ensureMessagesTable(): void
    {
        if (Schema::hasTable('messages')) {
            return;
        }

        Schema::create('messages', function ($table): void {
            $table->id();
            $table->unsignedBigInteger('term_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('author')->nullable();
            $table->unsignedBigInteger('sponsor_id')->nullable();
            $table->text('body')->nullable();
            $table->integer('sms_count')->nullable();
            $table->string('type')->nullable();
            $table->integer('num_recipients')->nullable();
            $table->string('status')->nullable();
            $table->string('external_message_id')->nullable();
            $table->string('delivery_status')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->decimal('price', 10, 2)->nullable();
            $table->string('price_unit')->nullable();
            $table->timestamps();
        });
    }

    private function ensureEmailsTable(): void
    {
        if (Schema::hasTable('emails')) {
            return;
        }

        Schema::create('emails', function ($table): void {
            $table->id();
            $table->unsignedBigInteger('term_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('sponsor_id')->nullable();
            $table->unsignedBigInteger('sender_id')->nullable();
            $table->unsignedBigInteger('receiver_id')->nullable();
            $table->string('receiver_type')->nullable();
            $table->string('subject')->nullable();
            $table->text('body')->nullable();
            $table->string('attachment_path')->nullable();
            $table->integer('num_of_recipients')->nullable();
            $table->string('status')->nullable();
            $table->string('type')->nullable();
            $table->text('error_message')->nullable();
            $table->longText('filters')->nullable();
            $table->timestamps();
        });
    }

    private function ensureWorkHistoriesTable(): void
    {
        if (Schema::hasTable('work_histories')) {
            return;
        }

        Schema::create('work_histories', function ($table): void {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('workplace')->nullable();
            $table->string('type_of_work')->nullable();
            $table->string('role')->nullable();
            $table->date('start')->nullable();
            $table->date('end')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }
}
