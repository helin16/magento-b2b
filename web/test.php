<?php

require_once 'bootstrap.php';
Core::setUser(UserAccount::get(UserAccount::ID_SYSTEM_ACCOUNT));
$pdf = EntityToPDF::getPDF(Order::get(4290));
