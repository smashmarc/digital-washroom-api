<?php

namespace App\Providers;

use App\Models\Backup;
use App\Models\Evaluation;
use App\Models\EvaluationTemplate;
use App\Models\Log;
use App\Models\Question;
use App\Models\QuestionCategory;
use App\Models\Role;
use App\Models\Room;
use App\Models\User;
use App\Models\Location;
use App\Models\Permission;
use App\Models\Report;
use App\Policies\BackupPolicy;
use App\Policies\EvaluationPolicy;
use App\Policies\EvaluationTemplatePolicy;
use App\Policies\LogPolicy;
use App\Policies\QuestionCategoryPolicy;
use App\Policies\QuestionPolicy;
use App\Policies\RolePolicy;
use App\Policies\RoomPolicy;
use App\Policies\UserPolicy;
use App\Policies\LocationPolicy;
use App\Policies\PermissionPolicy;
use App\Policies\ReportPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
      

    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
         Gate::policy(User::class, UserPolicy::class);
         Gate::policy(Role::class, RolePolicy::class);
         Gate::policy(Permission::class, PermissionPolicy::class);
         Gate::policy(Location::class, LocationPolicy::class);
         Gate::policy(Room::class, RoomPolicy::class);
         Gate::policy(Log::class, LogPolicy::class);
         Gate::policy(Report::class, ReportPolicy::class);
         Gate::policy(Backup::class, BackupPolicy::class);
         Gate::policy(QuestionCategory::class, QuestionCategoryPolicy::class);
         Gate::policy(Question::class, QuestionPolicy::class);
         Gate::policy(EvaluationTemplate::class, EvaluationTemplatePolicy::class);
         Gate::policy(Evaluation::class, EvaluationPolicy::class);
    }
}
