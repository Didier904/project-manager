<?php

namespace App\Controller;

use App\Repository\ClientRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DashboardController extends AbstractController
{
    #[Route('/dashboard', name: 'app_dashboard')]
    public function index(ClientRepository $clientRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        // Tous les utilisateurs voient les mêmes clients
        $clients = $clientRepository->findAllOrdered();

        // KPI
        $totalClients = count($clients);
        $now = new \DateTime();
        $currentMonth = $now->format('m');

        $activeClientsThisMonth = count(array_filter(
            $clients,
            fn($c) => $c->getCreatedAt() && $c->getCreatedAt()->format('m') === $currentMonth
        ));

        // Top 5 récents
        usort($clients, fn($a, $b) => $b->getCreatedAt() <=> $a->getCreatedAt());
        $topClients = array_slice($clients, 0, 5);

        // Notifications du jour
        $today = $now->format('Y-m-d');
        $newClientsToday = array_filter(
            $clients,
            fn($c) => $c->getCreatedAt() && $c->getCreatedAt()->format('Y-m-d') === $today
        );

        // Graphiques
        $clientsEvolutionLabels = [];
        $clientsEvolutionData = [];

        for ($i = 5; $i >= 0; $i--) {
            $date = new \DateTime("-$i months");
            $label = $date->format('M');
            $month = $date->format('m');

            $clientsEvolutionLabels[] = $label;
            $clientsEvolutionData[] = count(array_filter(
                $clients,
                fn($c) => $c->getCreatedAt() && $c->getCreatedAt()->format('m') === $month
            ));
        }

        // Répartition société
        $societeCountsMap = [];
        foreach ($clients as $client) {
            $societe = $client->getSociete() ?? 'Non défini';
            $societeCountsMap[$societe] = ($societeCountsMap[$societe] ?? 0) + 1;
        }

        return $this->render('dashboard/dashboard.html.twig', [
            'clients' => $clients,
            'totalClients' => $totalClients,
            'activeClientsThisMonth' => $activeClientsThisMonth,
            'topClients' => $topClients,
            'newClientsToday' => $newClientsToday,
            'clientsEvolutionLabels' => $clientsEvolutionLabels,
            'clientsEvolutionData' => $clientsEvolutionData,
            'societeLabels' => array_keys($societeCountsMap),
            'societeCounts' => array_values($societeCountsMap),
            'revenus' => 0,
        ]);
    }
}
