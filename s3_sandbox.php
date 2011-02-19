<?php

// AWS Plugin for Serendipity
// 20110212 by E Camden Fisher <fish@fishnix.net>

// Temporary for testing
header("Content-type: text/plain; charset=utf-8");

if (IN_serendipity != true) {
		die ("Don't hack!");
}

$time_start = microtime(true);
require_once 'sdk-1.2.3/sdk.class.php';

// Probe for a language include with constants. Still include defines later on, if some constants were missing
$probelang = dirname(__FILE__) . '/' . $serendipity['charset'] . 'lang_' . $serendipity['lang'] . '.inc.php';
if (file_exists($probelang)) {
    include $probelang;
}


//$ec2 = new AmazonEC2();
//
//$response = $ec2->describe_availability_zones();
//
//print_r($response->body->zoneName());


include_once dirname(__FILE__) . '/lang_en.inc.php';

$s3 = new AmazonS3();

//$response = $s3->create_bucket('s9y-test', $s3::REGION_US_E1 );
$response = $s3->get_bucket_list();

print_r($response);

$bucket = $response[0];

echo "Bucket is $bucket";

$createresponse = $s3->create_object($bucket, 'plain.txt', array(
	'body' => 'This is a plain text file with different content',
	'contentType' => 'text/plain',
	'acl' => $s3::ACL_PUBLIC,
	'storage' => $s3::STORAGE_REDUCED
));

print_r($createresponse);


$uploadresponse = $s3->create_object($bucket, 'photo.jpg', array(
	'fileUpload' => 'photo.jpg',
	'acl' => $s3::ACL_PUBLIC,
	'storage' => $s3::STORAGE_REDUCED
));

print_r($uploadresponse);


$listresponse = $s3->get_object_list($bucket);

print_r($listresponse);

$urlresponse = $s3->get_object_url($bucket, 'photo.jpg');
print_r($urlresponse);

$delresponse = $s3->delete_all_objects($bucket);

print_r($delresponse);

$s3->delete_bucket($bucket);



// =====================================

$time = microtime(true) - $time_start;
echo PHP_EOL . PHP_EOL . $time . PHP_EOL;