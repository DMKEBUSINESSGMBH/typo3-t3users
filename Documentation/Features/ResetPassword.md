Reset Password
==============

This action can be selected in the flexform of the plugin. It provides a form to set a new password. The link to the page with the plugin has to provide the correct confirm string for the user, so the form is displayed. The link can be send via mail from the login action (forgot password function).

The template for the action can look like this (default one)

~~~~ {.sourceCode .html}
###RESETPASSWORD_FORM###
       <h2>Please set a new password</h2>
       <form method="POST" action="###ACTION_URI###">
            <strong>###MESSAGE###</strong>
            <div>
                   <label for="pass1">Password:</label>
                   <input type="password" id="pass1" name="t3users[pass1]"><br />
                   <label for="pass2">Repeat:</label>
                   <input type="password" id="pass2" name="t3users[pass2]">
                   <br/>
                   <input type="submit" name="t3users[submit]" value="Send" />
            </div>
       </form>
###RESETPASSWORD_FORM###

###RESETPASSWORD_CONFIRMFAILED###
       <div>
            <h2>The request is no longer valid.</h2>
            <p>Please request a new password again.</p>
       </div>
###RESETPASSWORD_CONFIRMFAILED###

###RESETPASSWORD_FINISHED###
       <div>
            <h2>The new password was saved.</h2>
       </div>
###RESETPASSWORD_FINISHED###
~~~~

The template for the plugin can be configured through TypoScript as follows:

~~~~ {.sourceCode .ts}
plugin.tx_t3users_main.resetpasswordTemplate = ...
~~~~
