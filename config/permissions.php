<?php

// Block 1 - Users, Roles and Permissions Management | Student name / number: ________

/*
|--------------------------------------------------------------------------
| Permission catalog
|--------------------------------------------------------------------------
| Every permission the application knows about, grouped by block.
| The "Roles & Permissions" page shows these as checkboxes; Gate / the
| CheckPermission middleware check them. Other blocks can add their own group.
*/

return [
    'groups' => [
        'Users Management' => [
            'users.view'   => 'Search users and view their details',
            'users.create' => 'Add new users',
            'users.edit'   => 'Edit user information',
            'users.status' => 'Activate / deactivate accounts',
            'users.delete' => 'Delete users',
            'roles.manage' => 'Manage roles and permissions',
        ],
    ],

    /*
    | System roles seeded by Block 1.
    | The spec's actors (system admin, student affairs, instructor, exam affairs, student)
    | plus the department manager / employee roles every block uses.
    */
    'roles' => [
        'admin'           => 'System Administrator - all permissions, manages users, the five blocks, roles and permissions',
        'student_affairs' => 'Student Affairs - manages students and their sections',
        'instructor'      => 'Instructor - lectures, attendance, grades and absence reports',
        'exam_affairs'    => 'Exam Affairs - exams, grades and results',
        'student'         => 'Student - views his lectures, attendance, grades and sections (view only)',
        'manager'         => 'Department Manager - add, edit, delete, change status, manage',
        'employee'        => 'Department Employee - add, edit, search, view details',
    ],

    // Default permissions given to each role by the seeder.
    'defaults' => [
        'admin'    => ['users.view', 'users.create', 'users.edit', 'users.status', 'users.delete', 'roles.manage'],
        'manager'  => ['users.view', 'users.create', 'users.edit', 'users.status', 'users.delete', 'roles.manage'],
        'employee' => ['users.view', 'users.create', 'users.edit'],
    ],

    // System roles cannot be renamed or deleted from the UI (the blocks check them by name).
    'protected_roles' => ['admin', 'student_affairs', 'instructor', 'exam_affairs', 'student', 'manager', 'employee'],
];
