<?php

declare(strict_types=1);

namespace App\Router;

use Nette;
use Nette\Application\Routers\RouteList;

final class RouterFactory
{
    use Nette\StaticClass;

    public static function createRouter(): RouteList
    {
        $router = new RouteList;

        // LOGIN/LOGOUT HANDLERS
        $router->addRoute('login/', 'Sign:in');
        $router->addRoute('logout/', 'Homepage:logout');

        // CRON
        $router->addRoute('cron/[<action>/]<hash>/', 'Cron:default');

        // FILES
        //$router->addRoute('files/', 'Files:default');
        $router->addRoute('files/[<path .+>/]', 'Files:directory');

        $router->addRoute('download/[<storageID>/<downloadID>]', 'Files:download');
        $router->addRoute('download-bulk/[<storageID_List>]', 'Files:downloadBulk');

        $router->addRoute('delete/[<storageID>]', 'Files:delete');
        $router->addRoute('delete-bulk/[<storageID_List>]', 'Files:deleteBulk');
        //$router->addRoute('user-folder/[<userid>]', 'Files:userfolder');
        $router->addRoute('add-folder/[<tree_id>/<name>]', 'Files:addFolder');
        $router->addRoute('delete-folder/[<tree_id>]', 'Files:deleteFolder');

        // UPLOAD
        $router->addRoute('upload/[<hash>/]', 'Upload:default');

        // PERMISSIONS
        $router->addRoute('permissions/', 'Permissions:default');
        //$router->addRoute('permissions[/<userid>]', 'Permissions:user');

        // SETTINGS
        $router->addRoute('settings/', 'Settings:default');
        //$router->addRoute('settings[/<userid>]', 'Settings:user');

        // DEFAULT
        $router->addRoute('<presenter>/<action>/[<id>]', 'Homepage:default');
        return $router;
    }
}
