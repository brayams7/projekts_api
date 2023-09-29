<?php

// app/Constants/AppConstants.php

namespace App\Constants;

class Constants
{
    const DEFAULT_COLOR = '#0079bf';
    const NAME_STORAGE = 'public';
    // Define más constantes según tus necesidades

    //ROLES TYPES
    const ROLE_TYPE_MEMBER = 'MIEMBRO';
    const ROLE_TYPE_ADMIN = 'ADMINISTRADOR';

    //LIST PERMISSIONS

    const LIST_PERMISSIONS = [
       'workspace'=>'workspace',
        'board'=>'board',
        'tasks'=>'tasks',
        'dashboard'=>'dashboard',
        'members'=>'members',
        'settings'=>'settings',
        'views'=>'views'
    ];

    const BASE_APP_FE = 'http://localhost:5173/';
}
