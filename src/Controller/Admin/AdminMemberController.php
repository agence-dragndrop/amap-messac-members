<?php

namespace App\Controller\Admin;

use App\Entity\Member;
use App\Form\MemberType;
use App\Repository\MemberRepository;
use App\Service\AdminMember;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/member", name="member_")
 */
class AdminMemberController extends AbstractController
{
    private MemberRepository $memberRepository;

    /**
     * AdminMemberController constructor.
     * @param MemberRepository $memberRepository
     */
    public function __construct(MemberRepository $memberRepository)
    {
        $this->memberRepository = $memberRepository;
    }

    /**
     * @Route("/", name="index", methods={"GET", "POST"})
     */
    public function index(
        Request $request,
        AdminMember $adminMember,
        PaginatorInterface $paginator,
        MemberRepository $memberRepository
    ): Response
    {
        $form = $this->createFormBuilder()
            ->add('file', FileType::class, [
                'label' => 'Fichier',
                'help' => 'Sélectionner un fichier au format .vcard sur votre ordinateur.'
            ])
            ->getForm()
        ;
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $adminMember->import($form->get('file')->getData());
        }

        $pagination = $paginator->paginate(
            $memberRepository->queryFind(), /* query NOT result */
            $request->query->getInt('page', 1), /*page number*/
            25 /*limit per page*/
        );

        return $this->renderForm('admin/admin_member/index.html.twig', [
            'pagination' => $pagination,
            'form' => $form
        ]);
    }

    /**
     * @Route("/new", name="new", methods={"GET", "POST"})
     */
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $member = new Member();
        $form = $this->createForm(MemberType::class, $member);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($member);
            $entityManager->flush();

            return $this->redirectToRoute('admin_member_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('admin/admin_member/new.html.twig', [
            'member' => $member,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id<\d+>}", name="show", methods={"GET"})
     */
    public function show(Member $member): Response
    {
        return $this->render('admin/admin_member/show.html.twig', [
            'member' => $member,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="edit", methods={"GET", "POST"})
     */
    public function edit(
        Request $request,
        Member $member,
        EntityManagerInterface $entityManager
    ): Response {
        $form = $this->createForm(MemberType::class, $member);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Modifications enregistrées.');
            return $this->redirectToRoute('admin_member_edit', ['id' => $member->getId()], Response::HTTP_SEE_OTHER);
        }
        return $this->renderForm('admin/admin_member/edit.html.twig', [
            'member' => $member,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="delete", methods={"POST"})
     */
    public function delete(Request $request, Member $member, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$member->getId(), $request->request->get('_token'))) {
            $entityManager->remove($member);
            $entityManager->flush();
        }

        return $this->redirectToRoute('admin_member_index', [], Response::HTTP_SEE_OTHER);
    }

    /**
     * @param int $id
     * @return Response
     * @Route("/search", name="search")
     */
    public function widgetListMembers(Request $request): Response
    {

        return $this->render('admin/admin_member/_list_members.html.twig', [
            'members' => $this->memberRepository->searchMember($request->get('search')),
            'order_detail_id' => $request->get('orderDetailId')
        ]);
    }
}
