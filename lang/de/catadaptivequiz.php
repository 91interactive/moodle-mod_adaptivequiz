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
 * @package    mod_catadaptivequiz
 * @category   string
 * @copyright  2023 YOUR NAME <your@email.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['modulename'] = 'KAT-HS Adaptive Quiz';
$string['modulenameplural'] = 'KAT-HS Adaptive Quizs';
$string['pluginadministration'] = 'KAT-HS Adaptive Quiz administration';
$string['pluginname'] = 'KAT-HS Adaptive Quiz';

$string['testlength'] = 'Test Länge';
$string['testlengthDescription'] = 'Anzahl an vorzugebenden Aufgaben';
$string['testlengthDescription_help'] = 'Anzahl an vorzugebenden Aufgaben';
$string['testduration'] = 'Testzeit in Minuten';
$string['testdurationDescription'] = 'Maximale Testzeit';
$string['testdurationDescription_help'] = 'Maximale Testzeit';

$string['testsettingsheader'] = 'Test Einstellungen';

$string['selecttasktypes'] = 'Aufgabenauswahl nicht adaptiver Teil';
$string['selecttasktypesDescription'] = 'Aufgabenauswahl nicht adaptiver Teil.';
$string['selecttasktypesDescription_help'] = 'Diese Einstellung bezieht sich auf Aufgaben, die nicht adaptiv ausgewählt werden. Diese Aufgaben können entweder zufällig oder sequentiell gewählt werden, wobei die sequentielle Auswahl die Reihenfolge realisiert, in der die Aufgaben im Fragenkatalog spezifiziert sind.';
$string['notadaptivepartheader'] = 'Nicht adavptiver Teil';
// $string['adaptiveSettingsDescription'] = 'Hier wird spezifiziert, wie die Aufgabenauswahl für nicht adaptive Tests bzw.
// nicht adaptive Teile des Gesamttests erfolgt';

$string['numbercalibrationclusters'] = 'Anzahl Kalibrierungscluster';
$string['numbercalibrationclustersDescription'] = 'Anzahl Kalibrierungscluster';
$string['numbercalibrationclustersDescription_help'] = 'Anzahl an Aufgabenclustern mit neuen Aufgaben, die dazu dienen, den Aufgabepool zu vergrößern. Hinweis: Die Summe der drei Clusterarten bestimmt darüber hinaus die Anzahl der Testhefte, die automatisiert zusammengestellt werden. Basierend auf sogenannten Balanced Latin Square Designs (z. B. Williams, 1949; Frey, Hartig & Rupp, 2009) wird für Clusterpositionen und Carry-Over-Effekte erster Ordnung ausbalanciert. Summe der drei Clusterarten muss einem ganzzahligen Anteil der Testlänge entsprechen. Die Maximale Anzahl der Cluster ist gleich der Testlänge.';

$string['numberlinkingclusters'] = 'Anzahl Linkingcluster';
$string['numberlinkingclustersDescription'] = 'Anzahl Linkingcluster';
$string['numberlinkingclustersDescription_help'] = 'Anzahl an Aufgabenclustern, mit Aufgaben, die bereits über eine Parameterschätzung verfügen und die dazu genutzt werden, aufeinanderfolgende Testzyklen miteinander zu verbinden. Hinweis: Die Summe der drei Clusterarten bestimmt darüber hinaus die Anzahl der Testhefte, die automatisiert zusammengestellt werden. Basierend auf sogenannten Balanced Latin Square Designs (z. B. Williams, 1949; Frey, Hartig & Rupp, 2009) wird für Clusterpositionen und Carry-Over-Effekte erster Ordnung ausbalanciert. Summe der drei Clusterarten muss einem ganzzahligen Anteil der Testlänge entsprechen. Die Maximale Anzahl der Cluster ist gleich der Testlänge.';

