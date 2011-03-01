#Serendipity AWS Plugin

This is a plugin to interface the [*serendipity blog platform*](http://www.s9y.org/) with Amazon Web Services.

This is nowhere near complete, so if you stumble upon it, don't use it.

##Initial goals are:

	* 	Make plugin easily configurable via standard configuration interface
	*	Upload files to S3 when uploading to media manager
	*	Sync all medea from local media manager to S3
	*	Utility to check for out of sync repository
	* 	Swap out links/src to local files with those at S3 on demand per site
	*	Swap out links/src to local files with those at S3 on demand per entry
	
##What you will need to use this plugin:
	* AWS Account

##Why?

I have a couple reasons...

	* Cloud is cool
	* S3 is scalable, redundant storage
	* S3 for media allows me to use the cloudfront cdn on bursty days
	* S3 for media removes one of two things that make a local instance of s9y "special".  (DB is the other)  Using cfg mgmt (chef), I can deploy a s9y server on demand, and with a few quick clicks (literally) have the site up and running
	
	
##Todo

	* Job to sync files from local repo to S3
	* Job to check sync of files
	* Job to sync files from S3 to local repo?
	* Harden code to swap out img src (it's really dumb right now)
	* Make sure uploads are confirmed, check return codes, etc
	* Add config option for enable/disable per post?
