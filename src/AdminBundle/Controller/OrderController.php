<?php

namespace AdminBundle\Controller;

use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class OrderController extends Controller implements AuthenticatedController
{
    /**
     * @Route("/admin/orders", name="admin.orders")
     */
    public function adminOrdersAction(Request $request, $page = 1)
    {
        $limit = 10;
        $page = $request->query->get('page', $page);/*page number*/

        $em = $this->getDoctrine()->getManager();

        $query = $em->createQuery("SELECT o FROM UserBundle:FormOrder o WHERE o.isNew = 1 ORDER BY o.dateCreate DESC");

        // $orders = $query->getResult();

        $orders = $this->paginate($query, $page, $limit);
        $maxPages = ceil($orders->count() / $limit);
        $thisPage = $page;

        $city = $this->get('session')->get('city');

        return $this->render('AdminBundle::admin_orders.html.twig', [
            'orders' => $orders,
            'city' => $city,
            'maxPages' => $maxPages,
            'thisPage' => $thisPage
        ]);
    }

    /**
     * Paginator Helper
     *
     * Pass through a query object, current page & limit
     * the offset is calculated from the page and limit
     * returns an `Paginator` instance, which you can call the following on:
     *
     *     $paginator->getIterator()->count() # Total fetched (ie: `5` posts)
     *     $paginator->count() # Count of ALL posts (ie: `20` posts)
     *     $paginator->getIterator() # ArrayIterator
     *
     * @param Doctrine\ORM\Query $dql   DQL Query Object
     * @param integer            $page  Current page (defaults to 1)
     * @param integer            $limit The total number per page (defaults to 5)
     *
     * @return \Doctrine\ORM\Tools\Pagination\Paginator
     */
    public function paginate($dql, $page = 1, $limit = 5)
    {
        $paginator = new Paginator($dql);

        $paginator->getQuery()
            ->setFirstResult($limit * ($page - 1)) // Offset
            ->setMaxResults($limit); // Limit

        return $paginator;
    }

}
