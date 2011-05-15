#Serendipity AWS Plugin

This is a plugin to interface the [*serendipity blog platform*](http://www.s9y.org/) with Amazon Web Services.

This is not complete, so if you stumble upon it, use at your own risk.

##Initial goals are:

	* 	Make plugin easily configurable via standard configuration interface
	*	Upload files to S3 when uploading to media manager
	*	Sync all medea from local media manager to S3
	*	Utility to check for out of sync repository
	* 	Swap out links/src to local files with those at S3 on demand per site
	*	Swap out links/src to local files with those at S3 on demand per entry
	*	Cache only mode so sync doesn't have to happen (s9y "knows" what objects are in S3)
	
##What you will need to use this plugin:
	* AWS Account

##Why?

I have a couple reasons...

	* Cloud is cool
	* S3 is scalable, redundant storage
	* S3 for media allows me to use the cloudfront cdn on bursty days
	* S3 for media removes one of two things that make a local instance of s9y "special".  
	(DB is the other)  Using cfg mgmt (chef), I can deploy a s9y server on demand, and with a few 
	quick clicks (literally) have the site up and running
	
	
##Todo

	* Workout entry caching for entries munged with S3 links
	* Build S3 object list asychronously + store in DB with Memcache option
	* Job to sync files from local repo to S3
	* Job to check sync of files
	* Job to sync files from S3 to local repo? (need to go both ways if sites are portable)
	* Make sure uploads are confirmed, check return codes, etc
	* Add config option for enable/disable per post?
