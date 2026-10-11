<?php
return [
 'offer_seconds'=>(int)env('DISPATCH_OFFER_SECONDS',45),
 'max_radius_km'=>(float)env('DISPATCH_MAX_RADIUS_KM',25),
 'location_fresh_seconds'=>(int)env('DISPATCH_LOCATION_FRESH_SECONDS',120),
 'batch_size'=>(int)env('DISPATCH_BATCH_SIZE',50),
];
