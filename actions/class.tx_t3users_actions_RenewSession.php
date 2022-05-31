<?php
/**
 * @author Hannes Bochmann <dev@dmk-ebusiness.de>
 *
 *  Copyright notice
 *
 *  (c) 2012 Hannes Bochmann <dev@dmk-ebusiness.de>
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 */

/*
 * benötigte Klassen einbinden
 */

/**
 * per Ajax aktuelle Seite in einem bestimmten Intervall aufrufen
 * um ein automatisches Logout von TYPO3 zu verhindern.
 *
 * @author Hannes Bochmann <dev@dmk-ebusiness.de>
 */
class tx_t3users_actions_RenewSession extends \Sys25\RnBase\Frontend\Controller\AbstractAction
{
    /**
     * per Ajax aktuelle Seite in einem bestimmten Intervall aufrufen
     * um ein automatisches Logout von TYPO3 zu verhindern.
     *
     * @return string
     */
    public function handleRequest(\Sys25\RnBase\Frontend\Request\RequestInterface $request)
    {
        $configurations = $request->getConfigurations();

        $intervallInSeconds =
            $configurations->get($this->getConfId().'intervallInSeconds');

        $intervallInSeconds = $intervallInSeconds ? $intervallInSeconds : 300;

        $intervallInMilliSeconds = $intervallInSeconds * 1000;

        $GLOBALS['TSFE']->additionalHeaderData['tx_t3users_actions_RenewSession'] =
        "<script type='text/javascript'>
	RenewSession = {
		loadCurrentPage: function(){
			var xmlhttp;
			
			// code for IE7+, Firefox, Chrome, Opera, Safari
			if (window.XMLHttpRequest) {
			  	xmlhttp=new XMLHttpRequest();
			// code for IE6, IE5
			} else {
			  	xmlhttp=new ActiveXObject('Microsoft.XMLHTTP');
			}
			xmlhttp.open('GET',window.location,true);
			xmlhttp.send();
		},
		
		loadCurrentPageInIntervall: function(intervall) {
			window.setInterval('RenewSession.loadCurrentPage()', intervall);	
		}
	};

	RenewSession.loadCurrentPageInIntervall($intervallInMilliSeconds);
</script>";

        // wir brauchen kein template parsing
        return '&nbsp;';
    }

    /**
     * @return string
     */
    public function getTemplateName()
    {
        return 'renewSession';
    }

    /**
     * @return string
     */
    public function getViewClassName()
    {
        return \Sys25\RnBase\Frontend\View\Marker\BaseView::class;
    }
}
