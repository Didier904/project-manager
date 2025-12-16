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

        $user = $this->getUser();
        $clients = $clientRepository->findBy(['user' => $user]);

        // --- KPI ---
        $totalClients = count($clients);
        $now = new \DateTime();
        $currentMonth = $now->format('m');

        $activeClientsThisMonth = count(array_filter($clients, fn($c) => $c->getCreatedAt() && $c->getCreatedAt()->format('m') === $currentMonth));

        // --- Top 5 clients récents ---
        usort($clients, fn($a, $b) => $b->getCreatedAt() <=> $a->getCreatedAt());
        $topClients = array_slice($clients, 0, 5);

        // --- Clients ajoutés aujourd'hui ---
        $today = $now->format('Y-m-d');
        $newClientsToday = array_filter($clients, fn($c) => $c->getCreatedAt() && $c->getCreatedAt()->format('Y-m-d') === $today);

        // --- Graphique évolution clients (6 derniers mois) ---
        $clientsEvolutionLabels = [];
        $clientsEvolutionData = [];
        for ($i = 5; $i >= 0; $i--) {
            $monthDate = new \DateTime("-$i months");
            $monthLabel = $monthDate->format('M');
            $monthNum = $monthDate->format('m');

            $clientsEvolutionLabels[] = $monthLabel;
            $count = count(array_filter($clients, fn($c) => $c->getCreatedAt() && $c->getCreatedAt()->format('m') === $monthNum));
            $clientsEvolutionData[] = $count;
        }

        // --- Répartition par société ---
        $societeCountsMap = [];
        foreach ($clients as $client) {
            $societe = $client->getSociete() ?? 'Non défini';
            if (!isset($societeCountsMap[$societe])) {
                $societeCountsMap[$societe] = 0;
            }
            $societeCountsMap[$societe]++;
        }
        $societeLabels = array_keys($societeCountsMap);
        $societeCounts = array_values($societeCountsMap);

        // --- Revenus estimés ---
        $revenus = 0; // à remplacer si tu ajoutes un champ revenu

        return $this->render('dashboard/dashboard.html.twig', [
            'topClients' => $topClients,
            'newClientsToday' => $newClientsToday,
            'totalClients' => $totalClients,
            'activeClientsThisMonth' => $activeClientsThisMonth,
            'revenus' => $revenus,
            'clientsEvolutionLabels' => $clientsEvolutionLabels,
            'clientsEvolutionData' => $clientsEvolutionData,
            'societeLabels' => $societeLabels,
            'societeCounts' => $societeCounts,
        ]);
    }
}
