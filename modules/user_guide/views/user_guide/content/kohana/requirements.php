Article status [Draft] requires [Editing] 
# Basic Requirements

## Mandatory requirements
1. An Operating system capable of supporting [Unicode](http://unicode.org/) character sets.
  1. Supported OS
      1. Most Unix or Unix clones (Linux, BSD)
	  2. Windows (XP, Vista, Windows 2000)
	  3. Mac OS X

2. An HTTP Server.
 1. Supported Servers (Any server capable of supporting CGI should work)
     1. Apache httpd, version 1.1.37 or higher. (With CGI, FastCGI or Mod_php)
2. PHP Hypertext Processor, version 5.2.0 or higher. (Earlier versions are *not* supported)


## Optional requirements 
1. A Relational Database.
 1. Any database server currently supported by the PHP PDO database layer.
     1. MySQL and MySQLi
     2. SQLite3
     3. Postgres

2. The [mbstring](http://php.net/mbstring) extension.
    * It will speed up the utf8 class
    * However it must *not* be overloading the original string functions


*[HTTP]: Hyper Text Transfer Protocol
*[PDO]: PHP Database Object
*[PHP]: PHP Hypertext Processor