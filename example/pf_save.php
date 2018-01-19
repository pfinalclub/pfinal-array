<?php

use pf\arr\PFarr;

$data = [
    'k2' => 'string'
];

PFarr::pf_save('k0', $data, 'v0'); // returns: true, save as 'k0' => 'v0'
PFarr::pf_save('[k1][k1-1]', $data, 'v1-1'); // returns: true, save as 'k1' => ['k1-1' => 'v1-1']
PFarr::pf_save('[k2][2]', $data, 'p'); // returns: false, can't save value to string

// Broken key names
PFarr::pf_save('k3[', $data, 'v3'); // returns: false, can't save, bad syntax
PFarr::pf_save('["k4["]', $data, 'v4'); // returns: true, save as 'k4[' => 'v4'
PFarr::pf_save('"k4["', $data, 'v4'); // returns: false, can't save, bad syntax

// Append
PFarr::pf_save('k5', $data, []); // returns: true, create array 'k5' => []
PFarr::pf_save('k5[]', $data, 'v5-0'); // returns: true, append value to exists array 'k5' => [ 'v5-0' ]
PFarr::pf_save('k6[k6-1][]', $data, 'v6-1-0'); // returns: true, save as 'k6' => [ 'k6-1' => [ 'v6-1-0' ] ]

// Replace if not exists
PFarr::pf_save('k2', $data, 'something', false); // returns false, value not replaced because value is exists