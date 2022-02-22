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
use Gibbon\Module\Rubrics\Visualise;
use Gibbon\Domain\Rubrics\RubricGateway;
use Gibbon\Module\ATL\Domain\ATLColumnGateway;

//Module includes
require_once './modules/ATL/moduleFunctions.php';

if (isActionAccessible($guid, $connection2, '/modules/ATL/atl_view.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo 'You do not have access to this action.';
    echo '</div>';
} else {
    // Register scripts available to the core, but not included by default
    $page->scripts->add('chart');

    echo visualiseATL($container, $gibbonPersonID);
    
    echo getATLRecord($guid, $connection2, $gibbonPersonID);
}
