<?php

namespace App\Console\Commands;

use App\Models\Transaction;
use App\Services\DokuService;
use App\Services\TransactionPaymentService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class ReconcileDokuPayments extends Command
{

