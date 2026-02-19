<?php
// config/razorpay.php
define('RAZORPAY_KEY_ID', 'rzp_test_SGS4ip2m52ZKGp');       // your test key
define('RAZORPAY_KEY_SECRET', 'GcFvbIZ8Y0wap3Wr8aAdpw2L'); // your test secret

require '../vendor/autoload.php'; // Razorpay SDK
use Razorpay\Api\Api;

$api = new Api(RAZORPAY_KEY_ID, RAZORPAY_KEY_SECRET);
