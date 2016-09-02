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

include '../../functions.php';
include '../../config.php';

//New PDO DB connection
try {
    $connection2 = new PDO("mysql:host=$databaseServer;dbname=$databaseName;charset=utf8", $databaseUsername, $databasePassword);
    $connection2->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $connection2->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo $e->getMessage();
}

@session_start();

//Set timezone from session variable
date_default_timezone_set($_SESSION[$guid]['timezone']);

$gibbonCourseClassID = $_GET['gibbonCourseClassID'];
$atlColumnID = $_GET['atlColumnID'];
$URL = $_SESSION[$guid]['absoluteURL'].'/index.php?q=/modules/'.getModuleName($_GET['address'])."/atl_write_data.php&atlColumnID=$atlColumnID&gibbonCourseClassID=$gibbonCourseClassID";

if (isActionAccessible($guid, $connection2, '/modules/ATL/atl_write_data.php') == false) {
    //Fail 0
    $URL .= '&return=error0';
    header("Location: {$URL}");
} else {
    if (empty($_POST)) {
        $URL .= '&return=error5';
        header("Location: {$URL}");
    } else {
        //Proceed!
        //Check if school year specified
        if ($atlColumnID == '' or $gibbonCourseClassID == '') {
            //Fail1
            $URL .= '&return=error1';
            header("Location: {$URL}");
        } else {
            try {
                $data = array('atlColumnID' => $atlColumnID, 'gibbonCourseClassID' => $gibbonCourseClassID);
                $sql = 'SELECT * FROM atlColumn WHERE atlColumnID=:atlColumnID AND gibbonCourseClassID=:gibbonCourseClassID';
                $result = $connection2->prepare($sql);
                $result->execute($data);
            } catch (PDOException $e) {
                //Fail2
                $URL .= '&return=error2';
                header("Location: {$URL}");
                exit();
            }

            if ($result->rowCount() != 1) {
                //Fail 2
                $URL .= '&return=error2';
                header("Location: {$URL}");
            } else {
                $row = $result->fetch();
                $name = $row['name' ];
                $count = $_POST['count'];
                $partialFail = false;
                $comment = $row['comment'];

                for ($i = 1;$i <= $count;++$i) {
                    $gibbonPersonIDStudent = $_POST["$i-gibbonPersonID"];
                    //Comment
                    if ($comment != 'Y') {
                        $commentValue = null;
                    } else {
                        $commentValue = $_POST["comment$i"];
                    }
                    $gibbonPersonIDLastEdit = $_SESSION[$guid]['gibbonPersonID'];

                    $selectFail = false;
                    try {
                        $data = array('atlColumnID' => $atlColumnID, 'gibbonPersonIDStudent' => $gibbonPersonIDStudent);
                        $sql = 'SELECT * FROM atlEntry WHERE atlColumnID=:atlColumnID AND gibbonPersonIDStudent=:gibbonPersonIDStudent';
                        $result = $connection2->prepare($sql);
                        $result->execute($data);
                    } catch (PDOException $e) {
                        $partialFail = true;
                        $selectFail = true;
                    }
                    if (!($selectFail)) {
                        if ($result->rowCount() < 1) {
                            try {
                                $data = array('atlColumnID' => $atlColumnID, 'gibbonPersonIDStudent' => $gibbonPersonIDStudent, 'comment' => $commentValue, 'gibbonPersonIDLastEdit' => $gibbonPersonIDLastEdit);
                                $sql = 'INSERT INTO atlEntry SET atlColumnID=:atlColumnID, gibbonPersonIDStudent=:gibbonPersonIDStudent, comment=:comment, gibbonPersonIDLastEdit=:gibbonPersonIDLastEdit';
                                $result = $connection2->prepare($sql);
                                $result->execute($data);
                            } catch (PDOException $e) {
                                $partialFail = true;
                            }
                        } else {
                            $row = $result->fetch();
                            //Update
                            try {
                                $data = array('atlColumnID' => $atlColumnID, 'gibbonPersonIDStudent' => $gibbonPersonIDStudent, 'comment' => $commentValue, 'gibbonPersonIDLastEdit' => $gibbonPersonIDLastEdit, 'atlEntryID' => $row['atlEntryID']);
                                $sql = 'UPDATE atlEntry SET atlColumnID=:atlColumnID, gibbonPersonIDStudent=:gibbonPersonIDStudent, comment=:comment, gibbonPersonIDLastEdit=:gibbonPersonIDLastEdit WHERE atlEntryID=:atlEntryID';
                                $result = $connection2->prepare($sql);
                                $result->execute($data);
                            } catch (PDOException $e) {
                                $partialFail = true;
                            }
                        }
                    }
                }

                //Update column
                $completeDate = $_POST['completeDate'];
                if ($completeDate == '') {
                    $completeDate = null;
                    $complete = 'N';
                } else {
                    $completeDate = dateConvert($guid, $completeDate);
                    $complete = 'Y';
                }
                try {
                    $data = array('completeDate' => $completeDate, 'complete' => $complete, 'atlColumnID' => $atlColumnID);
                    $sql = 'UPDATE atlColumn SET completeDate=:completeDate, complete=:complete WHERE atlColumnID=:atlColumnID';
                    $result = $connection2->prepare($sql);
                    $result->execute($data);
                } catch (PDOException $e) {
                    $partialFail = true;
                }

                //Return!
                if ($partialFail == true) {
                    //Fail 3
                    $URL .= '&return=error3';
                    header("Location: {$URL}");
                } else {
                    //Success 0
                    $URL .= '&return=success0';
                    header("Location: {$URL}");
                }
            }
        }
    }
}
