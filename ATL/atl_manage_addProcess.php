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
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program. If not, see <http://www.gnu.org/licenses/>.
*/

use Gibbon\Module\ATL\Domain\ATLColumnGateway;

include '../../gibbon.php';


$gibbonCourseClassID = $_GET['gibbonCourseClassID'];
$URL = $session->get('absoluteURL').'/index.php?q=/modules/ATL/'; 

if (isActionAccessible($guid, $connection2, '/modules/ATL/atl_manage_add.php') == false) {
    //Fail 0
    $URL .= 'atl_manage.php&return=error0';
    header("Location: {$URL}");
} else {
    //Proceed!
    $URL .= "atl_manage_add.php&gibbonCourseClassID=$gibbonCourseClassID";

    //Validate Inputs
    $gibbonCourseClassIDMulti = array_unique($_POST['gibbonCourseClassIDMulti']) ?? '';
    $name = $_POST['name'] ?? '';
    $description = $_POST['description'] ?? '';
    $gibbonRubricID = $_POST['gibbonRubricID'] ?? '';
    $completeDate = $_POST['completeDate'] ?? '';
    if ($completeDate == '') {
        $completeDate = null;
        $complete = 'N';
    } else {
        $completeDate = dateConvert($guid, $completeDate);
        $complete = 'Y';
    }
    $forStudents = $_POST['forStudents'] ?? '';
    $gibbonPersonIDCreator = $session->get('gibbonPersonID');
    $gibbonPersonIDLastEdit = $session->get('gibbonPersonID');

    //Lock markbook column table
    try {
        $sqlLock = 'LOCK TABLES atlColumn WRITE';
        $resultLock = $connection2->query($sqlLock);
    } catch (PDOException $e) {
        //Fail 2
        $URL .= '&return=error2';
        header("Location: {$URL}");
        exit();
    }

    if (!is_array($gibbonCourseClassIDMulti) || empty($name) || empty($description) || empty($forStudents)) {
        //Fail 3
        $URL .= '&return=error1';
        header("Location: {$URL}");
        exit();
    } else {
        $partialFail = false;


        $data = [
            'name' => $name,
            'description' => $description,
            'gibbonRubricID' => $gibbonRubricID,
            'completeDate' => $completeDate,
            'forStudents' => $forStudents,
            'complete' => $complete,
            'gibbonPersonIDCreator' => $gibbonPersonIDCreator,
            'gibbonPersonIDLastEdit' => $gibbonPersonIDLastEdit
        ];
        foreach ($gibbonCourseClassIDMulti as $gibbonCourseClassIDSingle) {
            //Write to database
            $data['gibbonCourseClassID'] = $gibbonCourseClassIDSingle;
            $partialFail |= empty($atlColumnGateway->insert($data));
        }

        //Unlock module table
        try {
            $sql = 'UNLOCK TABLES';
            $result = $connection2->query($sql);
        } catch (PDOException $e) {
        }

        if ($partialFail) {
            $URL .= '&return=warning1';
        } else {
            $URL .= '&return=success0';
        }
        header("Location: {$URL}");
        exit();
    }
}
