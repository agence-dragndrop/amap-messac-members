<?php


namespace App\Controller\Admin;


use App\Entity\Member;
use App\Entity\Order;
use App\Entity\OrderDetail;
use App\Form\OrderType;
use App\Repository\OrderRepository;
use App\Service\AdminOrder;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\CsvEncoder;

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
    public function index(OrderRepository $orderRepository): Response
    {
        return $this->render('admin/admin_order/index.html.twig', [
            'orders' => $orderRepository->findAll(),
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
            return $this->redirectToRoute('admin_order_edit', ['id' => $order->getId()]);
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
        AdminOrder $adminOrder
    ): Response {
        $adminOrder->mapMember($order);
        return $this->render("admin/admin_order/map_to_member.html.twig", [
            'order' => $order
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
     * @Route("/{id}", name="delete", methods={"POST"})
     */
    public function delete(Request $request, Order $order, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$order->getId(), $request->request->get('_token'))) {
            $entityManager->remove($order);
            $entityManager->flush();
        }

        return $this->redirectToRoute('admin_order_index', [], Response::HTTP_SEE_OTHER);
    }
}
