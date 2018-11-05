<?php

  namespace App\Security;

  use App\Form\AdminLoginForm;
  use App\Entity\User;
  use App\Repository\UserRepository;
  use Doctrine\ORM\EntityManagerInterface;
  use Symfony\Component\Form\FormFactoryInterface;
  use Symfony\Component\HttpFoundation\RedirectResponse;
  use Symfony\Component\HttpFoundation\Request;
  use Symfony\Component\Routing\RouterInterface;
  use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
  use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
  use Symfony\Component\Security\Core\Exception\AuthenticationException;
  use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
  use Symfony\Component\Security\Core\Security;
  use Symfony\Component\Security\Core\User\UserInterface;
  use Symfony\Component\Security\Core\User\UserProviderInterface;
  use Symfony\Component\Security\Guard\Authenticator\AbstractFormLoginAuthenticator;
  use Symfony\Component\Security\Guard\AuthenticatorInterface;

  class AdminLoginAuthenticator extends AbstractFormLoginAuthenticator implements AuthenticatorInterface
  {
    /**
     * @var FormFactoryInterface
     */
    private $formFactory;

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var User
     */
    private $loggedUser;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var UserPasswordEncoderInterface
     */
    private $passwordEncoder;

    public function __construct(
      FormFactoryInterface $formFactory,
      RouterInterface $router,
      UserPasswordEncoderInterface $passwordEncoder,
      EntityManagerInterface $entityManager
    ) {
      $this->formFactory = $formFactory;
      $this->router = $router;
      $this->passwordEncoder = $passwordEncoder;
      $this->entityManager = $entityManager;
      $this->loggedUser = null;
    }

    public function supports(Request $request): bool
    {
      return 'admin_login' === $request->attributes->get('_route')
        && $request->isMethod('POST');
    }

    public function getCredentials(Request $request): array
    {
      $form = $this->formFactory->create(AdminLoginForm::class);
      $form->handleRequest($request);

      $data = $form->getData();
      $request->getSession()->set(
        Security::LAST_USERNAME,
        $data['email']
      );

      return $data;
    }

    public function getUser($credentials, UserProviderInterface $userProvider)
    {
      if(empty($this->entityManager->getRepository(User::class)->findByRole('ROLE_ADMIN'))) {
        $user = new User();
        $user->setEmail($credentials['email']);
        $password = $this->passwordEncoder->encodePassword($user, $credentials['password']);
        $user->setPassword($password);
        $user->setRoles(["SUPER_ADMIN_ROLE","ROLE_ADMIN", "ROLE_USER"]);
        $this->entityManager->persist($user);
        $this->entityManager->flush();
      } else {
        $user = $userProvider->loadUserByUsername($credentials['email']);
      }
      $this->loggedUser = $user;
      return $user;
    }

    public function checkCredentials($credentials, UserInterface $user)
    {
      if (!$this->passwordEncoder->isPasswordValid($user, $credentials['password'])) {
        $this->loggedUser = null;
        return false;
      }

      return true;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
      $request->getSession()->set(Security::AUTHENTICATION_ERROR, $exception);

      return $this->getLoginUrl();
    }

    protected function getLoginUrl(): RedirectResponse
    {
      return new RedirectResponse($this->router->generate('admin_login'));
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
      if (in_array('ROLE_ADMIN', $this->loggedUser->getRoles())) {
        return new RedirectResponse($this->router->generate('sonata_admin_dashboard'));
      } else {
        return new RedirectResponse($this->router->generate('app_user'));
      }
    }
  }
