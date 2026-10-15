<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ReportsRoleAccessTest extends TestCase
{
    use DatabaseTransactions;

    private function reportsUser(): User
    {
        return User::factory()->create([
            'name' => 'EazywearReports',
            'email' => 'reports-role-test@example.com',
            'role' => 'reports',
        ]);
    }

    public function test_reports_role_can_access_all_sales_report_pages(): void
    {
        $this->actingAs($this->reportsUser());

        $this->get(route('admin.sales-reports'))->assertOk();
        $this->get(route('admin.sales-reports.print'))->assertOk();
        $this->get(route('admin.sales-reports.export'))->assertOk();
    }

    public function test_reports_role_can_access_all_production_report_pages(): void
    {
        $this->actingAs($this->reportsUser());

        $this->get(route('admin.production-reports'))->assertOk();
        $this->get(route('admin.production-reports.print'))->assertOk();
        $this->get(route('admin.production-reports.export'))->assertOk();
    }

    public function test_reports_role_cannot_access_transaction_or_production_management_pages(): void
    {
        $this->actingAs($this->reportsUser());

        $this->get(route('admin.transactions'))->assertForbidden();
        $this->get(route('admin.productions'))->assertForbidden();
    }

    public function test_reports_role_login_redirects_to_production_report(): void
    {
        $this->actingAs($this->reportsUser());

        $this->get(route('login'))
            ->assertRedirect(route('admin.production-reports'));
    }
}