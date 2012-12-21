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
@define('PLUGIN_EVENT_AWS_PROP_AWS_ACCOUNT_ID',     			'Amazon Account ID without dashes. (Unused)');
@define('PLUGIN_EVENT_AWS_PROP_AWS_ACCOUNT_ID_DESC',    	'Used for identification with Amazon EC2. Found in the AWS Security Credentials.');
@define('PLUGIN_EVENT_AWS_PROP_AWS_CANONICAL_ID',     		'Your CanonicalUser ID (Unused).');
@define('PLUGIN_EVENT_AWS_PROP_AWS_CANONICAL_ID_DESC',    'Used for setting access control settings in AmazonS3. Found in the AWS Security Credentials.');
@define('PLUGIN_EVENT_AWS_PROP_AWS_CANONICAL_NAME',    		'Your CanonicalUser DisplayName. (Unused)');
@define('PLUGIN_EVENT_AWS_PROP_AWS_CANONICAL_NAME_DESC',  'Used for setting access control settings in AmazonS3. Found in the AWS Security Credentials.');
@define('PLUGIN_EVENT_AWS_PROP_AWS_S3_BUCKET_NAME',				'AWS S3 Bucket Name.');
@define('PLUGIN_EVENT_AWS_PROP_AWS_S3_BUCKET_NAME_DESC',	'Name of the Simple Storage Service bucket to put your bits in.');
@define('PLUGIN_EVENT_AWS_PROP_AWS_S3_STORAGE_TYPE',			'Type of Storage.');
@define('PLUGIN_EVENT_AWS_PROP_AWS_S3_STORAGE_TYPE_DESC',	'Storage Redundancy. Only effects newly added media. Standard is more costly than Reduced.');
@define('PLUGIN_EVENT_AWS_PROP_AWS_CACHEFILE_NAME',       'Cache file name for object list.');
@define('PLUGIN_EVENT_AWS_PROP_AWS_CACHEFILE_NAME_DESC',  'File saved in templates_c/ used to cache object list. Default should be fine.');
@define('PLUGIN_EVENT_AWS_INSTALL', 											'<P><strong>This is an experimental plugin.  I can\'t promise you won\'t lose all of your data.</strong>');
@define('PLUGIN_EVENT_AWS_UPLOAD_FILES',									'Upload files to Amazon S3');
@define('PLUGIN_EVENT_AWS_UPLOAD_FILES_DESC',							'Should we upload image to S3?');
@define('PLUGIN_EVENT_AWS_UPLOAD_SUCCESS',								'Upload to AWS S3 succeeded.');
@define('PLUGIN_EVENT_AWS_UPLOAD_FAILED',									'Upload to AWS S3 failed.');
@define('PLUGIN_EVENT_AWS_PROP_AWS_CACHE_ONLY',						'Enable Cache Only Mode');
@define('PLUGIN_EVENT_AWS_PROP_AWS_CACHE_ONLY_DESC',			'Keep a local list of what\'s in s3 and vend only those items.  Avoids need to sync, but slower and needs more memory. Won\'t work well for large repositories.');
@define('PLUGIN_EVENT_AWS_BAD_BUCKET_OR_CREDS',						'Bucket doesnt exist or bad creds');
@define('PLUGIN_EVENT_AWS_MISSING_LIBS',									'Problem loading AmazonS3 library!');
@define('PLUGIN_EVENT_AWS_DISABLED',											'AWS Disabled!');
@define('PLUGIN_EVENT_AWS_VERIFIED',											'Verified bucket (Name/Items)');
@define('PLUGIN_EVENT_AWS_PROP_AWS_S3_OBJLIST_MECH',			'Object List Storage Mechanism');
@define('PLUGIN_EVENT_AWS_PROP_AWS_S3_OBJLIST_MECH_DESC', 'Mechanism to use for storing the object list.  None is SLOW and fetches list everytime it builds an entry.');
@define('PLUGIN_EVENT_AWS_CACHE_UPDATE_SUCCESS',          'Successfully updated object cache.');
@define('PLUGIN_EVENT_AWS_CACHE_UPDATE_FAILURE',          'Failed to update object cache.');
?>