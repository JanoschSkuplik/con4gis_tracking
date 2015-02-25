<?php

/**
 * Contao Open Source CMS
 *
 * @version   php 5
 * @package   con4gis_tracking
 * @author    Janosch Oltmanns
 * @license   GNU/LGPL http://opensource.org/licenses/lgpl-3.0.html
 * @copyright Janosch Oltmanns in cooperation with Küstenschmiede GmbH Software & Design 2014 - 2015
 * @link      http://janosch-oltmanns.de https://www.kuestenschmiede.de
 */


$GLOBALS['TL_LANG']['tl_c4g_maps']['tDontShowIfEmpty'] = array('Ausblenden wenn keine Einträge vorhanden', 'Blendet die Ebene im Starboard aus, wenn sie keine Einträge enthält.');
$GLOBALS['TL_LANG']['tl_c4g_maps']['memberVisibility'] = array('Angezeigte Daten', 'Legt fest wessen Daten auf dieser Ebene dargestellt werden.');
$GLOBALS['TL_LANG']['tl_c4g_maps']['specialMembers'] = array('Ausgewählte Mitglieder', 'Mitglieder dessen Trackingdaten auf dieser Ebene dargestellt werden sollen.');
$GLOBALS['TL_LANG']['tl_c4g_maps']['specialGroups'] = array('Ausgewählte Gruppen', 'Gruppen dessen Mitglieder-Trackingdaten auf dieser Ebene dargestellt werden sollen.');
$GLOBALS['TL_LANG']['tl_c4g_maps']['useDatabaseStatus'] = array('Benutzerdefinierte Sichtbarkeit überschreiben', 'Überschreibt die vom Mitglied eingestellte Sichtbarkeiten seiner Trackingdaten. (Nicht empfohlen!)');
$GLOBALS['TL_LANG']['tl_c4g_maps']['databaseStatus'] = array('Mit folgendem Wert überschreiben', 'Der Wert mit dem die benutzerdefinierten Sichtbarkeiten überschrieben werden sollen.');


$GLOBALS['TL_LANG']['tl_c4g_maps']['references']['tPois'] = 'Tracking – POIs';
$GLOBALS['TL_LANG']['tl_c4g_maps']['references']['tTracks'] = 'Tracking – Tracks';
$GLOBALS['TL_LANG']['tl_c4g_maps']['references']['tLive'] = 'Tracking – Live-Ansicht';

$GLOBALS['TL_LANG']['tl_c4g_maps']['references']['memberVisibility'] = array
(
  'own' => 'Eigene Einträge',
  'ownGroups' => 'Mitglieder der Gruppen des aktuellen Mitglieds',
  'specialGroups' => 'Mitglieder ausgewählter Gruppen',
  'specialMember' => 'Ausgewählte Mitglieder',
  'all' => 'Aller Mitglieder'
);

$GLOBALS['TL_LANG']['tl_c4g_maps']['references']['databaseStatus'] = array
(
  'privat' => 'Privat',
  'membergroups' => 'Bestimmte Mitgliedergruppen',
  'owngroups' => 'Mitglieder der Gruppen des Mitglieds',
  'public' => 'Jeder'
);