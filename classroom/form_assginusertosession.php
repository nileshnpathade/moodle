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

require_once('../config.php');

global $CFG, $USER, $DB, $PAGE, $COURSE;
$context = context_system::instance();
$editmenumode = optional_param('editmenumode', 0, PARAM_TEXT);
$page = optional_param('page', 0, PARAM_INT);
$perpage = optional_param('perpage', 10, PARAM_INT);
require_login();
$out = '';
$out .= html_writer::empty_tag('input', array('type' => 'text',
    'class' => 'form-control', 'name' => 'search',
    'id' => 'search', 'placeholder' => 'Search',
    'style' => 'max-width:20%;float:right'));

echo $out."<br/><br/>";
echo "<style> td.cell.c2.lastcol{ padding-left:5px; } </style>";
$start = $page * $perpage;
$results1 = $DB->get_records_sql("SELECT * FROM {classroom_session} WHERE isdeleted != 0
    AND courseid = $COURSE->id ", array());
$results = $DB->get_records_sql("SELECT * FROM {classroom_session} WHERE isdeleted != 0
    AND courseid = $COURSE->id LIMIT $start,$perpage", array());

$table = new html_table();
    $table->id = 'myTable';
    $table->head = array('Session Name', 'Assign Users', 'Actions');

    $i = 1;
    $j = 0;
foreach ($results as $re) {
    $cid = $re->id;
    $session = $re->session;

    $getuserdetails = $DB->get_records_sql('select ca.id AS caid, u.username, u.id
        From {user} u INNER JOIN {classroom_assignuser} ca
        ON u.id = ca.userid where ca.session_id = ?', array($cid));

    $count = "<span class = 'tag tag-info' style = 'padding:5px;' title = 'No Users assign'>
    &nbsp;&nbsp; 0 &nbsp;&nbsp;</span>";
    if (!empty($getuserdetails)) {
        foreach ($getuserdetails as $key => $value) {
                $url = "../course/format/classroom/adduserforsession.php?id=$value->caid&seesionid=$cid&courseid=$COURSE->id";
                $count = "<span class = 'tag tag-info' style = 'padding:5px;'>
                <a href = ".$url." style='color:#FFF;text-decoration:none;'>&nbsp;&nbsp; ".
                    count($getuserdetails).
                    "&nbsp; &nbsp;</a></span>";
        }
    }
    $lurl = '../course/view.php?id='.$COURSE->id.'&editmenumode=true&menuaction=attendance&token=1&sess_id='.$cid;

    $link = '';
    if ($re->session_date >= time()) {
        $icon = '<i class="icon fa fa-plus fa-fw "></i>';
        $icondelete = '<i class="icon fa fa-trash fa-fw "></i>';
        $deleteurl = '../course/format/classroom/delete_assigneduser.php?session_id='.$cid.'&token=1&courseid='.$COURSE->id;
        $assignurl = '../course/format/classroom/adduserforsession.php?seesionid='.$cid.'&courseid='.$COURSE->id;
        $title = 'title="Assign User"';
        $titledelete = 'title="Unassign all User"';
        $link .= '<a href="'.$assignurl.'" '.$title.'>'.$icon.'</a>';
        $link .= '<a href="'.$deleteurl.'" '.$titledelete.'>'.$icondelete.'</a>';
        $link .= '<a href='.$lurl.' title="Attendance"><i class="icon fa fa-address-card"></i></a>';
    } else {
        $link .= '<a href='.$lurl.' title="Attendance"><i class="icon fa fa-address-card"></i></a>';
        $link .= "<span class='tag tag-info' style='background-color:gray;padding:5px;' title='No Users assign'>Not active</span>";
    }

    $table->data[] = array($session, $count, $link);
    $i++;
    $j++;
}

echo html_writer::table($table);

if ($j == 0) {
    echo "<b style='color:#3A3D3E'>".get_string('nodatatodisplay', 'format_classroom')."</b><br>";
}
$burl = 'course/view.php?id='.$COURSE->id.'&editmenumode=true&menuaction=assginusertosession&token=1';
$baseurl = new moodle_url($CFG->wwwroot.'/'.$burl, array('sort' => 'location', 'dir' => 'ASC', 'perpage' => $perpage));
echo $OUTPUT->paging_bar(count($results1), $page, $perpage, $baseurl);