Login
=====

The following options can be configured directly in the flexform of the plugin when choosing the login action.

**Welcome Header** and **Welcome Message**

Shown when logged in with last request (user clicked the login button and no redirect is configured).

You can either set it in the flexform, directly through TypoScript or with the locallang file.

alternative TypoScript path: *plugin.tx\_t3users\_main.loginbox.header\_welcome* and *plugin.tx\_t3users\_main.loginbox.message\_welcome*

alternative locallang path: *loginbox\_header\_welcome* or *loginbox\_message\_welcome*

**Goodbye Header** and **Goodbye Message**

Shown when logged out with last request (user clicked the logout button and no redirect is configured).

You can either set it in the flexform, directly through TypoScript or with the locallang file.

alternative TypoScript path: *plugin.tx\_t3users\_main.loginbox.header\_goodbye* and *plugin.tx\_t3users\_main.loginbox.message\_goodbye*

alternative locallang path: *loginbox\_header\_goodbye* or *loginbox\_message\_goodbye*

**Logged in Header** and **Logged in Message**

Shown when logged in. Mostly show the logout form.

You can either set it in the flexform, directly through TypoScript or with the locallang file.

alternative TypoScript path: *plugin.tx\_t3users\_main.loginbox.header\_login* and *plugin.tx\_t3users\_main.loginbox.message\_login*

alternative locallang path: *loginbox\_header\_login* or *loginbox\_message\_login*

**Logged out Header** and **Logged out Message**

Shown when logged out. Mostly show the login form.

You can either set it in the flexform, directly through TypoScript or with the locallang file.

alternative TypoScript path: *plugin.tx\_t3users\_main.loginbox.header\_logout* and *plugin.tx\_t3users\_main.loginbox.message\_logout*

alternative locallang path: *loginbox\_header\_logout* or *loginbox\_message\_logout*

**Login error Header** and **Login error Message**

Shown when an error occured upon login.

You can either set it in the flexform, directly through TypoScript or with the locallang file.

alternative TypoScript path: *plugin.tx\_t3users\_main.loginbox.header\_login\_error* and *plugin.tx\_t3users\_main.loginbox.message\_login\_error*

alternative locallang path: *loginbox\_header\_login\_error* or *loginbox\_message\_login\_error*

**Passwortmail From Adresser**, **Passwortmail From Name** and **Passwortmail ReplyTo Adresse**

Used when a mail for password forgotten is triggered.

alternative TypoScript path: *plugin.tx\_t3users\_main.loginbox.emailFrom*, *plugin.tx\_t3users\_main.loginbox.emailFromName* and *plugin.tx\_t3users\_main.loginbox.emailReply*

**redirect after login**

The page where to redirect after a successful login.

alternative TypoScript path: *plugin.tx\_t3users\_main.loginbox.links.loginRedirect.pid*

**redirect after logout**

The page where to redirect after a successful logout.

alternative TypoScript path: *plugin.tx\_t3users\_main.loginbox.logoutRedirectPage*

**HTML template**

File for the HTML template. Default is EXT:t3users/templates/loginbox.html

Links
-----

There some more links which can be used in the template below that are configured through TypoScript. These are links created with rn\_base so you have the full configuration options of rn\_base links.

*reset password*

Where the reset password action is inserted.

TypoScript path: *plugin.tx\_t3users\_main.loginbox.links.resetPassword.pid*

*forgot password*

Usually the page with the login plugin. Will show instead of the normal form a form where a user can enter his email to request a new password.

TypoScript path: *plugin.tx\_t3users\_main.loginbox.links.forgotpass.pid*

or constants: *plugin.tx\_t3users.loginboxPage*

*register*

Where the registration action is inserted.

TypoScript path: *plugin.tx\_t3users\_main.loginbox.links.confirm.pid*

or constants: *plugin.tx\_t3users.registerPage*

HTML Template
-------------

The default is EXT:t3users/templates/loginbox.html. It can look like this:

