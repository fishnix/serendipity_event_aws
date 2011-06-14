<?php 

/**
 *  @version $Revision: 1381 $
 *  @author E Camden Fisher <fishnix@gmail.com>
 *  EN-Revision: Revision of lang_en.inc.php
 */

@define('PLUGIN_EVENT_AWS_NAME',													'AWS Plugin');
@define('PLUGIN_EVENT_AWS_DESC',													'Plugin to support Amazon Web Services');
@define('PLUGIN_EVENT_AWS_PROP_AWS_S3_ON',								'Enable AWS');
@define('PLUGIN_EVENT_AWS_PROP_AWS_S3_ON_DESC',						'Enables the use of AWS features');
@define('PLUGIN_EVENT_AWS_PROP_AWS_KEY',     							'Amazon Web Services Key.');
@define('PLUGIN_EVENT_AWS_PROP_AWS_KEY_DESC',    					'Found in the AWS Security Credentials.');
@define('PLUGIN_EVENT_AWS_PROP_AWS_SECRET_KEY',     			'Amazon Web Services Secret Key.');
@define('PLUGIN_EVENT_AWS_PROP_AWS_SECRET_KEY_DESC',    	'Found in the AWS Security Credentials.');
@define('PLUGIN_EVENT_AWS_PROP_AWS_ACCOUNT_ID',     			'Amazon Account ID without dashes.');
@define('PLUGIN_EVENT_AWS_PROP_AWS_ACCOUNT_ID_DESC',    	'Used for identification with Amazon EC2. Found in the AWS Security Credentials (Currently Unused).');
@define('PLUGIN_EVENT_AWS_PROP_AWS_CANONICAL_ID',     		'Your CanonicalUser ID.');
@define('PLUGIN_EVENT_AWS_PROP_AWS_CANONICAL_ID_DESC',    'Used for setting access control settings in AmazonS3. Found in the AWS Security Credentials.');
@define('PLUGIN_EVENT_AWS_PROP_AWS_CANONICAL_NAME',    		'Your CanonicalUser DisplayName.');
@define('PLUGIN_EVENT_AWS_PROP_AWS_CANONICAL_NAME_DESC',  'Used for setting access control settings in AmazonS3. Found in the AWS Security Credentials.');
@define('PLUGIN_EVENT_AWS_PROP_AWS_S3_BUCKET_NAME',				'AWS Bucket Name.');
@define('PLUGIN_EVENT_AWS_PROP_AWS_S3_BUCKET_NAME_DESC',	'Name of the bucket to put your bits in.');
@define('PLUGIN_EVENT_AWS_INSTALL', 											'<P><strong>This is an experimental plugin.  I can\'t promise you won\'t lose all of your data.</strong>');
@define('PLUGIN_EVENT_AWS_UPLOAD_FILES',									'Upload files to Amazon S3');
@define('PLUGIN_EVENT_AWS_UPLOAD_FILES_DESC',							'Should we upload image to S3?');
@define('PLUGIN_EVENT_AWS_UPLOAD_SUCCESS',								'Upload to AWS S3 succeeded.');
@define('PLUGIN_EVENT_AWS_UPLOAD_FAILED',									'Upload to AWS S3 failed.');
@define('PLUGIN_EVENT_AWS_PROP_AWS_CACHE_ONLY',						'Enable Cache Only Mode');
@define('PLUGIN_EVENT_AWS_PROP_AWS_CACHE_ONLY_DESC',			'In Cache Only mode, we will keep a local list of what\'s in s3 and vend only those items.  Avoids need to sync.');
@define('PLUGIN_EVENT_AWS_BAD_BUCKET_OR_CREDS',						'Bucket doesnt exist or bad creds');
@define('PLUGIN_EVENT_AWS_MISSING_LIBS',									'Problem loading AmazonS3 library!');
@define('PLUGIN_EVENT_AWS_DISABLED',											'AWS Disabled!');
@define('PLUGIN_EVENT_AWS_VERIFIED',											'Verified bucket (Name/Items)');
@define('PLUGIN_EVENT_AWS_PROP_AWS_S3_OBJLIST_MECH',			'Object List Storage Mechanism');
@define('PLUGIN_EVENT_AWS_PROP_AWS_S3_OBJLIST_MECH_DESC', 'Mechanis to use for storing the object list.  None is SLOW and fetches list everytime it builds an entry.');
@define('PLUGIN_EVENT_AWS_WROTE_CACHE_FILE',							'Wrote object list cache file.');
@define('PLUGIN_EVENT_AWS_FAILED_CACHE_FILE',							'Failed to write object list cache file.');
@define('PLUGIN_EVENT_AWS_WROTE_CACHE_DB', 								'Wrote object list cache to database.');
@define('PLUGIN_EVENT_AWS_FAILED_CACHE_DB', 							'Failed to write object list cache to database.');
?>