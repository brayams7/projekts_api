<?php

// app/Constants/AppConstants.php

namespace App\Constants;

class Constants
{
    const NAME_STORAGE = 'public';

    const NAME_STORAGE_CLOUD = 's3';
    const NAME_DIRECTORY = 'attachments/';

    const NAME_THEMES_BOARD = 'themes_board/';

    const BASE_DIRECTORY = 'storage/';

    const NAME_DIRECTORY_PROFILE= 'profile_pictures/';

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
