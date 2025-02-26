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
 *
 *
 * @package    local_sharecourse
 * @copyright  2024 Edunao SAS (contact@edunao.com)
 * @author     Pierre FACQ <pierre.facq@edunao.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'Compartir Curso';
$string['share'] = 'Compartir';
$string['sharecourse'] = 'Compartir Curso';
$string['copy_link'] = 'Copiar';
$string['copy_clipboard'] = 'Copiar al portapapeles';
$string['copied_clipboard'] = '¡Copiado!';
$string['share_facebook'] = 'Compartir en Facebook';
$string['share_whatsapp'] = 'Compartir en WhatsApp';
$string['share_linkedin'] = 'Compartir en LinkedIn';
$string['share_email'] = 'Compartir por correo electrónico';
$string['share_email_subject'] = '{$a->coursename}';
$string['share_email_body'] = 'Estás invitado a mi nuevo curso "{$a->coursename}"

Haz clic aquí para acceder: {$a->courseurl}';
$string['share_lti'] = 'Compartir vía LTI';

$string['sharecourse:sharecourse'] = "Permite a un usuario acceder a la interfaz de compartir curso";
$string['sharecourse:sharelti'] = "Permite a un usuario acceder a 'Compartir vía LTI' en la interfaz de compartir curso";
