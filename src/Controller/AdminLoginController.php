<?php

  namespace App\Controller;

  use Symfony\Bundle\FrameworkBundle\Controller\Controller;
  use App\Form\AdminLoginForm;
  use Symfony\Component\Routing\Annotation\Route;
  use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

  class AdminLoginController extends Controller
  {
    /**
     * @var AuthenticationUtils
     */
    private $authenticationUtils;

    public function __construct(AuthenticationUtils $authenticationUtils)
    {
      $this->authenticationUtils = $authenticationUtils;
    }

    /**
     * @Route("/user/login", name="admin_login")
     */
    public function login()
    {
      $user = $this->getUser();
      if($user) {
        if (in_array('ROLE_ADMIN', $user->getRoles())) {
          return $this->redirectToRoute('sonata_admin_dashboard');
        } else {
          return $this->redirectToRoute('app_user');
        }
      }
      $form = $this->createForm(AdminLoginForm::class, [
        'email' => $this->authenticationUtils->getLastUsername()
      ]);

      return $this->render('security/login.html.twig', [
        'last_username' => $this->authenticationUtils->getLastUsername(),
        'form' => $form->createView(),
        'error' => $this->authenticationUtils->getLastAuthenticationError(),
      ]);
    }

    /**
     * @Route("/user/logout", name="admin_logout")
     */
    public function logoutAction()
    {
      // Left empty intentionally because this will be handled by Symfony.
    }
  }
