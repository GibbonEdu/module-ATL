<?php
/*
Gibbon: the flexible, open school platform
Founded by Ross Parker at ICHK Secondary. Built by Ross Parker, Sandra Kuipers and the Gibbon community (https://gibbonedu.org/about/)
Copyright © 2010, Gibbon Foundation
Gibbon™, Gibbon Education Ltd. (Hong Kong)

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

use Gibbon\Services\Format;
use Gibbon\Domain\System\SettingGateway;

//Module includes
include './modules/'.$session->get('module').'/moduleFunctions.php';

//Get gibbonHookID
$gibbonHookID = null;
try {
    $data = array();
    $sql = "SELECT gibbonHookID FROM gibbonHook WHERE type='Student Profile' AND name='ATL'";
    $result = $connection2->prepare($sql);
    $result->execute($data);
} catch (PDOException $e) {
}
if ($result->rowCount() == 1) {
    $row = $result->fetch();
    $gibbonHookID = $row['gibbonHookID'];
}

if (isActionAccessible($guid, $connection2, '/modules/ATL/atl_write.php') == false) {
    //Acess denied
    echo "<div class='error'>";
    echo __('Your request failed because you do not have access to this action.');
    echo '</div>';
} else {
    // Register scripts available to the core, but not included by default
    $page->scripts->add('chart');

    //Get action with highest precendence
    $highestAction = getHighestGroupedAction($guid, $_GET['q'], $connection2);
    if ($highestAction == false) {
        echo "<div class='error'>";
        echo __('The highest grouped action cannot be determined.');
        echo '</div>';
    } else {
        $alert = getAlert($guid, $connection2, 002);

        //Proceed!
        //Get class variable
        $gibbonCourseClassID = null;
        if (isset($_GET['gibbonCourseClassID'])) {
            $gibbonCourseClassID = $_GET['gibbonCourseClassID'];
        }
        if ($gibbonCourseClassID == '') {
            try {
                $data = array('gibbonSchoolYearID' => $session->get('gibbonSchoolYearID'), 'gibbonPersonID' => $session->get('gibbonPersonID'));
                $sql = 'SELECT gibbonCourse.nameShort AS course, gibbonCourseClass.nameShort AS class, gibbonCourseClass.gibbonCourseClassID FROM gibbonCourse, gibbonCourseClass, gibbonCourseClassPerson WHERE gibbonSchoolYearID=:gibbonSchoolYearID AND gibbonCourse.gibbonCourseID=gibbonCourseClass.gibbonCourseID AND gibbonCourseClass.gibbonCourseClassID=gibbonCourseClassPerson.gibbonCourseClassID AND gibbonCourseClassPerson.gibbonPersonID=:gibbonPersonID AND gibbonCourseClassPerson.role NOT LIKE "%Left" ORDER BY course, class';
                $result = $connection2->prepare($sql);
                $result->execute($data);
            } catch (PDOException $e) {
                echo "<div class='error'>".$e->getMessage().'</div>';
            }
            if ($result->rowCount() > 0) {
                $row = $result->fetch();
                $gibbonCourseClassID = $row['gibbonCourseClassID'];
            }
        }
        if ($gibbonCourseClassID == '') {
            $page->breadcrumbs->add(__('Write ATLs'));
            echo "<div class='warning'>";
            echo 'Use the class listing on the right to choose an ATL to write.';
            echo '</div>';
        } else {
            //Check existence of and access to this class.
            try {
                if ($highestAction == 'Write ATLs_all') {
                    $data = array('gibbonCourseClassID' => $gibbonCourseClassID);
                    $sql = "SELECT gibbonCourse.nameShort AS course, gibbonCourse.name AS courseName, gibbonCourseClass.nameShort AS class, gibbonYearGroupIDList FROM gibbonCourse JOIN gibbonCourseClass ON (gibbonCourse.gibbonCourseID=gibbonCourseClass.gibbonCourseID) WHERE gibbonCourseClassID=:gibbonCourseClassID AND gibbonCourseClass.reportable='Y' ";
                } else {
                    $data = array('gibbonCourseClassID' => $gibbonCourseClassID, 'gibbonPersonID' => $session->get('gibbonPersonID'), 'gibbonCourseClassID2' => $gibbonCourseClassID, 'gibbonPersonID2' => $session->get('gibbonPersonID'), 'gibbonSchoolYearID' => $session->get('gibbonSchoolYearID'));
                    $sql = "(SELECT gibbonCourse.nameShort AS course, gibbonCourse.name AS courseName, gibbonCourseClass.nameShort AS class, gibbonYearGroupIDList FROM gibbonCourse JOIN gibbonCourseClass ON (gibbonCourse.gibbonCourseID=gibbonCourseClass.gibbonCourseID) JOIN gibbonCourseClassPerson ON (gibbonCourseClassPerson.gibbonCourseClassID=gibbonCourseClass.gibbonCourseClassID) WHERE gibbonCourseClass.gibbonCourseClassID=:gibbonCourseClassID AND gibbonPersonID=:gibbonPersonID AND (role='Teacher' OR role='Assistant') AND gibbonCourseClass.reportable='Y')
                        UNION
                        (SELECT gibbonCourse.nameShort AS course, gibbonCourse.name AS courseName, gibbonCourseClass.nameShort AS class, gibbonYearGroupIDList FROM gibbonCourseClass JOIN gibbonCourse ON (gibbonCourseClass.gibbonCourseID=gibbonCourse.gibbonCourseID) JOIN gibbonDepartment ON (gibbonCourse.gibbonDepartmentID=gibbonDepartment.gibbonDepartmentID) JOIN gibbonDepartmentStaff ON (gibbonDepartmentStaff.gibbonDepartmentID=gibbonDepartment.gibbonDepartmentID) WHERE gibbonCourseClass.gibbonCourseClassID=:gibbonCourseClassID2 AND gibbonDepartmentStaff.gibbonPersonID=:gibbonPersonID2 AND gibbonDepartmentStaff.role='Coordinator' AND gibbonCourse.gibbonSchoolYearID=:gibbonSchoolYearID AND gibbonCourseClass.reportable='Y')";
                }
                $result = $connection2->prepare($sql);
                $result->execute($data);
            } catch (PDOException $e) {
                echo "<div class='error'>".$e->getMessage().'</div>';
            }
            if ($result->rowCount() != 1) {
                $page->breadcrumbs->add(__('Write ATLs'));
                echo "<div class='error'>";
                echo __('The specified record does not exist or you do not have access to it.');
                echo '</div>';
            } else {
                $row = $result->fetch();
                $courseName = $row['courseName'];
                $gibbonYearGroupIDList = $row['gibbonYearGroupIDList'];

                $page->breadcrumbs->add(__('Write {courseClass} ATLs', ['courseClass' => $row['course'].'.'.$row['class']]));

                if (isset($_GET['deleteReturn'])) {
                    $deleteReturn = $_GET['deleteReturn'];
                } else {
                    $deleteReturn = '';
                }
                $deleteReturnMessage = '';
                $class = 'error';
                if (!($deleteReturn == '')) {
                    if ($deleteReturn == 'success0') {
                        $deleteReturnMessage = __('Your request was completed successfully.');
                        $class = 'success';
                    }
                    echo "<div class='$class'>";
                    echo $deleteReturnMessage;
                    echo '</div>';
                }

                //Get teacher list
                $teaching = false;
                try {
                    $data = array('gibbonCourseClassID' => $gibbonCourseClassID);
                    $sql = "SELECT gibbonPerson.gibbonPersonID, title, surname, preferredName, gibbonCourseClassPerson.reportable FROM gibbonCourseClassPerson JOIN gibbonPerson ON (gibbonCourseClassPerson.gibbonPersonID=gibbonPerson.gibbonPersonID) WHERE (role='Teacher' OR role='Assistant') AND gibbonCourseClassID=:gibbonCourseClassID ORDER BY surname, preferredName";
                    $result = $connection2->prepare($sql);
                    $result->execute($data);
                } catch (PDOException $e) {
                    echo "<div class='error'>".$e->getMessage().'</div>';
                }

                if ($result->rowCount() > 0) {
                    echo "<h3 style='margin-top: 0px'>";
                    echo __('Teachers');
                    echo '</h3>';
                    echo '<ul>';
                    while ($row = $result->fetch()) {
                        if ($row['reportable'] != 'Y') continue;

                        echo '<li>'.Format::name($row['title'], $row['preferredName'], $row['surname'], 'Staff').'</li>';
                        if ($row['gibbonPersonID'] == $session->get('gibbonPersonID')) {
                            $teaching = true;
                        }
                    }
                    echo '</ul>';
                }

                //Print marks
                echo '<h3>';
                echo __('Marks');
                echo '</h3>';

                //Count number of columns
                try {
                    $data = array('gibbonCourseClassID' => $gibbonCourseClassID);
                    $sql = 'SELECT * FROM atlColumn WHERE gibbonCourseClassID=:gibbonCourseClassID ORDER BY complete, completeDate DESC';
                    $result = $connection2->prepare($sql);
                    $result->execute($data);
                } catch (PDOException $e) {
                    echo "<div class='error'>".$e->getMessage().'</div>';
                }
                $columns = $result->rowCount();
                if ($columns < 1) {
                    echo "<div class='warning'>";
                    echo __('There are no records to display.');
                    echo '</div>';
                } else {
                    $x = null;
                    if (isset($_GET['page'])) {
                        $x = $_GET['page'];
                    }
                    if ($x == '') {
                        $x = 0;
                    }
                    $columnsPerPage = 3;
                    $columnsThisPage = 3;

                    if ($columns < 1) {
                        echo "<div class='warning'>";
                        echo __('There are no records to display.');
                        echo '</div>';
                    } else {
                        if ($columns < 3) {
                            $columnsThisPage = $columns;
                        }
                        if ($columns - ($x * $columnsPerPage) < 3) {
                            $columnsThisPage = $columns - ($x * $columnsPerPage);
                        }
                        try {
                            $data = array('gibbonCourseClassID' => $gibbonCourseClassID);
                            $sql = 'SELECT * FROM atlColumn WHERE gibbonCourseClassID=:gibbonCourseClassID ORDER BY complete, completeDate DESC LIMIT '.($x * $columnsPerPage).', '.$columnsPerPage;
                            $result = $connection2->prepare($sql);
                            $result->execute($data);
                        } catch (PDOException $e) {
                            echo "<div class='error'>".$e->getMessage().'</div>';
                        }

                        //Work out details for external assessment display
                        $externalAssessment = false;
                        if (isActionAccessible($guid, $connection2, '/modules/External Assessment/externalAssessment_details.php')) {
                            $gibbonYearGroupIDListArray = (explode(',', $gibbonYearGroupIDList));
                            if (count($gibbonYearGroupIDListArray) == 1) {
                                $primaryExternalAssessmentByYearGroup = unserialize($container->get(SettingGateway::class)->getSettingByScope('School Admin', 'primaryExternalAssessmentByYearGroup'));
                                if ($primaryExternalAssessmentByYearGroup[$gibbonYearGroupIDListArray[0]] != '' and $primaryExternalAssessmentByYearGroup[$gibbonYearGroupIDListArray[0]] != '-') {
                                    $gibbonExternalAssessmentID = substr($primaryExternalAssessmentByYearGroup[$gibbonYearGroupIDListArray[0]], 0, strpos($primaryExternalAssessmentByYearGroup[$gibbonYearGroupIDListArray[0]], '-'));
                                    $gibbonExternalAssessmentIDCategory = substr($primaryExternalAssessmentByYearGroup[$gibbonYearGroupIDListArray[0]], (strpos($primaryExternalAssessmentByYearGroup[$gibbonYearGroupIDListArray[0]], '-') + 1));

                                    try {
                                        $dataExternalAssessment = array('gibbonExternalAssessmentID' => $gibbonExternalAssessmentID, 'category' => $gibbonExternalAssessmentIDCategory);
                                        $courseNameTokens = explode(' ', $courseName);
                                        $courseWhere = ' AND (';
                                        $whereCount = 1;
                                        foreach ($courseNameTokens as $courseNameToken) {
                                            if (strlen($courseNameToken) > 3) {
                                                $dataExternalAssessment['token'.$whereCount] = '%'.$courseNameToken.'%';
                                                $courseWhere .= "gibbonExternalAssessmentField.name LIKE :token$whereCount OR ";
                                                ++$whereCount;
                                            }
                                        }
                                        if ($whereCount < 1) {
                                            $courseWhere = '';
                                        } else {
                                            $courseWhere = substr($courseWhere, 0, -4).')';
                                        }
                                        $sqlExternalAssessment = "SELECT gibbonExternalAssessment.name AS assessment, gibbonExternalAssessmentField.name, gibbonExternalAssessmentFieldID, category FROM gibbonExternalAssessmentField JOIN gibbonExternalAssessment ON (gibbonExternalAssessmentField.gibbonExternalAssessmentID=gibbonExternalAssessment.gibbonExternalAssessmentID) WHERE gibbonExternalAssessmentField.gibbonExternalAssessmentID=:gibbonExternalAssessmentID AND category=:category $courseWhere ORDER BY name";
                                        $resultExternalAssessment = $connection2->prepare($sqlExternalAssessment);
                                        $resultExternalAssessment->execute($dataExternalAssessment);
                                    } catch (PDOException $e) {
                                        echo "<div class='error'>".$e->getMessage().'</div>';
                                    }
                                    if ($resultExternalAssessment->rowCount() >= 1) {
                                        $rowExternalAssessment = $resultExternalAssessment->fetch();
                                        $externalAssessment = true;
                                        $externalAssessmentFields = array();
                                        $externalAssessmentFields[0] = $rowExternalAssessment['gibbonExternalAssessmentFieldID'];
                                        $externalAssessmentFields[1] = $rowExternalAssessment['name'];
                                        $externalAssessmentFields[2] = $rowExternalAssessment['assessment'];
                                        $externalAssessmentFields[3] = $rowExternalAssessment['category'];
                                    }
                                }
                            }
                        }

                        //Print table header
                        echo "<p class='text-right mb-2 text-xs'>";
                            if ($x <= 0) {
                                echo __('Newer');
                            } else {
                                echo "<a href='".$session->get('absoluteURL')."/index.php?q=/modules/ATL/atl_write.php&gibbonCourseClassID=$gibbonCourseClassID&page=".($x - 1)."'>".__('Newer').'</a>';
                            }
                            echo ' | ';
                            if ((($x + 1) * $columnsPerPage) >= $columns) {
                                echo __('Older');
                            } else {
                                echo "<a href='".$session->get('absoluteURL')."/index.php?q=/modules/ATL/atl_write.php&gibbonCourseClassID=$gibbonCourseClassID&page=".($x + 1)."'>".__('Older').'</a>';
                            }
                        echo '</p>';

                        echo "<table class='mini' cellspacing='0' style='width: 100%; margin-top: 0px'>";
                        echo "<tr class='head' style='height: 120px'>";
                        echo "<th style='width: 150px; max-width: 200px'rowspan=2>";
                        echo __('Student');
                        echo '</th>';

                        //Show Baseline data header
                        if ($externalAssessment == true) {
                            echo "<th rowspan=2 style='width: 20px'>";
                            $title = __($externalAssessmentFields[2]).' | ';
                            $title .= __(substr($externalAssessmentFields[3], (strpos($externalAssessmentFields[3], '_') + 1))).' | ';
                            $title .= __($externalAssessmentFields[1]);

							//Get PAS
							$PAS = $container->get(SettingGateway::class)->getSettingByScope('System', 'primaryAssessmentScale');

                            $dataPAS = array('gibbonScaleID' => $PAS);
							$sqlPAS = 'SELECT * FROM gibbonScale WHERE gibbonScaleID=:gibbonScaleID';
							$resultPAS = $connection2->prepare($sqlPAS);
							$resultPAS->execute($dataPAS);

                            if ($resultPAS->rowCount() == 1) {
                                $rowPAS = $resultPAS->fetch();
                                $title .= ' | '.$rowPAS['name'].' '.__('Scale').' ';
                            }

                            echo "<div style='-webkit-transform: rotate(-90deg); -moz-transform: rotate(-90deg); -ms-transform: rotate(-90deg); -o-transform: rotate(-90deg); transform: rotate(-90deg);' title='$title'>";
                            echo __('Baseline').'<br/>';
                            echo '</div>';
                            echo '</th>';
                        }

                        $columnID = array();
                        for ($i = 0; $i < $columnsThisPage; ++$i) {
                            $row = $result->fetch();
                            if ($row === false) {
                                $columnID[$i] = false;
                            } else {
                                $columnID[$i] = $row['atlColumnID'];
                                $gibbonRubricID[$i] = $row['gibbonRubricID'];
                            }

                            //Column count
                            $span = 1;
                            $contents = true;
                            if ($gibbonRubricID[$i] != '') {
                                ++$span;
                            }
                            if ($span == 1) {
                                $contents = false;
                            }

                            echo "<th style='text-align: center; min-width: 140px' colspan=$span>";
                            echo "<span title='".htmlPrep($row['description'])."'>".$row['name'].'</span><br/>';
                            echo "<span style='font-size: 90%; font-style: italic; font-weight: normal'>";
                            if ($row['completeDate'] != '') {
                                echo __('Marked on').' '.Format::date($row['completeDate']).'<br/>';
                            } else {
                                echo __('Unmarked').'<br/>';
                            }
                            echo '</span><br/>';
                            if (isActionAccessible($guid, $connection2, '/modules/ATL/atl_write_data.php')) {
                                echo "<a href='".$session->get('absoluteURL')."/index.php?q=/modules/ATL/atl_write_data.php&gibbonCourseClassID=$gibbonCourseClassID&atlColumnID=".$row['atlColumnID']."'><img style='margin-top: 3px' title='".__('Enter Data')."' src='./themes/".$session->get('gibbonThemeName')."/img/markbook.png'/></a> ";
                            }
                            echo '</th>';
                        }
                        echo '</tr>';

                        echo "<tr class='head'>";
                        for ($i = 0; $i < $columnsThisPage; ++$i) {
                            if ($columnID[$i] == false or $contents == false) {
                                echo "<th style='text-align: center' colspan=$span>";

                                echo '</th>';
                            } else {
                                $leftBorder = false;
                                //Set up complete checkbox
                                $leftBorderStyle = '';
                                if ($leftBorder == false) {
                                    $leftBorder = true;
                                    $leftBorderStyle = 'border-left: 2px solid #666;';
                                }
                                echo "<th style='$leftBorderStyle text-align: center; width: 60px'>";
                                echo "<span>".__('Complete').'</span>';
                                echo '</th>';
                                //Set up rubric box
                                if ($gibbonRubricID[$i] != '') {
                                    $leftBorderStyle = '';
                                    if ($leftBorder == false) {
                                        $leftBorder = true;
                                        $leftBorderStyle = 'border-left: 2px solid #666;';
                                    }
                                    echo "<th style='$leftBorderStyle text-align: center; width: 30px'>";
                                    echo "<span>".__('Rubric').'</span>';
                                    echo '</th>';
                                }
                            }
                        }
                        echo '</tr>';

                        $count = 0;
                        $rowNum = 'odd';

                        try {
                            $dataStudents = array('gibbonCourseClassID' => $gibbonCourseClassID);
                            $sqlStudents = "SELECT title, surname, preferredName, gibbonPerson.gibbonPersonID, dateStart FROM gibbonCourseClassPerson JOIN gibbonPerson ON (gibbonCourseClassPerson.gibbonPersonID=gibbonPerson.gibbonPersonID) WHERE role='Student' AND gibbonCourseClassID=:gibbonCourseClassID AND status='Full' AND (dateStart IS NULL OR dateStart<='".date('Y-m-d')."') AND (dateEnd IS NULL  OR dateEnd>='".date('Y-m-d')."') AND gibbonCourseClassPerson.reportable='Y'  ORDER BY surname, preferredName";
                            $resultStudents = $connection2->prepare($sqlStudents);
                            $resultStudents->execute($dataStudents);
                        } catch (PDOException $e) {
                            echo "<div class='error'>".$e->getMessage().'</div>';
                        }
                        if ($resultStudents->rowCount() < 1) {
                            echo '<tr>';
                            echo '<td colspan='.($columns + 1).'>';
                            echo '<i>'.__('There are no records to display.').'</i>';
                            echo '</td>';
                            echo '</tr>';
                        } else {
                            while ($rowStudents = $resultStudents->fetch()) {
                                if ($count % 2 == 0) {
                                    $rowNum = 'even';
                                } else {
                                    $rowNum = 'odd';
                                }
                                ++$count;

                                //COLOR ROW BY STATUS!
                                echo "<tr class=$rowNum>";
                                echo '<td>';
                                echo "<div style='padding: 2px 0px'><b><a href='index.php?q=/modules/Students/student_view_details.php&gibbonPersonID=".$rowStudents['gibbonPersonID']."&hook=ATL&module=ATL&action=$highestAction&gibbonHookID=$gibbonHookID#".$gibbonCourseClassID."'>".Format::name('', $rowStudents['preferredName'], $rowStudents['surname'], 'Student', true).'</a><br/></div>';
                                echo '</td>';

                                if ($externalAssessment == true) {
                                    echo "<td style='text-align: center'>";
                                    try {
                                        $dataEntry = array('gibbonPersonID' => $rowStudents['gibbonPersonID'], 'gibbonExternalAssessmentFieldID' => $externalAssessmentFields[0]);
                                        $sqlEntry = "SELECT gibbonScaleGrade.value, gibbonScaleGrade.descriptor, gibbonExternalAssessmentStudent.date FROM gibbonExternalAssessmentStudentEntry JOIN gibbonExternalAssessmentStudent ON (gibbonExternalAssessmentStudentEntry.gibbonExternalAssessmentStudentID=gibbonExternalAssessmentStudent.gibbonExternalAssessmentStudentID) JOIN gibbonScaleGrade ON (gibbonExternalAssessmentStudentEntry.gibbonScaleGradeIDPrimaryAssessmentScale=gibbonScaleGrade.gibbonScaleGradeID) WHERE gibbonPersonID=:gibbonPersonID AND gibbonExternalAssessmentFieldID=:gibbonExternalAssessmentFieldID AND NOT gibbonScaleGradeIDPrimaryAssessmentScale='' ORDER BY date DESC";
                                        $resultEntry = $connection2->prepare($sqlEntry);
                                        $resultEntry->execute($dataEntry);
                                    } catch (PDOException $e) {
                                        echo "<div class='error'>".$e->getMessage().'</div>';
                                    }
                                    if ($resultEntry->rowCount() >= 1) {
                                        $rowEntry = $resultEntry->fetch();
                                        echo "<a title='".__($rowEntry['descriptor']).' | '.__('Test taken on').' '.Format::date($rowEntry['date'])."' href='index.php?q=/modules/Students/student_view_details.php&gibbonPersonID=".$rowStudents['gibbonPersonID']."&subpage=External Assessment'>".__($rowEntry['value']).'</a>';
                                    }
                                    echo '</td>';
                                }

                                for ($i = 0; $i < $columnsThisPage; ++$i) {
                                    $row = $result->fetch();
                                    try {
                                        $dataEntry = array('atlColumnID' => $columnID[($i)], 'gibbonPersonIDStudent' => $rowStudents['gibbonPersonID']);
                                        $sqlEntry = 'SELECT atlEntry.* FROM atlEntry JOIN atlColumn ON (atlEntry.atlColumnID=atlColumn.atlColumnID) WHERE atlEntry.atlColumnID=:atlColumnID AND gibbonPersonIDStudent=:gibbonPersonIDStudent';
                                        $resultEntry = $connection2->prepare($sqlEntry);
                                        $resultEntry->execute($dataEntry);
                                    } catch (PDOException $e) {
                                        echo "<div class='error'>".$e->getMessage().'</div>';
                                    }
                                    if ($resultEntry->rowCount() == 1) {
                                        $rowEntry = $resultEntry->fetch();
                                        $leftBorder = false;

                                        //Complete
                                        $leftBorderStyle = '';
                                        if ($leftBorder == false) {
                                            $leftBorder = true;
                                            $leftBorderStyle = 'border-left: 2px solid #666;';
                                        }
                                        echo "<td style='$leftBorderStyle text-align: center;'>";
                                            $checked = '';
                                            if ($rowEntry['complete'] == 'Y') {
                                                $checked = 'checked';
                                            }
                                            echo '<input disabled '.$checked.' type=\'checkbox\' name=\'complete[]\' value=\''.$rowEntry['complete'].'\'>';
                                        echo '</td>';
                                        //Rubric
                                        if ($gibbonRubricID[$i] != '') {
                                            $leftBorderStyle = '';
                                            if ($leftBorder == false) {
                                                $leftBorder = true;
                                                $leftBorderStyle = 'border-left: 2px solid #666;';
                                            }
                                            echo "<td style='$leftBorderStyle text-align: center;'>";
                                            if ($gibbonRubricID[$i] != '') {
                                                echo "<a class='thickbox' href='".$session->get('absoluteURL').'/fullscreen.php?q=/modules/ATL/atl_write_rubric.php&gibbonRubricID='.$gibbonRubricID[$i]."&gibbonCourseClassID=$gibbonCourseClassID&atlColumnID=".$columnID[$i].'&gibbonPersonID='.$rowStudents['gibbonPersonID']."&mark=FALSE&type=effort&width=1100&height=550'><img style='margin-bottom: -3px; margin-left: 3px' title='".__('View Rubric')."' src='./themes/".$session->get('gibbonThemeName')."/img/rubric.png'/></a>";
                                            }
                                            echo '</td>';
                                        }
                                    } else {
                                        $emptySpan = 1;
                                        if ($gibbonRubricID[$i] != '') {
                                            ++$emptySpan;
                                        }
                                        if ($emptySpan > 0) {
                                            echo "<td style='border-left: 2px solid #666; text-align: center' colspan=$emptySpan></td>";
                                        }
                                    }
                                    if (isset($submission[$i])) {
                                        if ($submission[$i] == true) {
                                            $leftBorderStyle = '';
                                            if ($leftBorder == false) {
                                                $leftBorder = true;
                                                $leftBorderStyle = 'border-left: 2px solid #666;';
                                            }
                                            echo "<td style='$leftBorderStyle text-align: center;'>";
                                            try {
                                                $dataWork = array('gibbonPlannerEntryID' => $rowEntry['gibbonPlannerEntryID'], 'gibbonPersonID' => $rowStudents['gibbonPersonID']);
                                                $sqlWork = 'SELECT * FROM gibbonPlannerEntryHomework WHERE gibbonPlannerEntryID=:gibbonPlannerEntryID AND gibbonPersonID=:gibbonPersonID ORDER BY count DESC';
                                                $resultWork = $connection2->prepare($sqlWork);
                                                $resultWork->execute($dataWork);
                                            } catch (PDOException $e) {
                                                echo "<div class='error'>".$e->getMessage().'</div>';
                                            }
                                            if ($resultWork->rowCount() > 0) {
                                                $rowWork = $resultWork->fetch();

                                                if ($rowWork['status'] == 'Exemption') {
                                                    $linkText = __('Exe');
                                                } elseif ($rowWork['version'] == 'Final') {
                                                    $linkText = __('Fin');
                                                } else {
                                                    $linkText = __('Dra').$rowWork['count'];
                                                }

                                                $style = '';
                                                $status = 'On Time';
                                                if ($rowWork['status'] == 'Exemption') {
                                                    $status = __('Exemption');
                                                } elseif ($rowWork['status'] == 'Late') {
                                                    $style = "style='color: #ff0000; font-weight: bold; border: 2px solid #ff0000; padding: 2px 4px'";
                                                    $status = __('Late');
                                                }

                                                if ($rowWork['type'] == 'File') {
                                                    echo "<span title='".$rowWork['version'].". $status. ".__('Submitted at').' '.substr($rowWork['timestamp'], 11, 5).' '.__('on').' '.Format::date(substr($rowWork['timestamp'], 0, 10))."' $style><a href='".$session->get('absoluteURL').'/'.$rowWork['location']."'>$linkText</a></span>";
                                                } elseif ($rowWork['type'] == 'Link') {
                                                    echo "<span title='".$rowWork['version'].". $status. ".__('Submitted at').' '.substr($rowWork['timestamp'], 11, 5).' '.__('on').' '.Format::date(substr($rowWork['timestamp'], 0, 10))."' $style><a target='_blank' href='".$rowWork['location']."'>$linkText</a></span>";
                                                } else {
                                                    echo "<span title='$status. ".__('Recorded at').' '.substr($rowWork['timestamp'], 11, 5).' '.__('on').' '.Format::date(substr($rowWork['timestamp'], 0, 10))."' $style>$linkText</span>";
                                                }
                                            } else {
                                                if (date('Y-m-d H:i:s') < $homeworkDueDateTime[$i]) {
                                                    echo "<span title='".__('Pending')."'>Pen</span>";
                                                } else {
                                                    if ($rowStudents['dateStart'] > $lessonDate[$i]) {
                                                        echo "<span title='".__('Student joined school after assessment was given.')."' style='color: #000; font-weight: normal; border: 2px none #ff0000; padding: 2px 4px'>".__('NA').'</span>';
                                                    } else {
                                                        if ($rowSub['homeworkSubmissionRequired'] == 'Compulsory') {
                                                            echo "<span title='".__('Incomplete')."' style='color: #ff0000; font-weight: bold; border: 2px solid #ff0000; padding: 2px 4px'>".__('Inc').'</span>';
                                                        } else {
                                                            echo "<span title='".__('Not submitted online')."'>".__('NA').'</span>';
                                                        }
                                                    }
                                                }
                                            }
                                            echo '</td>';
                                        }
                                    }
                                }
                                echo '</tr>';
                            }
                        }
                        echo '</table>';
                    }
                }
            }
        }

        //Print sidebar
        $session->set('sidebarExtra', sidebarExtraATL($guid, $connection2, $gibbonCourseClassID, 'write', $highestAction));
    }
}
