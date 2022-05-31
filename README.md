t3users
=======


![TYPO3 compatibility](https://img.shields.io/badge/TYPO3-10.4%20%7C%2011.5-orange?maxAge=3600&style=flat-square&logo=typo3)
[![Latest Stable Version](https://img.shields.io/packagist/v/dmk/t3users.svg?maxAge=3600&style=flat-square&logo=composer)](https://packagist.org/packages/dmk/t3users)
[![Total Downloads](https://img.shields.io/packagist/dt/dmk/t3users.svg?maxAge=3600&style=flat-square)](https://packagist.org/packages/dmk/t3users)
[![Build Status](https://img.shields.io/github/workflow/status/DMKEBUSINESSGMBH/typo3-t3users/PHP-CI.svg?maxAge=3600&style=flat-square&logo=github-actions)](https://github.com/DMKEBUSINESSGMBH/typo3-t3users/actions?query=workflow%3APHP-CI)
[![License](https://img.shields.io/packagist/l/dmk/t3users.svg?maxAge=3600&style=flat-square&logo=gnu)](https://packagist.org/packages/dmk/t3users)

What does it do?
----------------

The target of this project is an out-of-the-box solution for TYPO3 frontend user management. I'm tired using newloginbox and sr\_fe\_userregister with all the bugs and problems.

This first release contains a replacement for newloginbox. These are the main features:

-   Login and Logout with predefined redirect pages
-   No more problems with IE password storage
-   Full featured HTML-Templates. You can output any feuser data you want.
-   Possible login with email address
-   Login from the backend module with any FE user for debugging purposes

The extensions contains also a set of useful classes for other extensions. So sometimes you need to install this extension without to use the plugin.


[Developer](Documentation/Developer/Index.md)

[Features](Documentation/Features/Index.md)

[Upgrade](Documentation/Upgrade/Index.md)

[Changelog](Documentation/Changelog.md)