~~~~ {.sourceCode .html}
<h1>Start with template for loginbox</h1>
###TEMPLATE_LOGIN###
   <!--###HEADER_VALID###-->
      <h3>###STATUS_HEADER###</h3>
   <!--###HEADER_VALID###-->

   <!--###MESSAGE_VALID###-->
      <p>###STATUS_MESSAGE###</p>
   <!--###MESSAGE_VALID###-->
   Users online: ###USER_ONLINE###

   <form name="logform" id="logform" action="###ACTION_URI###" target="_top" method="post">
     <input type="hidden" id="user" name="user" value="" />
     <input type="hidden" id="pass" name="pass" value="" /><br />
      <input type="hidden" name="logintype" value="login" />
      <input type="hidden" name="pid" value="###STORAGE_PID###" />
      <input type="hidden" name="redirect_url" value="###REDIRECT_URL###" />
      ###EXTRA_HIDDEN###
   </form>

   <form action="###ACTION_URI###" target="_top" method="post" onSubmit="return ###ON_SUBMIT###">
      <table>
         <tbody>
            <tr>
               <th><label for="user1">###LABEL_USERNAME###</label></th>
               <td><input type="text" id="user1" name="user1" value="" /></td>
            </tr>
            <tr>
               <th><label for="pass1">###LABEL_PASSWORD###</label></th>
               <td><input type="password" id="pass1" name="pass1" value="" /></td>
            </tr>
            <!--###PERMALOGIN_VALID###-->
            <tr>
               <th><label for="permalogin">###LABEL_PERMALOGIN###</label></th>
               <td>
                  <input name="permalogin" value="0" type="hidden" ###PERMALOGIN_HIDDENFIELD_ATTRIBUTES### id="permaloginHiddenField">
                  <input name="permalogin" value="1" type="checkbox" ###PERMALOGIN_CHECKBOX_ATTRIBUTES### id="permalogin"  onclick="document.getElementById('permaloginHiddenField').disabled = this.checked;" />
               </td>
            </tr>
            <!--###PERMALOGIN_VALID###-->
         </tbody>
         <tfoot>
            <tr>
               <td></td>
               <td>
                  <input type="submit" name="submit" value="###LABEL_LOGIN###" />
               </td>
            </tr>
         </tfoot>
      </table>
      <input type="hidden" name="logintype" value="login" />
      <input type="hidden" name="pid" value="###STORAGE_PID###" />
      <input type="hidden" name="redirect_url" value="###REDIRECT_URL###" />
      ###EXTRA_HIDDEN###
   </form>

   <p><!--###LOGINBOX_FORGOTPASSLINK###-->###LABEL_FORGOT_PASSWORD###<!--###LOGINBOX_FORGOTPASSLINK###--></p>
   <p><!--###LOGINBOX_REGISTERLINK###-->###LABEL_REGISTER###<!--###LOGINBOX_REGISTERLINK###--></p>

###TEMPLATE_LOGIN###

<!-- ------------------------------------------------------
------------------------------------------------------- -->

