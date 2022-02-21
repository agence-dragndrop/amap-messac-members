<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Repository\MemberRepository;
use App\Repository\UserRepository;
use App\Security\EmailVerifier;
use App\Security\SecurityAuthenticator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;

class RegistrationController extends AbstractController
{
    private EmailVerifier $emailVerifier;

    public function __construct(EmailVerifier $emailVerifier)
    {
        $this->emailVerifier = $emailVerifier;
    }

    /**
     * @Route("/register", name="app_register")
     */
    public function register(
        Request $request,
        UserPasswordHasherInterface $userPasswordHasher,
        UserAuthenticatorInterface $userAuthenticator,
        SecurityAuthenticator $authenticator,
        EntityManagerInterface $entityManager,
        MemberRepository $memberRepository
    ): Response {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            $member = $memberRepository->findOneByEmail($user->getEmail());
            if (null === $member) {
                $this->addFlash(
                    "danger",
                    "Nous n'avons pas trouvé de compte adhérent actif pour cette adresse e-mail"
                );
                return $this->redirectToRoute("app_register");
            }
            $user->setIsVerified(true);
            $user->setMember($member);
            $user->setPassword(
            $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            $entityManager->persist($user);
            $entityManager->flush();

            // generate a signed url and email it to the user
//            $this->sendEmailConfirmation($user);
            // do anything else you need here, like send an email

            return $userAuthenticator->authenticateUser(
                $user,
                $authenticator,
                $request
            );
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    /**
     * @Route("/verify/email", name="app_verify_email")
     */
    public function verifyUserEmail(Request $request, UserRepository $userRepository): Response
    {
        $id = $request->get('id');

        if (null === $id) {
            return $this->redirectToRoute('app_register');
        }

        $user = $userRepository->find($id);

        if (null === $user) {
            return $this->redirectToRoute('app_register');
        }

        // validate email confirmation link, sets User::isVerified=true and persists
        try {
            $this->emailVerifier->handleEmailConfirmation($request, $user);
        } catch (VerifyEmailExceptionInterface $exception) {
            $this->addFlash('verify_email_error', $exception->getReason());

            return $this->redirectToRoute('app_register');
        }

        // @TODO Change the redirect on success and handle or remove the flash message in your templates
        $this->addFlash('success', 'Votre adresse mail a bien été vérifiée, merci !');

        return $this->redirectToRoute('user_account');
    }

    /**
     * @Route("/resend-confirmation", name="resend_email_confirmation")
     */
    public function requestVerifyEmail(Request $request)
    {
        $this->sendEmailConfirmation($this->getUser());
        $this->addFlash(
            "success",
            "Un nouveau lien de vérification a été envoyé à l'adresse <strong>" . $this->getUser()->getUserIdentifier() . "</strong>."
        );
        return $this->redirect($request->headers->get('referer'));
    }

    private function sendEmailConfirmation(User $user)
    {
        $this->emailVerifier->sendEmailConfirmation('app_verify_email', $user,
            (new TemplatedEmail())
                ->from(new Address($this->getParameter('mailer_from'), $this->getParameter('mailer_from_name')))
                ->to($user->getEmail())
                ->subject('Confirmez votre e-mail')
                ->htmlTemplate('registration/confirmation_email.html.twig')
        );
    }
}
