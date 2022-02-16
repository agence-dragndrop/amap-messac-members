<?php

namespace App\Controller\User;

use App\Entity\Order;
use App\Repository\OrderDetailRepository;
use App\Repository\OrderRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\CodePointString;
use Symfony\Component\String\Slugger\AsciiSlugger;

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
     * @param Order $order
     * @param OrderDetailRepository $detailRepository
     * @return Response
     * @Route("/download-csv/{id}.csv", name="download_csv")
     */
    public function downloadCsv(Order $order, OrderDetailRepository $detailRepository): Response
    {
        $order->details = $detailRepository->findOrderDetailByMember($this->getUser(), $order);
        $csv = $this->renderView("order/csv.csv.twig", [
            'order' => $order
        ]);
        $fileName = strtolower($order->getName()) . ".csv";
        $response = new Response($csv);
        $response->headers->set('Content-Type', 'text/csv; charset=utf-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $fileName . '"');
        return $response;
    }
}