###TEMPLATE_WELCOME###
   <h3>###STATUS_HEADER###</h3>

   <p>###STATUS_MESSAGE###</p>
   ###FEUSER_FEGROUPS###
   <h4>Sie (###FEUSER_USERNAME###) sind Mitglied in diesen Gruppen:</h4>
   <ul>
   ###FEUSER_FEGROUP###
   <li>###FEUSER_FEGROUP_TITLE###
   ###FEUSER_FEGROUP###
   </ul>
   ###FEUSER_FEGROUPS###

###TEMPLATE_WELCOME###


<!-- ------------------------------------------------------
------------------------------------------------------- -->

###TEMPLATE_STATUS###
   <h3>###STATUS_HEADER###</h3>
   <p>###STATUS_MESSAGE###</p>

   <form action="###ACTION_URI###" target="_top" method="post">
      <table>
         <thead>
            <tr>
               <th>###LABEL_USERNAME###</th>
               <td>###FEUSER_USERNAME###</td>
            </tr>
         </thead>
         <tbody>
            <tr>
               <td></td>
               <td>
                  <input type="submit" name="submit" value="###LABEL_LOGOUT###" />
               </td>
            </tr>
         </tbody>
      </table>
      <input type="hidden" name="###PREFIXID###[NK_logintype]" value="logout" />
      <input type="hidden" name="pid" value="###STORAGE_PID###" />
   </form>
###TEMPLATE_STATUS###

<!-- ------------------------------------------------------
------------------------------------------------------- -->

###TEMPLATE_FORGOT###

   <h3>###STATUS_HEADER###</h3>
   <p>###STATUS_MESSAGE###</p>
   <form action="###ACTION_URI###" method="post">
      <table>
         <tfoot>
            <tr>
               <td></td>
               <td><input type="submit" name="submit" value="###LABEL_SENDPASS###" /></td>
            </tr>
         </tfoot>
         <tbody>
            <tr>
               <th><label for="###PREFIXID###[NK_forgot_email]">###LABEL_EMAIL###</label></th>
               <td><input type="text" name="###PREFIXID###[NK_forgot_email]" /></td>
            </tr>
         </tbody>
      </table>
   </form>
###TEMPLATE_FORGOT###

<!-- ------------------------------------------------------
------------------------------------------------------- -->

###TEMPLATE_FORGOT_SENT###

   <h3>###STATUS_HEADER###</h3>
   <p>###STATUS_MESSAGE###</p>

###TEMPLATE_FORGOT_SENT###

<!-- ------------------------------------------------------
------------------------------------------------------- -->

###TEMPLATE_REQUESTCONFIRMATION###

   <h3>###STATUS_HEADER###</h3>
   <p>###STATUS_MESSAGE###</p>
   <form action="###ACTION_URI###" method="post">
      <table>
         <tfoot>
            <tr>
               <td></td>
               <td><input type="submit" name="submit" value="###LABEL_SENDCONFIRMLINK###" /></td>
            </tr>
         </tfoot>
         <tbody>
            <tr>
               <th><label for="###PREFIXID###[NK_requestconfirmation_email]">###LABEL_EMAIL###</label></th>
               <td><input type="text" name="###PREFIXID###[NK_requestconfirmation_email]" /></td>
            </tr>
         </tbody>
      </table>
   </form>

###TEMPLATE_REQUESTCONFIRMATION###

<!-- ------------------------------------------------------
------------------------------------------------------- -->

###TEMPLATE_REQUESTCONFIRMATION_SENT###

   <h3>###STATUS_HEADER###</h3>
   <p>###STATUS_MESSAGE###</p>

###TEMPLATE_REQUESTCONFIRMATION_SENT###
~~~~

Crypt send password
-------------------

You can choose how the password should be crypted when sending the login. Default is auto which should be fine for most users, but you can specify it. Possible values are these:

-   none
-   auto
-   nomd5
-   md5
-   rsa
-   rsa62

Password forgotten
------------------

It's still possible to send a new password directly through mail but this absolutely not recommended. So please set *plugin.tx\_t3users\_main.loginbox.resetPasswordMode* empty. Unfortunately we have keep the default value of *sendpassword* to be backwards compatible.

If it's empty you just have to configure *plugin.tx\_t3users\_main.loginbox.links.resetPassword.pid* and let the reset password action do the rest. In the login form there will be a field to enter a email. A mail is than send to the user with a confirmation link pointing to the page configured in *plugin.tx\_t3users\_main.loginbox.links.resetPassword.pid*.

For the mail you can use either [mkmailer](http://typo3.org/extensions/repository/view/mkmailer) or the simple method.

For the simple method you find the mail template in the locallang file in the paths *loginbox\_reset\_infomail* or *loginbox\_reset\_infomail\_html* which can look like this (the first line is the subject). You can overwrite this by adding an additional locallang file by configuring *plugin.tx\_t3users\_main.locallangFilename.0 = path-to-file*

~~~~ {.sourceCode .html}
Your password
Hi ###FEUSER_NAME###

Your username is &quot;###FEUSER_USERNAME###&quot;
Please open this URL to reset your password:

###RESETLINKURL###
or click ###RESETLINK###here###RESETLINK###

This Link will work the next 48 hours.
~~~~

If you want to use mkmailer you just have to install and configure the extension and set *plugin.tx\_t3users\_main.loginbox.email.useMkmailer = 1*. Than you have to create the mail template with the key *t3users\_resetPassword*. The content of this template can be the same as above.

The link contains a confirmstring for which you can configure an additional secret which strengthens the encryption in *plugin.tx\_t3users\_main.loginbox.passwordsecret*

Bruteforce Protection
---------------------

If a login fails the HTTP header "Login: -1" is set. This can be used e.g. when using mod\_security of the Apache web server to block bruteforce attacks against the FE login and block them.

An example configuration for mod\_security, which has to be put into the httpd.conf, could look like this: (the only thing neccessary is to provide the .html files in case of a block.)

~~~~ {.sourceCode .apacheconf}
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
~~~~

If a attacker uses parallel requests with several IPs this mechanism might not be efficient enough. So there is a feature which at least slows down bruteforce attacks. If a login fails you can configure a delay in seconds through TypoScript. This way e.g. after every failed login the site loads with a delay of 3 seconds.

The default value is 0 seconds. This is because the core has this delay now implemented since 6.2.14. But we keep it in the extension as it could be useful for some situations where the postLoginFailureProcessing hook is used but has no sleep.

Configure through the TypoScript constants editor (FE User Management (Misc)) in the path plugin.tx\_t3users.delayInSecondsAfterFailedLogin or directly in the TypoScript setup in the path plugin.tx\_t3users\_main.loginbox.delayInSecondsAfterFailedLogin.

~~~~ {.sourceCode .ts}
### constants
plugin.tx_t3users.delayInSecondsAfterFailedLogin = 5

### setup
plugin.tx_t3users_main.loginbox.delayInSecondsAfterFailedLogin = 5
~~~~

Those are just some mechanisms to protect your login. There are plenty more things you can do. Search the internet and a you will find a lot more.