$string['numberadaptiveclusters'] = 'Anzahl adaptiver Cluster';
$string['numberadaptiveclustersDescription'] = 'Anzahl adaptiver Cluster';
$string['numberadaptiveclustersDescription_help'] = 'Anzahl an Aufgabenclustern, deren Aufgaben adaptiv gewählt werden sollen. Hinweis: Die Summe der drei Clusterarten bestimmt darüber hinaus die Anzahl der Testhefte, die automatisiert zusammengestellt werden. Basierend auf sogenannten Balanced Latin Square Designs (z. B. Williams, 1949; Frey, Hartig & Rupp, 2009) wird für Clusterpositionen und Carry-Over-Effekte erster Ordnung ausbalanciert. Summe der drei Clusterarten muss einem ganzzahligen Anteil der Testlänge entsprechen. Die Maximale Anzahl der Cluster ist gleich der Testlänge.';

$string['personalparameterestimation'] = 'Personenparameterschätzung';
$string['personalparameterestimationDescription'] = 'Personenparameterschätzung';
$string['personalparameterestimationDescription_help'] = 'Die Personenparameterschätzung während des Tests. Zur Auswahl stehen folgende Schätzer: Expected-A-Posteriori (EAP; Bock & Mislevy, 1982), Maximum-A-Posteriori (MAP; Mislevy, 1986), Weighted Likelihood Estimate (WLE; Warm, 1989), Maximum Likelihood Estimate (ML; Lord, 1980).';

$string['adaptivepart'] = 'Aufgabenauswahl adaptiver Teil';
$string['adaptivepartDescription'] = 'Aufgabenauswahl adaptiver Teil';
$string['adaptivepartDescription_help'] = 'Das Kriterium für die adaptive Aufgabenauswahl in adaptiven Clustern. Zur Auswahl stehen: Maximum Information (Default), Maximum Expected Posterior Variance, Maximum Likelihood Weighted Information, Maximum Posterior Weighted Information, Maximum Expected Information, Integration based Kullback-Leibler';

$string['randomesqueexposurecontrol'] = 'Randomesque Exposure Control';
$string['randomesqueexposurecontrolDescription'] = 'Randomesque Exposure Control';
$string['randomesqueexposurecontrolDescription_help'] = 'Gibt an, ob für den adaptiven Teil des Tests die 
Randomesque Exposure Control Methode (Kingsbury & Zara, 1989) verwendet werden soll. Setzt man hier einen Haken, so ist zusätzlich die Anzahl der am besten passenden Aufgaben, aus 
denen zufällig gewählt werden soll, anzugeben.';

$string['suitabletasks'] = 'Anzahl der am besten passenden Aufgaben';
$string['suitabletasksdescription'] = 'Anzahl der am besten passenden Aufgaben';
$string['suitabletasksdescription_help'] = 'Nur wenn Randomesque Exposure Control ausgewählt wurde: Spezifiziert die Anzahl an am besten passenden Aufgaben aus denen zufällig gewählt werden soll.';

$string['sequential'] = 'Sequentiell';
$string['random'] = 'Zufällig';

$string['messageatlastpage'] = 'Nachricht auf der letzten Seite des Tests';
$string['messageatlastpageDescription'] = 'Nachricht auf der letzten Seite des Tests';
$string['messageatlastpageDescription_help'] = 'Könnte für das individuelle Feedback genutzt werden. Heißt, entweder steht für alle das gleiche da, oder, wenn wir  individuelles Feedback erlauben, steht das dann jeweils auf der letzten Seite.';

$string['contentareas'] = 'Benutzerdefinierte Angabe von Anteilen einzelner Inhaltsbereicheam Gesamttest';
$string['contentareaDistributionDescription'] = 'Verteilung Inhaltsbereiche';
$string['contentareaDistributionDescription_help'] = 'Semikolon getrennte Liste die jeweils den Inhaltsbereich sowie den Anteil (im Wertebereich von 0 bis1) des jeweiligen Inhaltsbereiches am Gesamttest spezifiziert. Z.B. catname1:0.2;catname2:0.3;catname3:0.5';

$string['formtextareaempty'] = 'Textarea darf nicht leer sein';
$string['detaildtestresults'] = 'Detailierte Test Ergebnisse';

