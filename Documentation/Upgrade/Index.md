TYPO3 7.6
=========

Since TYPO3 7.6 the login via RSA has changed. If you use your own templates for the login, those templates have to be modified. First add the attribute "data-rsa-encryption" to the input field with the ID "pass" inside the form with the ID "logform". Than you have to add the following submit button to the form with the ID "logform":

~~~~ {.sourceCode .HTML}
<input type="submit" name="submit" style="display:none" id=t3users_login_real_submit/>
~~~~

From now on logins should work again as long as the TypoScript configuration in plugin.tx\_t3users\_main.loginbox.extend.method is set to auto or rsa7.
