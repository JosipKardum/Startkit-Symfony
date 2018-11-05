<?php

  namespace App\Controller;

  use App\Entity\User;
  use Symfony\Bundle\FrameworkBundle\Controller\Controller;
  use Symfony\Component\HttpFoundation\Response;
  use Symfony\Component\Routing\Annotation\Route;

  class DefaultController extends Controller
  {

    /**
     * @Route("/", name="app_homepage")
     */
    public function index()
    {
      return new Response("Hello Symfony");
    }

    /**
     * @Route("/user", name="app_user")
     */
    public function user()
    {
      /** @var $user User */
      $user = $this->getUser();
      return new Response("Hello " . $user->getEmail());
    }
  }