$string['catadaptivequizname'] = 'Name';
$string['catadaptivequizname_help'] = 'Geben Sie den Namen der Adaptive Quiz-Instanz ein';
$string['catadaptivequiz:addinstance'] = 'Neuen adaptiven Test hinzufügen';
$string['catadaptivequiz:viewreport'] = 'Berichte des adaptiven Tests anzeigen';
$string['catadaptivequiz:reviewattempts'] = 'Überprüfen Sie die Abgaben des adaptiven Tests';
$string['catadaptivequiz:attempt'] = 'Versuch adaptiver Test';
$string['abilityestimated'] = 'Geschätzte Fähigkeit';
$string['abilityestimated_help'] = 'Die geschätzte Fähigkeit eines Prüfungsteilnehmers entspricht der Schwierigkeit der Frage, bei der der Prüfungsteilnehmer eine 50% Wahrscheinlichkeit hat, die Frage richtig zu beantworten. Um das Leistungsniveau zu identifizieren, passen Sie den Fähigkeitswert mit dem Frageniveau-Bereich ab (siehe den Bereich nach dem \'/\' Symbol).';
$string['activityavailabilitymissingquestionbehaviour'] = 'Das erforderliche Frageverhalten \'{$a}\' wurde nicht in den installierten Verhaltensweisen gefunden';
$string['activityavailabilitymanagernotification'] = 'WARNUNG: Die Seitenkonfiguration ist nicht kompatibel mit den Aktivitätsanforderungen: {$a}. Die Aktivität wird für Studierende nicht verfügbar sein, es sei denn, die Seitenkonfiguration wird aktualisiert.';
$string['activityavailabilityquestionbehaviourdisabled'] = 'Das erforderliche Frageverhalten \'{$a}\' ist in der Konfiguration der Seitenfragen deaktiviert';
$string['activityavailabilitystudentnotification'] = 'Die Seite ist nicht richtig konfiguriert, um das Quiz auszuführen. Bitte raten Sie dem Kursmanager, die Aktivitätsansichtsseite zu besuchen, um nach angezeigten Warnungen zu suchen.';
$string['attemptfeedbackdefaulttext'] = 'Sie haben den Versuch beendet, danke für die Teilnahme am Quiz!';
$string['attemptquestion_ability'] = 'Fähigkeitsmessung';
$string['attemptquestionsprogress'] = 'Fragenfortschritt: {$a}';
$string['attemptquestionsprogress_help'] = 'Die maximale Anzahl der hier angezeigten Fragen ist nicht notwendigerweise die Anzahl der Fragen, die Sie während des Quiz beantworten müssen. Es ist die MAXIMALE MÖGLICHE Anzahl von Fragen, die Sie nehmen könnten, das Quiz kann früher enden, wenn die Fähigkeitsmessung ausreichend definiert ist.';
$string['attempt_summary'] = 'Versuchszusammenfassung';
$string['attemptsusernoprevious'] = 'Sie haben dieses Quiz noch nicht versucht.';
$string['attemptsuserprevious'] = 'Ihre vorherigen Versuche';
$string['attemptnofirstquestion'] = 'Entschuldigung, aber konnte die erste Frage nicht definieren, um den Versuch zu starten, das Quiz ist möglicherweise falsch konfiguriert.';
$string['completionattemptcompletedcminfo'] = 'Einen Versuch abschließen';
$string['completionattemptcompletedform'] = 'Student muss mindestens einen abgeschlossenen Versuch bei dieser Aktivität haben';
$string['eventattemptcompleted'] = 'Versuch abgeschlossen';
$string['modformshowattemptprogress'] = 'Quizfortschritt den Studierenden anzeigen';
$string['modformshowattemptprogress_help'] = 'Wenn ausgewählt, wird während des Versuchs ein Fortschrittsbalken angezeigt, der darstellt, wie viele Fragen aus der maximalen Anzahl beantwortet sind.';
$string['showabilitymeasure'] = 'Fähigkeitsmessung den Studierenden anzeigen';
$string['showabilitymeasure_help'] = 'Manchmal kann es nützlich sein, die Schätzungen der Fähigkeiten den Studierenden nach der Teilnahme an einem adaptiven Quiz zu offenbaren. Mit dieser Einstellung aktiviert kann ein Student die Fähigkeitsschätzung in der Versuchszusammenfassung und direkt nach Beendigung eines Versuchs sehen.';
$string['questionspoolerrornovalidstartingquestions'] = 'Die ausgewählten Fragenkategorien enthalten keine Fragen, die ordnungsgemäß markiert sind, um dem ausgewählten Startniveau der Schwierigkeit zu entsprechen.';
$string['reportattemptanswerdistributiontab'] = 'Antwortverteilung';
$string['reportattemptanswerdistributiontabletitle'] = 'Tabellenansicht der Antwortverteilung';
$string['reportattemptgraphtab'] = 'Versuchdetails';
$string['reportattemptgraphtabletitle'] = 'Tabellenansicht der Versuchdetails';
$string['reportattemptquestionsdetailstab'] = 'Fragendetails';
$string['reportattemptreviewpageheading'] = '{$a->quizname} - Überprüfung des Versuchs von {$a->fullname} eingereicht am {$a->finished}';
$string['reportattemptsbothenrolledandnotenrolled'] = 'alle Benutzer, die jemals Versuche unternommen haben';
$string['reportattemptsdownloadfilename'] = '{$a}_Versuchsbericht';
$string['reportattemptsenrolledwithattempts'] = 'Teilnehmer, die Versuche unternommen haben';
$string['reportattemptsenrolledwithnoattempts'] = 'Teilnehmer ohne unternommene Versuche';
$string['reportattemptsfilterformsubmit'] = 'Filter';
$string['reportattemptsfilterincludeinactiveenrolments'] = 'Benutzer mit inaktiven Einschreibungen einschließen';
$string['reportattemptsfilterincludeinactiveenrolments_help'] = 'Ob Benutzer mit ausgesetzten Einschreibungen einbezogen werden sollen.';
$string['reportattemptsfilterusers'] = 'Anzeigen';
$string['reportattemptsfilterformheader'] = 'Filterung';
$string['reportattemptsnotenrolled'] = 'nicht eingeschriebene Benutzer, die Versuche unternommen haben';
$string['reportattemptspersistentfilter'] = 'Dauerhafter Filter';
$string['reportattemptspersistentfilter_help'] = 'Wenn aktiviert, werden die unten angegebenen Filtereinstellungen beim Absenden gespeichert und dann jedes Mal angewendet, wenn Sie die Berichtsseite besuchen.';
$string['reportattemptsprefsformheader'] = 'Berichtseinstellungen';
$string['reportattemptsprefsformsubmit'] = 'Anwenden';
$string['reportattemptsresetfilter'] = 'Filter zurücksetzen';
$string['reportattemptsshowinitialbars'] = 'Initialenleiste anzeigen';
$string['reportattemptsusersperpage'] = 'Anzahl der angezeigten Benutzer:';
$string['reportattemptsummarytab'] = 'Versuchszusammenfassung';
$string['reportindividualuserattemptpageheading'] = '{$a->quizname} - individueller Benutzerversuchsbericht für {$a->username}';
$string['reportuserattemptstitleshort'] = 'Versuche von {$a}';
$string['reportquestionanalysispageheading'] = '{$a} - Fragenbericht';
$string['settingsdefaultsettingsheading'] = 'Standardkonfigurationen von Aktivitätsinstanzen';
$string['settingsdefaultsettingsheadinginfo'] = 'Wenn Ihre adaptiven Quizze in den Kursen identisch konfiguriert sein sollen, möchten Sie hier vielleicht einige Standardkonfigurationen dafür festlegen.';


