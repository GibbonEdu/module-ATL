<?php
//USE ;end TO SEPERATE SQL STATEMENTS. DON'T USE ;end IN ANY OTHER PLACES!

$sql = array();
$count = 0;

//v1.0.00 - FIRST VERSION, SO NO CHANGES
$sql[$count][0] = '1.0.00';
$sql[$count][1] = '';

//v1.0.01
++$count;
$sql[$count][0] = '1.0.01';
$sql[$count][1] = '';

//v1.0.02
++$count;
$sql[$count][0] = '1.0.02';
$sql[$count][1] = '';

//v1.1.00
++$count;
$sql[$count][0] = '1.1.00';
$sql[$count][1] = "
ALTER TABLE `atlEntry` DROP `comment`;end
ALTER TABLE `atlEntry` ADD `complete` ENUM('Y','N') NOT NULL DEFAULT 'N' AFTER `gibbonPersonIDStudent`;end
ALTER TABLE `atlColumn` DROP `comment`;end
";

//v1.1.01
++$count;
$sql[$count][0] = '1.1.01';
$sql[$count][1] = '';

//v1.1.02
++$count;
$sql[$count][0] = '1.1.02';
$sql[$count][1] = '';

//v1.1.03
++$count;
$sql[$count][0] = '1.1.03';
$sql[$count][1] = "
ALTER TABLE `atlEntry` ADD INDEX(`atlColumnID`);end
ALTER TABLE `atlEntry` ADD INDEX(`gibbonPersonIDStudent`);end
ALTER TABLE `atlColumn` ADD INDEX(`gibbonCourseClassID`);end
ALTER TABLE `atlColumn` ADD INDEX(`gibbonRubricID`);end
";

//v1.2.00
++$count;
$sql[$count][0] = '1.2.00';
$sql[$count][1] = '';

//v1.3.00
++$count;
$sql[$count][0] = '1.3.00';
$sql[$count][1] = '';

//v1.4.00
++$count;
$sql[$count][0] = '1.4.00';
$sql[$count][1] = '';

//v1.4.01
++$count;
$sql[$count][0] = '1.4.01';
$sql[$count][1] = '';

//v1.4.02
++$count;
$sql[$count][0] = '1.4.02';
$sql[$count][1] = '';

//v1.4.03
++$count;
$sql[$count][0] = '1.4.03';
$sql[$count][1] = '';

//v1.4.04
++$count;
$sql[$count][0] = '1.4.04';
$sql[$count][1] = '';

//v1.4.05
++$count;
$sql[$count][0] = '1.4.05';
$sql[$count][1] = '';

//v1.4.06
++$count;
$sql[$count][0] = '1.4.06';
$sql[$count][1] = '';

//v1.4.07
++$count;
$sql[$count][0] = '1.4.07';
$sql[$count][1] = '';

//v1.4.08
++$count;
$sql[$count][0] = '1.4.08';
$sql[$count][1] = '';

//v1.4.09
++$count;
$sql[$count][0] = '1.4.09';
$sql[$count][1] = '';

//v1.4.10
++$count;
$sql[$count][0] = '1.4.10';
$sql[$count][1] = '';

//v1.4.11
++$count;
$sql[$count][0] = '1.4.11';
$sql[$count][1] = '';

//v1.4.12
++$count;
$sql[$count][0] = '1.4.12';
$sql[$count][1] = '';

//v1.4.13
++$count;
$sql[$count][0] = '1.4.13';
$sql[$count][1] = '';

//v1.5.00
++$count;
$sql[$count][0] = '1.5.00';
$sql[$count][1] = '';

//v1.5.01
++$count;
$sql[$count][0] = '1.5.01';
$sql[$count][1] = '';

//v1.5.02
++$count;
$sql[$count][0] = '1.5.02';
$sql[$count][1] = '';
