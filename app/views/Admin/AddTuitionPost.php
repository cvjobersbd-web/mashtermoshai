<?php
$ViewData['Title'] = 'Add Tuition Post - Admin Panel';
partial('Admin/_TuitionQuickForm', ['Model' => $Model, 'isEdit' => false, 'ViewBag' => $ViewBag]);
