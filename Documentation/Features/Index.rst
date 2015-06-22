.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../Includes.txt


.. _features:

Features
========


.. _login:

Login
-----

If a login fails the HTTP header "Login: -1" is set. This can be used e.g. when using mod_security
of the Apache web server to block bruteforce attacks against the FE login and block them.

An example configuration for mod_security, which has to be put into the httpd.conf, could look like this:
(the only thing neccessary is to provide the .html files in case of a block.)

.. code-block:: apacheconf

   # when a login fails 15 times from a IP and/or 5 times with a username/password
   # the login is blocked
   <LocationMatch '.*login\.html'>
      # Make sure the secrule engine is enabled (http://typo3.org/waf.txt 
      # will disable modsecurity for the entire back-end)
      SecRuleEngine On

      # Enforce an existing IP block
      SecRule IP:bf_block "@eq 1" \
         "phase:2,deny,redirect:/ip-locked.html,id:5000103"
    
      # Retrieve the per-username record
      SecAction phase:2,nolog,pass,initcol:USER=%{ARGS.user},id:5000105

      # Enforce an existing username block
      SecRule USER:bf_block "@eq 1" \
         "phase:2,deny,redirect:/user-locked.html,id:5000104"

      # Retrieve the password parameter
           SecAction phase:2,nolog,pass,initcol:RESOURCE=%{ARGS.pass},id:5000107

      # Enforce an existing password block
      SecRule RESOURCE:bf_block "@eq 1" \
         "phase:2,deny,redirect:/password-locked.html,id:5000108"
    
      # Check for authentication failure and increment counters
      SecRule RESPONSE_HEADERS:Login "@streq -1" \
         "phase:5,t:none,pass, \
       another  setvar:IP.bf_counter=+1, \
         setvar:USER.bf_counter=+1, \
         setvar:RESOURCE.bf_counter=+1, \
         expirevar:IP.bf_counter=3600, \
         expirevar:RESOURCE.bf_counter=3600, \
         expirevar:USER.bf_counter=3600,id:5000100"

      # Check for too many failures from a single IP address
      SecRule IP:bf_counter "@gt 15" \
         "phase:5,pass,t:none, \
         setvar:IP.bf_block, \
         setvar:!IP.bf_counter, \
         expirevar:IP.bf_block=600,id:5000102"

      # Check for too many failures for a single username
      SecRule USER:bf_counter "@gt 5" \
         "phase:5,t:none,pass, \
         setvar:USER.bf_block, \
         setvar:!USER.bf_counter, \
         expirevar:USER.bf_block=600,id:5000101"

      # Check for too many failures for a single password
      SecRule RESOURCE:bf_counter "@gt 5" \
         "phase:5,t:none,pass, \
         setvar:RESOURCE.bf_block, \
         setvar:!RESOURCE.bf_counter, \
         expirevar:RESOURCE.bf_block=600,id:5000106"
   </LocationMatch>

If a attacker uses parallel requests with several IPs this mechanism might not be efficient enough.
So there is a feature which at least slows down bruteforce attacks. If a login fails you
can configure a delay in seconds through TypoScript. This way e.g. after every failed login the site loads with a
delay of 3 seconds.

Configure through the TypoScript constants editor (FE User Management (Misc)) in the path
plugin.tx_t3users.delayInSecondsAfterFailedLogin or
directly in the TypoScript setup in the path plugin.tx_t3users_main.loginbox.delayInSecondsAfterFailedLogin.

.. code-block:: ts

   ### constants
   plugin.tx_t3users.delayInSecondsAfterFailedLogin = 5
   
   ### setup
   plugin.tx_t3users_main.loginbox.delayInSecondsAfterFailedLogin = 5
   
Those are just some mechanisms to protect your login. There are plenty more things you can do. Search
the internet and a you will find a lot more.