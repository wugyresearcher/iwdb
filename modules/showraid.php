<?php
/*****************************************************************************
 * showraid.php                                                              *
 *****************************************************************************
 * Iw DB: Icewars geoscan and sitter database                                *
 * Open-Source Project started by Robert Riess (robert@riess.net)            *
 * ========================================================================= *
 * Copyright (c) 2004 Robert Riess - All Rights Reserved                     *
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
 *                                                                           *
 * Entwicklerforum/Repo:                                                     *
 *                                                                           *
 *        https://handels-gilde.org/?www/forum/index.php;board=1099.0        *
 *                   https://github.com/iwdb/iwdb                            *
 *                                                                           *
 *****************************************************************************/

//direktes Aufrufen verhindern
if (!defined('IRA')) {
    header('HTTP/1.1 403 forbidden');
    exit;
}

//****************************************************************************

if (empty($coords)) {
    $coords = getVar('coords');
}

doc_title('Raids von ' . $coords);

if (!empty($coords)) {
    $sql = "SELECT * FROM " . $db_tb_raid . " WHERE coords='" . $coords . "' ORDER BY date DESC";
    $result = $db->db_query($sql)
        or error(GENERAL_ERROR, 'Could not query config information.', '', __FILE__, __LINE__, $sql);
    while ($row = $db->db_fetch_array($result)) {
        ?>
        <table class="table_format" style="width: 80%;">
            <tr>
                <td class="windowbg2">
                    Datum:
                </td>
                <td class="windowbg1">
                    <?php echo strftime(CONFIG_DATETIMEFORMAT, $row['date']);?>
                </td>
            </tr>
            <tr>
                <td colspan="2" class="windowbg1">
                    <?php echo nl2br($row['bericht']);?>
                </td>
            </tr>
        </table>
        <br>
    <?php
    }
}
?>
