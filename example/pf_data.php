<?php

require __DIR__.'/../vendor/autoload.php';

$datas = \pf\arr\PFarr::pf_date_indexed("2014-01-01", "2014-01-20", "+1 day", "m/d/Y");

echo '<pre>';
print_r($datas);

$dates_a = \pf\arr\PFarr::pf_date_indexed("01:00:00", "23:00:00", "+1 hour", "H:i:s");

print_r($dates_a);

$dates_b = \pf\arr\PFarr::pf_date_assoc("2014-01-01", "2014-01-20", 0, "+1 day", "m/d/Y");

print_r($dates_b);