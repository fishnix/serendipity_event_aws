#Serendipity AWS Plugin

This is a plugin to interface the [*serendipity blog platform*](http://www.s9y.org/) with Amazon Web Services.

This is not complete, so if you stumble upon it, use at your own risk.

*It is recommended that you use a tool like s3cmd to keep your media in sync.*

##Goals are:

	* 	Make plugin easily configurable via standard configuration interface (DONE)
	*	Upload files to S3 when uploading to media manager (DONE)
	*	Sync all medea from local media manager to S3
	*	Utility to check for out of sync repository
	* 	Swap out links/src to local files with those at S3 on demand per site (DONE)
	*	Swap out links/src to local files with those at S3 on demand per entry (Maybe WONTDO)
	*	Cache only mode so sync doesn't have to happen (s9y "knows" what objects are in S3) (DONE)
	*	Enable query of local object list via DB
	*	Enable query of local object list via Memcahe or faster means than DB (MAYBE - entryproperties caches entries already)
	*	Support Rackspace cloudfiles
	
##What you will need to use this plugin:
	* AWS Account
	* serendipity_event_entryproperties plugin

##Why?

I have a couple reasons...

	* Cloud is cool
	* S3 is scalable, redundant storage
	* S3 for media allows me to use the cloudfront cdn on bursty days
	* S3 for media removes one of two things that make a local instance of s9y "special".  
	(DB is the other)  Using cfg mgmt (chef), I can deploy a s9y server on demand, and with a few 
	quick clicks (literally) have the site up and running
	
	
##Todo

	* Build S3 object list asychronously + store in DB with Memcache option
	* Job to sync files from local repo to S3
	* Job to check sync of files
	* Job to sync files from S3 to local repo? (need to go both ways if sites are portable)
	* Make sure uploads are confirmed, check return codes, etc
	* Add config option for enable/disable per post?
