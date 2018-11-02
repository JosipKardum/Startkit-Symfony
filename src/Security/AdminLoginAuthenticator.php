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
     * @var UserRepository
     */
    private $userRepository;

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
    }

    public function supports(Request $request): bool
    {
      if ($request->getPathInfo() != '/admin/login' || $request->getMethod() != 'POST') {
        return false;
      }

      return true;
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

    public function getUser($credentials, UserProviderInterface $userProvider): UserInterface
    {
      if(empty($this->entityManager->getRepository(User::class)->findByRole('ROLE_ADMIN'))) {
        $user = new User();
        $user->setEmail($credentials['email']);
        $password = $this->passwordEncoder->encodePassword($user, $credentials['password']);
        $user->setPassword($password);
        $user->setRoles(["ROLE_ADMIN"]);
        $this->entityManager->persist($user);
        $this->entityManager->flush();
        return $user;
      } else {
        return $userProvider->loadUserByUsername($credentials['email']);
      }
    }

    public function checkCredentials($credentials, UserInterface $user): bool
    {
      if (!$this->passwordEncoder->isPasswordValid($user, $credentials['password'])) {
        return false;
      }

      if (!$user->hasRole('ROLE_ADMIN')) {
        throw new CustomUserMessageAuthenticationException("You don't have permission to access that page.");
      }

      return true;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): RedirectResponse
    {
      $request->getSession()->set(Security::AUTHENTICATION_ERROR, $exception);

      return $this->getLoginUrl();
    }

    protected function getLoginUrl(): RedirectResponse
    {
      return new RedirectResponse($this->router->generate('admin_login'));
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey): RedirectResponse
    {
      return new RedirectResponse($this->router->generate('sonata_admin_dashboard'));
    }
  }
