aCurl
=====

aCurl makes PHP's cURL functionality Object-Oriented, and less dependent on the file system. 

While many similar projects already exist to make cURL Object-oriented, this takes this a step further by also seeking 
to mitigate cURL's dependency on the file system.  Most importantly, this library allows cookies to be stored in an 
variable (an aCurlCookieJar object), which makes them more easily manipulated than the standard Cookie Jar file,
and also makes them serializable for storage in a database. 

Requires only PHP 5.5+ compiled with cURL.

## License

To license this software you *must* email me and let me know how you plan to use this software.  Upon sending of that 
email, you may use this software generally in the way you described, with modifications.  

This software is provided "as is" and any expressed or implied warranties, including but not limited to implied 
warranties of merchantability and fitness for any particular purpose. 
