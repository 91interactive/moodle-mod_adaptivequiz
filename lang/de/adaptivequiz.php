<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * English language pack for Adaptive Quiz
 *
 * @package    mod_adaptivequiz
 * @category   string
 * @copyright  2023 YOUR NAME <your@email.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['modulename'] = 'Adaptive Quiz';
$string['modulenameplural'] = 'Adaptive Quizs';
$string['pluginadministration'] = 'Adaptive Quiz administration';
$string['pluginname'] = 'Adaptive Quiz';

$string['testlength'] = 'Test Länge';
$string['testlengthDescription'] = 'Anzahl an vorzugebenden Aufgaben';
$string['testlengthDescription_help'] = 'Anzahl an vorzugebenden Aufgaben';
$string['testduration'] = 'Testzeit in Minuten';
$string['testdurationDescription'] = 'Maximale Testzeit';
$string['testdurationDescription_help'] = 'Maximale Testzeit';

$string['testsettingsheader'] = 'Test Einstellungen';

$string['selecttasktypes'] = 'Aufgabentype wählen';
$string['selecttasktypesDescription'] = 'Aufgabentype wählen';
$string['selecttasktypesDescription_help'] = 'Hier wird spezifiziert, wie die Aufgabenauswahl für nicht adaptive Tests bzw.
nicht adaptive Teile des Gesamttests erfolgt';
$string['notadaptivepartheader'] = 'Nicht adavptiver Teil';
// $string['adaptiveSettingsDescription'] = 'Hier wird spezifiziert, wie die Aufgabenauswahl für nicht adaptive Tests bzw.
// nicht adaptive Teile des Gesamttests erfolgt';

$string['numbercalibrationclusters'] = "Anzahl Kalibrierungscluster";
$string['numbercalibrationclustersDescription'] = "Anzahl Kalibrierungscluster";
$string['numbercalibrationclustersDescription_help'] = "Anzahl an Aufgabenclustern (Cluster = Gruppe von Aufgaben), die Aufgaben enthalten, die noch keine Parameterschätzung haben und daher erst kalibriert werden müssen. Wenn der Test nur aus neuen (unkalibrierten) Aufgaben besteht, enthält der Test nur Kalibrierungscluster. Kalibrierungscluster werden jeder Testperson vorgegeben";

$string['numberlinkingclusters'] = "Anzahl Linkingcluster";
$string['numberlinkingclustersDescription'] = "Anzahl Linkingcluster";
$string['numberlinkingclustersDescription_help'] = "Anzahl an Aufgabenclustern, die Linkaufgaben (Items zur Verlinkung der Berichtsmetrik über die Zeit) enthalten. Linkaufgaben haben bereits eine Parameterschätzung. Linkingcluster werden jeder Testperson vorgegeben.";

$string['numberadaptivclusters'] = "Anzahl adaptiver Cluster";
$string['numberadaptivclustersDescription'] = "Anzahl adaptiver Cluster";
$string['numberadaptivclustersDescription_help'] = "Anzahl an Aufgabenclustern, in denen Aufgaben adaptiv gewählt werden. Aufgaben hier verfügen bereits über eine Parameterschätzung.";

$string['personalparameterestimation'] = "Personenparameterschätzung";
$string['personalparameterestimationDescription'] = "Personenparameterschätzung";
$string['personalparameterestimationDescription_help'] = "Spezifiziert den Schätzer, der genutzt wird, um die vorläufige Personenfähigkeit während des Tests nach Beantwortung jeder einzelnen Aufgabe zu schätzen.";

$string['adaptivepart'] = "Aufgabenauswahl adaptiver Teil";
$string['adaptivepartDescription'] = "Aufgabenauswahl adaptiver Teil";
$string['adaptivepartDescription_help'] = "Spezifiziert Kriterium nach dem Aufgaben während des adaptiven Tests ausgewählt werden.";

$string['suitabletasks'] = "Anzahl der am besten passenden Aufgaben";
$string['suitabletasksdescription'] = "Anzahl der am besten passenden Aufgaben";
$string['suitabletasksdescription_help'] = "Anzahl der am besten passenden Aufgaben, aus denen zufällig gewählt werden soll";

$string['sequential'] = "Sequentiell";
$string['random'] = "Zufällig";

$string['messagebeforetest']="Nachricht bevor der Test startet";
$string['messagebeforetestDescription']="Nachricht bevor der Test startet";
$string['messagebeforetestDescription_help']="Seite nach den Test-/Klausurinstruktionen; Dient hauptsächlich dazu, dass bei einer Klausur alle gleichzeitig mit der Bearbeitung beginnen. Wenn man das Feld leer lässt, gibt es die entsprechende Seite im Test dann nicht. Kann ggf. auch weggelassen werden";

$string['messageatlastpage']="Nachricht auf der letzten Seite des Tests";
$string['messageatlastpageDescription']="Nachricht auf der letzten Seite des Tests";
$string['messageatlastpageDescription_help']="Könnte für das individuelle Feedback genutzt werden. Heißt, entweder steht für alle das gleiche da, oder, wenn wir  individuelles Feedback erlauben, steht das dann jeweils auf der letzten Seite.";

$string['contentAreas'] = "Benutzerdefinierte Angabe von Anteilen einzelner Inhaltsbereicheam Gesamttest?";

$string['formtextareaempty'] = 'Textarea darf nicht leer sein';
$string['detaildtestresults'] = 'Detailierte Test Ergebnisse';

