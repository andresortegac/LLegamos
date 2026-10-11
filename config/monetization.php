<?php
return [
    'commission_rate' => (float) env('PLATFORM_COMMISSION_RATE', 12),
    'earning_hold_hours' => (int) env('EARNING_HOLD_HOURS', 0),
    'minimum_payout' => (float) env('MINIMUM_PAYOUT', 20000),
];