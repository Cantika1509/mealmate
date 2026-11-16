<?php
requireRole('Admin');

render('admin/dashboard', [
    'title' => 'Dashboard Admin - MealMate',
], 'admin');
