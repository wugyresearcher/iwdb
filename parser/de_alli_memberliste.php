<?php
/*****************************************************************************
 * de_alli_memberliste.php                                                   *
 *****************************************************************************
 * This program is free software; you can redistribute it and/or modify it   *
 * under the terms of the GNU General Public License as published by the     *
 * Free Software Foundation; either version 2 of the License, or (at your    *
 * option) any later version.                                                *
 *                                                                           *
 * This program is distributed in the hope that it will be useful, but       *
 * WITHOUT ANY WARRANTY; without even the implied warranty of                *
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General *
 * Public License for more details.                                          *
 *                                                                           *
 * The GNU GPL can be found in LICENSE in this directory                     *
 *****************************************************************************
 * Diese Erweiterung der ursprünglichen DB ist ein Gemeinschaftsprojekt von  *
 * IW-Spielern.                                                              *
 *                                                                           *
 * Autor: Mac (MacXY@herr-der-mails.de)                                      *
 * Datum: April 2012                                                         *
 *                                                                           *
 * Bei Problemen kannst du dich an das eigens dafür eingerichtete            *
 * Entwicklerforum wenden:                                                   *
 *                   https://www.handels-gilde.org                           *
 *                   https://github.com/iwdb/iwdb                            *
 *                                                                           *
 *****************************************************************************/

//direktes Aufrufen verhindern
if (!defined('IRA')) {
    header('HTTP/1.1 403 forbidden');
    exit;
}

if (!defined('DEBUG_LEVEL')) {
    define('DEBUG_LEVEL', 0);
}

function parse_de_alli_memberliste($aParserData)
{
    global $user_allianz;
    global $db, $db_tb_user;

    if (empty($user_allianz)) {
        echo "<div class='system_warning'>User-Allianz nicht festgelegt</div>";

        return;
    }

    echo "<div class='system_notification'>Member werden folgender Allianz zugeordnet: [" . $user_allianz . "]</div>";

    //! bisherige Member der Allianz suchen
    $oldMember = array();
    $sql       = "SELECT `sitterlogin` FROM `{$db_tb_user}` WHERE allianz = '" . $user_allianz . "'";
    $sqlres = $db->db_query($sql);
    while ($row = $db->db_fetch_array($sqlres)) {
        array_push($oldMember, $row["sitterlogin"]);
    }

    $bDateOfEntryVisible = $aParserData->objResultData->bDateOfEntryVisible;
    $bUserTitleVisible   = $aParserData->objResultData->bUserTitleVisible;

    $aktMember = array();
    foreach ($aParserData->objResultData->aMembers as $object_user) {
        $scan_udata = array();
        array_push($aktMember, $object_user->strName);

        $scan_udata['sitterlogin'] = $object_user->strName;
        $scan_udata['rang']        = $object_user->eRank;
        $scan_udata['gebp']        = $object_user->iGebP;
        $scan_udata['fp']          = $object_user->iFP;
        $scan_udata['allianz']     = $user_allianz;
        $scan_udata['gesamtp']     = $object_user->iGesamtP;
        $scan_udata['ptag']        = $object_user->iPperDay;

        if ($bDateOfEntryVisible) {
            $scan_udata['dabei'] = $object_user->iDabeiSeit;
        }

        if ($bUserTitleVisible) {
            $scan_udata['titel'] = $object_user->strTitel;
        }

        // Dann noch die gewonnenen Daten in die DB eintragen.
        updateuser($scan_udata);
    }

    //! Mac: in der original Version wurden nie Spieler entfernt ?
    foreach ($oldMember as $formerUser) {
        if (!in_array($formerUser, $aktMember, true)) {
            echo "Mac: todo: " . $formerUser . " entfernen<br />";
        }
    }
}

function updateuser($scan_data)
{
    global $db, $db_tb_user, $db_tb_punktelog;

    // Daten ins Punktelog übernehmen.
    $aSqlData = array (
        'user' => $scan_data['sitterlogin'],
        'date' => CURRENT_UNIX_TIME,
        'gebp' => $scan_data['gebp'],
        'fp'    => $scan_data['fp'],
        'gesamtp' => $scan_data['gesamtp'],
        'ptag' => $scan_data['ptag']
    );
    $db->db_insertignore($db_tb_punktelog, $aSqlData);

    // Prüfe Mitglied, ob es bereits in der DB gespeichert ist.
    $sql = "SELECT sitterlogin FROM " . $db_tb_user . " WHERE sitterlogin='" . $scan_data['sitterlogin'] . "'";
    $result = $db->db_query($sql);
    $row = $db->db_fetch_array($result);

    if (!empty($row['sitterlogin'])) {
        // Das Mitglied existiert bereits. Daten in Tabelle user aktualisieren.
        foreach ($scan_data as $key => $data) {
            $update = (empty($update)) ? $key . "='" . $data . "'" : $update . ", " . $key . "='" . $data . "'";
        }

        $sql = "UPDATE " . $db_tb_user . " SET " . $update . " WHERE sitterlogin='" . $scan_data['sitterlogin'] . "'";
        $db->db_query($sql);

    } else {
        // Das Mitglied existiert noch nicht, Daten in Tabelle user einfügen.
        echo "neues Mitglied: " . $scan_data["sitterlogin"] . "<br />";
        $scan_data['id'] = $scan_data['sitterlogin'];
        foreach ($scan_data as $key => $data) {
            $sql_key  = (empty($sql_key)) ? $key
                : $sql_key . ", " . $key;
            $sql_data = (empty($sql_data)) ? "'" . $data . "'"
                : $sql_data . ", '" . $data . "'";
        }
        $sql = "INSERT INTO " . $db_tb_user . " (" . $sql_key . ") VALUES (" . $sql_data . ")";
        $db->db_query($sql);

    }
}