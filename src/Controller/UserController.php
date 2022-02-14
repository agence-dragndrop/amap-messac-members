<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/mon-compte", name="user_")
 */
class UserController extends AbstractController
{
    /**
     * @Route("/", name="account")
     */
    public function account(): Response
    {
        return $this->render('user/account.html.twig');
    }
}
