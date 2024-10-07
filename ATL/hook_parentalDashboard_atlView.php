<?php
/*
Gibbon, Flexible & Open School System
Copyright (C) 2010, Ross Parker

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

use Gibbon\Services\Format;

global $page, $container;

$returnInt = null;

require_once __DIR__ . '/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/ATL/atl_view.php') == false) {
    //Acess denied
    $returnInt .= Format::alert(__('You do not have access to this action.'));
} else {
    // Register scripts available to the core, but not included by default
    $page->scripts->add('chart');
    //TODO: This is a hack to make sure that the ATLEntryGateway is loaded and should be fixed properly somehow.
    $container->get('autoloader')->addPsr4('Gibbon\\Module\\ATL\\', $session->get('absolutePath') . '/modules/ATL/src'); 

    $returnInt .= visualiseATL($container, $gibbonPersonID);
    
    $returnInt .= getATLRecord($gibbonPersonID);
}

return $returnInt;