$string['modulename_help'] = 'Das KAT-HS Adaptive Quiz Plugin ermöglicht es adaptive Tests zu erstellen, die die Fähigkeiten der Teilnehmer effizient messen. Dabei werden die Fragen in Echtzeit aus einem Fragenpool ausgewählt, sodass sie zum geschätzten Fähigkeitsniveau der aktuellen Testperson passen. Das heißt, wenn eine Frage richtig beantwortet wird, wird als nächstes eine schwierigere Frage aus dem Fragenpool ausgewählt. Wenn eine Frage falsch beantwortet wird, wird als nächstes eine weniger schwierige Frage gestellt. So kann das Fähigkeitsniveau der Testpersonen effizient bestimmt werden.';
$string['nonewmodules'] = 'Keine Adaptive Quiz-Instanzen gefunden';
$string['catadaptivequizname'] = 'Name';
$string['catadaptivequizname_help'] = 'Name des Tests';


$string['attemptsallowed'] = 'Erlaubte Versuche';
$string['attemptsallowed_help'] = 'Die Anzahl der Versuche, die ein Student für diese Aktivität unternehmen darf';
$string['requirepassword'] = 'Passwort erforderlich';
$string['requirepassword_help'] = 'Studenten müssen ein Passwort eingeben, bevor sie ihren Versuch beginnen';
$string['browsersecurity'] = 'Browser-Sicherheit';
$string['minimumquestions'] = 'Mindestanzahl von Fragen';
$string['minimumquestions_help'] = 'Die Mindestanzahl von Fragen, die der Student versuchen muss';
$string['maximumquestions'] = 'Höchstanzahl von Fragen';
$string['maximumquestions_help'] = 'Die maximale Anzahl von Fragen, die der Student versuchen kann';
$string['startinglevel'] = 'Startniveau der Schwierigkeit';
$string['startinglevel_help'] = 'Wenn der Student einen Versuch beginnt, wählt die Aktivität zufällig eine Frage aus, die dem Schwierigkeitsgrad entspricht';
$string['lowestlevel'] = 'Niedrigstes Schwierigkeitsniveau';
$string['lowestlevel_help'] = 'Das niedrigste oder am wenigsten schwierige Niveau, aus dem die Bewertung Fragen auswählen kann. Während eines Versuchs wird die Aktivität nicht über dieses Schwierigkeitsniveau hinausgehen';
$string['highestlevel'] = 'Höchstes Schwierigkeitsniveau';
$string['highestlevel_help'] = 'Das höchste oder schwierigste Niveau, aus dem die Bewertung Fragen auswählen kann. Während eines Versuchs wird die Aktivität nicht über dieses Schwierigkeitsniveau hinausgehen';
$string['questionpool'] = 'Fragenpool';
$string['questionpool_help'] = 'Wählen Sie die Fragekategorie(n), aus denen die Aktivität während eines Versuchs Fragen ziehen wird';
$string['formelementempty'] = 'Geben Sie eine positive Ganzzahl von 1 bis 999 ein';
$string['formelementnumeric'] = 'Geben Sie einen numerischen Wert von 1 bis 999 ein';
$string['formelementnegative'] = 'Geben Sie eine positive Zahl von 1 bis 999 ein';
$string['formminquestgreaterthan'] = 'Die Mindestanzahl von Fragen muss kleiner als die Höchstanzahl von Fragen sein';
$string['formlowlevelgreaterthan'] = 'Das niedrigste Niveau muss niedriger als das höchste Niveau sein';
$string['formstartleveloutofbounds'] = 'Das Startniveau muss eine Zahl sein, die zwischen dem niedrigsten und dem höchsten Niveau liegt';
$string['standarderror'] = 'Standardfehler zum Stoppen';
$string['standarderror_help'] = 'Wenn der Standardfehler der individuellen Fähigkeitsschätzung unter diesen Wert fällt, stoppt der Test automatisch. Wird nicht beachtet, wenn auf 0 gesetzt. Vorrang hat immer die Höchstanzahl von Aufgaben.';
$string['formelementdecimal'] = 'Geben Sie eine Dezimalzahl ein. Maximal 10 Stellen lang und maximal 5 Stellen rechts vom Dezimalpunkt';
$string['attemptfeedback'] = 'Feedback zum Versuch';
$string['attemptfeedback_help'] = 'Das Feedback zum Versuch wird dem Benutzer angezeigt, sobald der Versuch beendet ist';
$string['formquestionpool'] = 'Wählen Sie mindestens eine Fragenkategorie aus';
$string['submitanswer'] = 'Antwort einreichen';
$string['startattemptbtn'] = 'Versuch starten';
$string['errorfetchingquest'] = 'Frage für Level {$a} konnte nicht abgerufen werden';
$string['leveloutofbounds'] = 'Angefordertes Level {$a} liegt außerhalb des zulässigen Bereichs für den Versuch';
$string['errorattemptstate'] = 'Fehler bei der Bestimmung des Zustands des Versuchs';
$string['nopermission'] = 'Sie haben keine Berechtigung, diese Ressource anzusehen';
$string['maxquestattempted'] = 'Maximale Anzahl von versuchten Fragen';
$string['notyourattempt'] = 'Dies ist nicht Ihr Versuch der Aktivität';
$string['noattemptsallowed'] = 'Keine weiteren Versuche bei dieser Aktivität erlaubt';
$string['numofattemptshdr'] = 'Anzahl der Versuche';
$string['standarderrorhdr'] = 'Standardfehler';
$string['errorlastattpquest'] = 'Fehler bei der Überprüfung des Antwortwerts für die zuletzt versuchte Frage';
$string['errorsumrightwrong'] = 'Summe der richtigen und falschen Antworten entspricht nicht der Gesamtzahl der versuchten Fragen';
$string['calcerrorwithinlimits'] = 'Berechneter Standardfehler von {$a->calerror} liegt innerhalb der durch die Aktivität festgelegten Grenzen {$a->definederror}';
$string['missingtagprefix'] = 'Fehlendes Tag-Präfix';
$string['recentactquestionsattempted'] = 'Versuchte Fragen: {$a}';
$string['recentattemptstate'] = 'Zustand des Versuchs:';
$string['recentinprogress'] = 'In Bearbeitung';
$string['notinprogress'] = 'Dieser Versuch ist nicht in Bearbeitung.';
$string['recentcomplete'] = 'Abgeschlossen';
$string['functiondisabledbysecuremode'] = 'Diese Funktionalität ist derzeit deaktiviert';
$string['enterrequiredpassword'] = 'Erforderliches Passwort eingeben';
$string['requirepasswordmessage'] = 'Um diesen Quizversuch zu starten, müssen Sie das Quizpasswort kennen';
$string['wrongpassword'] = 'Passwort ist falsch';
$string['attemptstate'] = 'Zustand des Versuchs';
$string['attemptstopcriteria'] = 'Grund für den Abbruch des Versuchs';
$string['questionsattempted'] = 'Summe der versuchten Fragen';
$string['attemptfinishedtimestamp'] = 'Zeitpunkt des Versuchsendes';
$string['reviewattempt'] = 'Versuch überprüfen';
$string['indvuserreport'] = 'Bericht über Einzelversuche des Benutzers {$a}';
$string['activityreports'] = 'Bericht über Versuche';
$string['stopingconditionshdr'] = 'Abbruchbedingungen';
$string['reviewattemptreport'] = 'Überprüfung des Versuchs von {$a->fullname}, eingereicht am {$a->finished}';
$string['deleteattemp'] = 'Versuch löschen';
$string['confirmdeleteattempt'] = 'Bestätigung der Löschung des Versuchs von {$a->name}, eingereicht am {$a->timecompleted}';
$string['attemptdeleted'] = 'Versuch für {$a->name}, eingereicht am {$a->timecompleted}, gelöscht';
$string['closeattempt'] = 'Versuch schließen';
$string['confirmcloseattempt'] = 'Sind Sie sicher, dass Sie diesen Versuch von {$a->name} abschließen und beenden möchten?';
$string['confirmcloseattemptstats'] = 'Dieser Versuch wurde am {$a->started} gestartet und zuletzt am {$a->modified} aktualisiert.';
$string['confirmcloseattemptscore'] = '{$a->num_questions} Fragen wurden beantwortet und die bisherige Punktzahl beträgt {$a->measure} {$a->standarderror}.';
$string['attemptclosedstatus'] = 'Manuell geschlossen von {$a->current_user_name} (Benutzer-ID: {$a->current_user_id}) am {$a->now}.';
$string['attemptclosed'] = 'Der Versuch wurde manuell geschlossen.';
$string['errorclosingattempt_alreadycomplete'] = 'Dieser Versuch ist bereits abgeschlossen und kann nicht manuell geschlossen werden.';
$string['formstderror'] = 'Es muss eine Zahl kleiner als 1 und größer oder gleich 0 eingegeben werden';
$string['score'] = 'Punktzahl';
$string['theta'] = 'Theta';
$string['bestscore'] = 'Beste Punktzahl';
$string['bestscorestderror'] = 'Standardfehler';
$string['attempt_questiondetails'] = 'Fragendetails';
$string['attemptstarttime'] = 'Startzeit des Versuchs';
$string['attempttotaltime'] = 'Gesamtzeit (hh:mm:ss)';
$string['attempt_user'] = 'Benutzer';
$string['attempt_state'] = 'Versuchszustand';
$string['attemptquestion_num'] = '#';
$string['attemptquestion_level'] = 'Fragenlevel';
$string['attemptquestion_rightwrong'] = 'Punkte';
$string['attemptquestion_error'] = 'Standardfehler';
$string['attemptquestion_difficulty'] = 'Fragenschwierigkeit (logits)';
$string['attemptquestion_diffsum'] = 'Schwierigkeitssumme';
$string['attemptquestion_abilitylogits'] = 'Gemessene Fähigkeit (logits)';
$string['attemptquestion_stderr'] = 'Standardfehler';
$string['graphlegend_target'] = 'Ziellevel';
$string['graphlegend_error'] = 'Standardfehler';
$string['answerdistgraph_title'] = 'Antwortverteilung für {$a->firstname} {$a->lastname}';
$string['answerdistgraph_questiondifficulty'] = 'Fragenlevel';
$string['answerdistgraph_numrightwrong'] = 'Anz. falsch (-)  /  Anz. richtig (+)';
$string['answerdistgraph_right'] = 'Richtig';
$string['answerdistgraph_wrong'] = 'Falsch';
$string['numright'] = 'Anz. richtig';
$string['numwrong'] = 'Anz. falsch';
$string['questionnumber'] = 'Frage #';
$string['na'] = 'n/v';
$string['downloadcsv'] = 'CSV herunterladen';
$string['grademethod'] = 'Bewertungsmethode';
$string['gradehighest'] = 'Höchste Note';
$string['attemptfirst'] = 'Erster Versuch';
$string['attemptlast'] = 'Letzter Versuch';
$string['grademethod_help'] = 'Wenn mehrere Versuche erlaubt sind, stehen die folgenden Methoden zur Berechnung der endgültigen Quiznote zur Verfügung:

