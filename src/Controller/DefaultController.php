<?php

  namespace App\Controller;

  use App\Entity\User;
  use Symfony\Bundle\FrameworkBundle\Controller\Controller;
  use Symfony\Component\HttpFoundation\Response;

  class DefaultController extends Controller
  {

    public function index()
    {
      return new Response("Hello Symfony");
    }

    public function user()
    {
      /** @var $user User */
      $user = $this->getUser();
      return new Response("Hello " . $user->getEmail());
    }
  }
