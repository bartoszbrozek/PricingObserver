<?php

namespace App\Controller;

use App\Entity\Observer;
use App\Form\ObserverType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

class HomeController extends AbstractController
{
    public function index()
    {
        $observers = $this->getDoctrine()->getRepository(Observer::class)->findAll();

        return $this->render('home/index.html.twig', [
            'observers' => $observers
        ]);
    }

    public function add(Request $request)
    {
        $observer = new Observer();

        $form = $this->createForm(ObserverType::class, $observer);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($observer);
            $em->flush();
            $this->addFlash('success', 'Observer has been created');

            return $this->redirectToRoute('index');
        }

        return $this->render('home/add.html.twig', [
            'form' => $form->createView()
        ]);
    }

    public function edit(Request $request, Observer $observer)
    {
        $form = $this->createForm(ObserverType::class, $observer);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($observer);
            $em->flush();
            $this->addFlash('success', 'Observer has been saved');
            return $this->redirectToRoute('index');
        }

        return $this->render('home/add.html.twig', [
            'form' => $form->createView()
        ]);
    }

    public function show(int $id)
    {
        $observer = $this->getDoctrine()->getRepository(Observer::class)->findOneBy(['id' => $id]);

        return $this->render('home/show.html.twig', [
            'observer' => $observer
        ]);
    }

    public function remove(Observer $observer)
    {
        $em = $this->getDoctrine()->getManager();
        $em->remove($observer);
        $em->flush();
        $this->addFlash('success', 'Observer has been removed');
        return $this->redirectToRoute('index');
    }
}
