<?php

namespace App\Service;

class FrontNavigation
{
    public const NAVIGATION = [
        ["title" => "Accueil", "path" => "home", "granted" => ""],
        ["title" => "Les commandes", "path" => "order_index", "granted" => "ROLE_USER_VERIFIED"],
        ["title" => "Connexion", "path" => "login", "granted" => ""],
        ["title" => "S'inscrire", "path" => "app_register", "granted" => ""],
        ["title" => "Mon compte", "path" => "user_account", "granted" => ""],
        ["title" => "Déconnexion", "path" => "app_logout", "granted" => ""],
    ];

    public function links()
    {
        return self::NAVIGATION;
    }

    public function link(string $path)
    {
        return call_user_func_array('array_merge',
            array_filter(self::NAVIGATION,
                fn($x) => $x['path'] === $path
            ));
    }
}
