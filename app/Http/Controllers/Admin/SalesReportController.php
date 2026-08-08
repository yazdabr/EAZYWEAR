<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class SalesReportController extends Controller
{
    public function index()
    {
        return view('admin.sales-reports.index');
    }

    public function print()
    {
        $reportTransactions = [

            [
                'invoice' => 'INV-20260808-001',
                'date' => '08 Aug 2026',
                'customer' => 'John Doe',
                'payment' => 'QRIS',
                'status' => 'Paid',
                'total' => 'Rp517.000',
            ],

            [
                'invoice' => 'INV-20260808-002',
                'date' => '08 Aug 2026',
                'customer' => 'Sarah Wilson',
                'payment' => 'Cash',
                'status' => 'Completed',
                'total' => 'Rp298.000',
            ],

            [
                'invoice' => 'INV-20260807-003',
                'date' => '07 Aug 2026',
                'customer' => 'Michael Brown',
                'payment' => 'Transfer',
                'status' => 'Completed',
                'total' => 'Rp745.000',
            ],

            [
                'invoice' => 'INV-20260807-004',
                'date' => '07 Aug 2026',
                'customer' => 'Emily Davis',
                'payment' => 'EDC',
                'status' => 'Paid',
                'total' => 'Rp425.000',
            ],

            [
                'invoice' => 'INV-20260806-005',
                'date' => '06 Aug 2026',
                'customer' => 'James Anderson',
                'payment' => 'QRIS',
                'status' => 'Completed',
                'total' => 'Rp632.000',
            ],

        ];

        return view('admin.sales-reports.print', compact('reportTransactions'));
    }
}