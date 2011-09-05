
   Batchelor - Notes for users of PHP4
   
** GENERAL:

   I don't recommend using Batchelor with PHP4, but sometimes you don't have
   the option to choose. This README outlines the step required to get it
   work as far as possible.
   
** THE WEB SERVICE TEST UTILITY (WS.PHP):

   This script can't be used out of the box, you have to generate an PHP4
   compatible version by running:
   
     bash$> cd utils
     bash$> make ws_php4
   
   This step is required because the ws.php uses try and catch blocks, a
   language construct unknown to the PHP interpreter prior to PHP5.
   
** THE SOAP SERVICE:

   The SOAP service can't be used:
   
     1. The script source/ws/soap/index.php uses the throw keywork (PHP5).
     2. The SOAP extension is incompatible with PHP4.

   It exist a PEAR SOAP package, but I don't have the time to integrate it
   in Batchelor. If you do, please send me an patch.

** THE XML-RPC SERVICE:

   The PHP4 interpreter chokes on:
   
     $value = $param->first_child()->get_content();
     
   I'm sure this could be fixed, but becasue XML-RPC is an more or less
   obsolete web service protocol I don't intend to spend any time fixing it.
   
** ZIP FILES:

   ZIP-files are always created using an external ZIP command (like 7zip or
   zip) when using PHP4. For PHP5 we use the builtin zip extension.
   
   The decision to use external commands entierly for PHP4 was that I got
   various support for statements like this and decided to make it entierly
   PHP5 in include/zip5.inc:
   
     $objzip->open($zipfile, ZipArchive::CREATE)
   
** RUNNING LINT:

   Using lint is a good start to find incompatible code. Scan the source
   like this to find problems:
   
     bash$> find -type f | egrep '\.(php|inc)$' | while read file; \
              do php -l $file; \
            done

   The above should be on a single command line.
   

// Anders Lövgren, 2009-05-26
