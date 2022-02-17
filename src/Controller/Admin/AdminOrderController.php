<?php


namespace App\Controller\Admin;


use App\Entity\Member;
use App\Entity\Order;
use App\Entity\OrderDetail;
use App\Form\OrderType;
use App\Repository\OrderRepository;
use App\Service\AdminOrder;
use App\Utility\AttachmentFile;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\CsvEncoder;
use Symfony\Component\Validator\Constraints\File;

/**
 * @Route("/commandes", name="order_")
 */
class AdminOrderController extends AbstractController
{
    private EntityManagerInterface $entityManager;

    /**
     * AdminOrderController constructor.
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @Route("/", name="index")
     */
    public function index(
        OrderRepository $orderRepository,
        Request $request,
        AttachmentFile $attachmentFile
    ): Response {
        $form = $this->createFormBuilder()
            ->add('file', FileType::class, [
                'label' => 'Fichier pdf',
                'help' => 'Sélectionner un fichier .pdf sur votre ordinateur.',
                'constraints' => [
                    new File([
                        'mimeTypes' => ["application/pdf", "application/x-pdf"],
                        'mimeTypesMessage' => "Fichier PDF uniquement"
                    ])
                ]
            ])
            ->add('submit', SubmitType::class, [
                'label' => '<i class="bi bi-upload"></i> télécharger',
                'label_html' => true,
                'attr' => ['class' => 'btn btn-success']
            ])
            ->getForm()
        ;
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $file = $form->get('file')->getData();
            if ($file->move($attachmentFile->getFilePath(), $attachmentFile->getFileName())) {
                $this->addFlash(
                    "success",
                    "Le fichier <strong>" . $file->getClientOriginalName() .
                    "</strong> a bien été téléchargé et renommé pour <strong>"  . $attachmentFile->getFileName() . "</strong>."
                );
            }
            return $this->redirectToRoute('admin_order_index');
        }
        return $this->renderForm('admin/admin_order/index.html.twig', [
            'orders' => $orderRepository->findAll(),
            'form' => $form,
            'pdf_file' => $attachmentFile
        ]);
    }

    /**
     * @Route("/edit/{id}", name="edit")
     */
    public function edit(Order $order, Request $request): Response
    {
        $form = $this->createForm(OrderType::class, $order);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $order->setUpdatedAt(new \DateTime());
            $this->entityManager->flush();
            $this->addFlash("success", "La commande a bien été mise à jour.");
            return $this->redirectToRoute('admin_order_edit', ['id' => $order->getId()]);
        }

        return $this->renderForm("admin/admin_order/edit.html.twig", [
            'form' => $form,
            'order' => $order
        ]);
    }

    /**
     * @param Request $request
     * @Route("/nouvelle-commande", name="new")
     */
    public function new(Request $request): Response
    {
        $order = new Order();
        $form = $this->createForm(OrderType::class, $order);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($order);
            $this->entityManager->flush();
            return $this->redirectToRoute('admin_order_map_to_member', ['id' => $order->getId()]);
        }

        return $this->renderForm('admin/admin_order/new.html.twig', [
            'form' => $form
        ]);
    }

    /**
     * @param Order $order
     * @Route("/map-to-member/{id}", name="map_to_member")
     */
    public function mapOrderToMember(
        Order $order,
        AdminOrder $adminOrder,
        Request $request
    ): Response {
        $form = $this->createFormBuilder()
            ->add('file', FileType::class, [
                'label' => 'Fichier csv',
                'help' => 'Sélectionner un fichier csv sur votre ordinateur puis valider pour déclencher 
                la synchronisation automatique des commandes avec les comptes adhérents.'
            ])
            ->getForm()
        ;
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $adminOrder->mapMember($order, $form->get('file')->getData());
            $order->setUpdatedAt(new \DateTime());
            $this->entityManager->flush();
            return $this->redirectToRoute('admin_order_edit', ['id' => $order->getId()]);
        }
        return $this->renderForm("admin/admin_order/map_to_member.html.twig", [
            'order' => $order,
            'form' => $form
        ]);
    }

    /**
     * @Route("/member-to-order-detail/{orderDetailId}/{memberId}/{action}", name="link_member")
     * @ParamConverter("member", options={"mapping": {"memberId": "id"}})
     * @ParamConverter("orderDetail", options={"mapping": {"orderDetailId": "id"}})
     */
    public function linkMemberToOrderDetail(Member $member, OrderDetail $orderDetail, string $action): Response
    {
        if ($action === 'link') {
            $orderDetail->setMember($member);
        } else {
            $member->removeOrderDetail($orderDetail);
        }
        $this->entityManager->flush();
        return $this->render('admin/admin_order/_widget_link_member.html.twig', [
            'order_detail' => $orderDetail
        ]);
    }

    /**
     * @Route("/delete/{id}", name="delete", methods={"POST"})
     */
    public function delete(Request $request, Order $order, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$order->getId(), $request->request->get('_token'))) {
            $entityManager->remove($order);
            $entityManager->flush();
        }

        return $this->redirectToRoute('admin_order_index', [], Response::HTTP_SEE_OTHER);
    }

    /**
     * @Route("/delete-links/{id}", name="delete_links", methods={"POST"})
     */
    public function deleteLinks(Request $request, Order $order, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$order->getId(), $request->request->get('_token'))) {
            foreach ($order->getOrderDetails() as $orderDetail) {
                $entityManager->remove($orderDetail);
            }
            $entityManager->flush();
        }

        return $this->redirectToRoute('admin_order_edit', ['id' => $order->getId()], Response::HTTP_SEE_OTHER);
    }

    /**
     * @Route("/remove-links/{id}", name="remove_links", methods={"POST"})
     */
    public function removeLinks(Request $request, Order $order, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$order->getId(), $request->request->get('_token'))) {
            foreach ($order->getOrderDetails() as $orderDetail) {
                $orderDetail->setMember(null);
            }
            $entityManager->flush();
        }

        return $this->redirectToRoute('admin_order_edit', ['id' => $order->getId()], Response::HTTP_SEE_OTHER);
    }

    /**
     * @Route("/remove-attachment", name="remove_attachment")
     */
    public function removeAttachment(AttachmentFile $attachmentFile)
    {
        if ($attachmentFile->unlink()) {
            $this->addFlash("success", 'Le fichier <strong>'. $attachmentFile->getFileName() . '</strong> a bien été retiré');
        }
        return $this->redirectToRoute("admin_order_index");
    }
}
