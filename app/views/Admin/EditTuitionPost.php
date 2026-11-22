<?php
$ViewData['Title'] = 'Edit Tuition Post - Admin Panel';
partial('Admin/_TuitionQuickForm', ['Model' => $Model, 'isEdit' => true, 'ViewBag' => $ViewBag]);
