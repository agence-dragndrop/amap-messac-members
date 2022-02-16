<?php

namespace App\Controller\User;

use App\Entity\Order;
use App\Form\OrderType;
use App\Repository\OrderDetailRepository;
use App\Repository\OrderRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/mes-commandes", name="order_")
 */
class OrderController extends AbstractController
{
    /**
     * @Route("/", name="index")
     */
    public function index(OrderDetailRepository $detailRepository, OrderRepository $orderRepository): Response
    {
        $orders = $orderRepository->findUserOrders($this->getUser());
        foreach ($orders as $order) {
            $order->details = $detailRepository->findOrderDetailByMember($this->getUser(), $order);
        }
        return $this->render('order/index.html.twig', [
            'orders' => $orders
        ]);
    }

    /**
     * @Route("/edit/{id}", name="edit")
     */
    public function edit(Order $order, Request $request, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(OrderType::class, $order);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash("success", "La commande a bien été mise à jour");
        }

        return $this->renderForm("order/edit.html.twig", [
            'form' => $form,
            'order' => $order
        ]);
    }

    /**
     * @param Request $request
     * @Route("/nouvelle-commande", name="new")
     */
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $order = new Order();
        $form = $this->createForm(OrderType::class, $order);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($order);
            $entityManager->flush();
            return $this->redirectToRoute('order_index');
        }

        return $this->renderForm('order/new.html.twig', [
            'form' => $form
        ]);
    }
}