* Höchste Note aller Versuche
* Erster Versuch (alle anderen Versuche werden ignoriert)
* Letzter Versuch (alle anderen Versuche werden ignoriert)';
$string['resetadaptivequizsall'] = 'Alle adaptiven Quizversuche löschen';
$string['all_attempts_deleted'] = 'Alle adaptiven Quizversuche wurden gelöscht';
$string['all_grades_removed'] = 'Alle Noten des adaptiven Quiz wurden entfernt';
$string['questionanalysisbtn'] = 'Fragenanalyse';
$string['id'] = 'ID';
$string['name'] = 'Name';
$string['questions_report'] = 'Fragenbericht';
$string['question_report'] = 'Fragenanalyse';
$string['times_used_display_name'] = 'Häufigkeit der Nutzung';
$string['percent_correct_display_name'] = '% Richtig';
$string['discrimination_display_name'] = 'Diskriminierung';
$string['back_to_all_questions'] = '&laquo; Zurück zu allen Fragen';
$string['answers_display_name'] = 'Antworten';
$string['answer'] = 'Antwort';
$string['statistic'] = 'Statistik';
$string['value'] = 'Wert';
$string['allanswers'] = 'Antworten aller Benutzer';
$string['highlevelusers'] = 'Benutzer über dem Fragenlevel';
$string['midlevelusers'] = 'Benutzer nahe dem Fragenlevel';
$string['lowlevelusers'] = 'Benutzer unter dem Fragenlevel';
$string['user'] = 'Benutzer';
$string['result'] = 'Ergebnis';
$string['testSettings'] = 'Testeinstellungen';
$string['REC'] = 'Randomesque Exposure Control';
