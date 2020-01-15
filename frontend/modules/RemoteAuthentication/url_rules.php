<?php
/**
 * Created by PhpStorm.
 * User: miloslawsky
 * Date: 04.03.19
 * Time: 15:25
 */

return [
    ['pattern' => 'connect/logout',                                                        'route' => 'RemoteAuthentication/auth/logout'],
    ['pattern' => 'connect/practices/<practice_umr_id:\w+>/<billing_id:\w+>',              'route' => 'RemoteAuthentication/auth/index'],
    ['pattern' => 'connect/practices/<practice_umr_id:\w+>/<billing_id:\w+>/terms-of-use', 'route' => 'RemoteAuthentication/auth/terms-of-use'],
];