<?php
/**
 * Created by PhpStorm.
 * User: hitoTright
 * Date: 2017/11/1
 * Time: 15:13
 */
namespace app\common;

use PhpRbac\Rbac;
use \Jf;
class Permission extends Rbac {
    public function __construct($unit_test = '')
    {
        $host=config('database.hostname');
        $user=config('database.username');
        $pass=config('database.password');
        $dbname=config('database.database');
        $adapter="pdo_mysql";
        $tablePrefix = config('database.prefix').'org_';
        require_once VENDOR_PATH.'owasp/phprbac/PhpRbac/src/PhpRbac/core/lib/Jf.php';
        $this->Permissions = Jf::$Rbac->Permissions;
        $this->Roles = Jf::$Rbac->Roles;
        $this->Users = Jf::$Rbac->Users;
    }
}