<?php

namespace App\DataFixtures;

use App\Entity\Action;
use App\Entity\AxeStrategique;
use App\Entity\Enum\Periodicite;
use App\Entity\Enum\StatutActivite;
use App\Entity\Enum\TypeIndicateur;
use App\Entity\Indicateur;
use App\Entity\Programme;
use App\Entity\RealisationIndicateur;
use App\Entity\SousTache;
use App\Entity\Tache;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

/**
 * Jeu de démo GAR réaliste pour valider le modèle de bout en bout : deux
 * axes stratégiques, trois programmes avec RProg, une chaîne complète
 * Action > Tâche > Sous-tâche sur le premier programme, et des indicateurs
 * avec quelques réalisations saisies.
 */
class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $axeIndustrie = (new AxeStrategique())
            ->setCode('AXE1')
            ->setLibelle('Développement industriel et compétitivité')
            ->setDescription("Renforcement du tissu industriel national et amélioration de la compétitivité des entreprises.");
        $manager->persist($axeIndustrie);

        $axeCommerce = (new AxeStrategique())
            ->setCode('AXE2')
            ->setLibelle('Promotion du commerce intérieur et extérieur')
            ->setDescription('Facilitation des échanges commerciaux et régulation du marché.');
        $manager->persist($axeCommerce);

        $programmeZonesIndustrielles = (new Programme())
            ->setCode('P1')
            ->setLibelle('Programme de développement des zones industrielles')
            ->setAxeStrategique($axeIndustrie)
            ->setExerciceBudgetaire(2026)
            ->setRprogEmail('awa.fall@mincom.sn')
            ->setRprogNom('Fall')
            ->setRprogPrenom('Awa')
            ->setBudgetPrevu('2500000000.00')
            ->setBudgetEngage('900000000.00')
            ->setBudgetExecute('450000000.00')
            ->setDateDebut(new \DateTimeImmutable('2026-01-01'))
            ->setDateFin(new \DateTimeImmutable('2028-12-31'))
            ->setStatut(StatutActivite::EN_COURS);
        $manager->persist($programmeZonesIndustrielles);

        $programmeAppuiPme = (new Programme())
            ->setCode('P2')
            ->setLibelle('Programme d\'appui aux PME/PMI')
            ->setAxeStrategique($axeIndustrie)
            ->setExerciceBudgetaire(2026)
            ->setRprogEmail('moussa.ndiaye@mincom.sn')
            ->setRprogNom('Ndiaye')
            ->setRprogPrenom('Moussa')
            ->setBudgetPrevu('800000000.00')
            ->setBudgetEngage('200000000.00')
            ->setBudgetExecute('50000000.00')
            ->setDateDebut(new \DateTimeImmutable('2026-01-01'))
            ->setDateFin(new \DateTimeImmutable('2027-12-31'))
            ->setStatut(StatutActivite::PLANIFIE);
        $manager->persist($programmeAppuiPme);

        $programmeFacilitationCommerce = (new Programme())
            ->setCode('P3')
            ->setLibelle('Programme de régulation et facilitation du commerce')
            ->setAxeStrategique($axeCommerce)
            ->setExerciceBudgetaire(2026)
            ->setRprogEmail('fatou.diop@mincom.sn')
            ->setRprogNom('Diop')
            ->setRprogPrenom('Fatou')
            ->setBudgetPrevu('600000000.00')
            ->setBudgetEngage('600000000.00')
            ->setBudgetExecute('380000000.00')
            ->setDateDebut(new \DateTimeImmutable('2026-01-01'))
            ->setDateFin(new \DateTimeImmutable('2026-12-31'))
            ->setStatut(StatutActivite::EN_COURS);
        $manager->persist($programmeFacilitationCommerce);

        $actionAmenagementDiamniadio = (new Action())
            ->setCode('P1-A1')
            ->setLibelle('Aménagement de la zone industrielle de Diamniadio')
            ->setProgramme($programmeZonesIndustrielles)
            ->setResponsableEmail('ibrahima.sarr@mincom.sn')
            ->setResponsableNom('Sarr')
            ->setResponsablePrenom('Ibrahima')
            ->setBudgetPrevu('1800000000.00')
            ->setBudgetEngage('700000000.00')
            ->setBudgetExecute('380000000.00')
            ->setDateDebut(new \DateTimeImmutable('2026-01-01'))
            ->setDateFin(new \DateTimeImmutable('2027-06-30'))
            ->setStatut(StatutActivite::EN_COURS);
        $manager->persist($actionAmenagementDiamniadio);

        $tacheEtudeFaisabilite = (new Tache())
            ->setLibelle('Étude de faisabilité')
            ->setAction($actionAmenagementDiamniadio)
            ->setResponsableEmail('khady.gueye@mincom.sn')
            ->setResponsableNom('Gueye')
            ->setResponsablePrenom('Khady')
            ->setDateDebut(new \DateTimeImmutable('2026-01-01'))
            ->setDateFin(new \DateTimeImmutable('2026-03-31'))
            ->setStatut(StatutActivite::REALISE)
            ->setAvancementPourcentage(100)
            ->setBudgetPrevu('50000000.00')
            ->setBudgetExecute('48000000.00');
        $manager->persist($tacheEtudeFaisabilite);

        $sousTacheTopographie = (new SousTache())
            ->setLibelle('Collecte des données topographiques')
            ->setTache($tacheEtudeFaisabilite)
            ->setResponsableEmail('khady.gueye@mincom.sn')
            ->setResponsableNom('Gueye')
            ->setResponsablePrenom('Khady')
            ->setDateDebut(new \DateTimeImmutable('2026-01-01'))
            ->setDateFin(new \DateTimeImmutable('2026-02-15'))
            ->setStatut(StatutActivite::REALISE)
            ->setAvancementPourcentage(100);
        $manager->persist($sousTacheTopographie);

        $sousTacheRapport = (new SousTache())
            ->setLibelle("Rédaction du rapport d'étude")
            ->setTache($tacheEtudeFaisabilite)
            ->setResponsableEmail('khady.gueye@mincom.sn')
            ->setResponsableNom('Gueye')
            ->setResponsablePrenom('Khady')
            ->setDateDebut(new \DateTimeImmutable('2026-02-16'))
            ->setDateFin(new \DateTimeImmutable('2026-03-31'))
            ->setStatut(StatutActivite::REALISE)
            ->setAvancementPourcentage(100);
        $manager->persist($sousTacheRapport);

        $tacheViabilisation = (new Tache())
            ->setLibelle('Travaux de viabilisation')
            ->setAction($actionAmenagementDiamniadio)
            ->setResponsableEmail('ibrahima.sarr@mincom.sn')
            ->setResponsableNom('Sarr')
            ->setResponsablePrenom('Ibrahima')
            ->setDateDebut(new \DateTimeImmutable('2026-04-01'))
            ->setDateFin(new \DateTimeImmutable('2027-06-30'))
            ->setStatut(StatutActivite::EN_COURS)
            ->setAvancementPourcentage(30)
            ->setBudgetPrevu('1750000000.00')
            ->setBudgetExecute('332000000.00');
        $manager->persist($tacheViabilisation);

        $actionFondsGarantie = (new Action())
            ->setCode('P2-A1')
            ->setLibelle('Mise en place d\'un fonds de garantie PME')
            ->setProgramme($programmeAppuiPme)
            ->setResponsableEmail('aissatou.ba@mincom.sn')
            ->setResponsableNom('Ba')
            ->setResponsablePrenom('Aïssatou')
            ->setBudgetPrevu('500000000.00')
            ->setDateDebut(new \DateTimeImmutable('2026-03-01'))
            ->setDateFin(new \DateTimeImmutable('2026-12-31'))
            ->setStatut(StatutActivite::PLANIFIE);
        $manager->persist($actionFondsGarantie);

        $tacheCadreReglementaire = (new Tache())
            ->setLibelle('Élaboration du cadre réglementaire')
            ->setAction($actionFondsGarantie)
            ->setResponsableEmail('aissatou.ba@mincom.sn')
            ->setResponsableNom('Ba')
            ->setResponsablePrenom('Aïssatou')
            ->setDateDebut(new \DateTimeImmutable('2026-03-01'))
            ->setDateFin(new \DateTimeImmutable('2026-05-31'))
            ->setStatut(StatutActivite::PLANIFIE)
            ->setAvancementPourcentage(0);
        $manager->persist($tacheCadreReglementaire);

        $actionGuichetUnique = (new Action())
            ->setCode('P3-A1')
            ->setLibelle('Modernisation du guichet unique du commerce extérieur')
            ->setProgramme($programmeFacilitationCommerce)
            ->setResponsableEmail('cheikh.diagne@mincom.sn')
            ->setResponsableNom('Diagne')
            ->setResponsablePrenom('Cheikh')
            ->setBudgetPrevu('600000000.00')
            ->setBudgetEngage('600000000.00')
            ->setBudgetExecute('380000000.00')
            ->setDateDebut(new \DateTimeImmutable('2026-01-01'))
            ->setDateFin(new \DateTimeImmutable('2026-11-30'))
            ->setStatut(StatutActivite::EN_COURS);
        $manager->persist($actionGuichetUnique);

        $tachePlateforme = (new Tache())
            ->setLibelle('Déploiement de la plateforme numérique')
            ->setAction($actionGuichetUnique)
            ->setResponsableEmail('cheikh.diagne@mincom.sn')
            ->setResponsableNom('Diagne')
            ->setResponsablePrenom('Cheikh')
            ->setDateDebut(new \DateTimeImmutable('2026-01-01'))
            ->setDateFin(new \DateTimeImmutable('2026-11-30'))
            ->setStatut(StatutActivite::EN_COURS)
            ->setAvancementPourcentage(60)
            ->setBudgetPrevu('600000000.00')
            ->setBudgetExecute('380000000.00');
        $manager->persist($tachePlateforme);

        $indicateurEntreprisesInstallees = (new Indicateur())
            ->setCode('P1-IND1')
            ->setLibelle("Nombre d'entreprises installées dans la zone industrielle")
            ->setUnite('nombre')
            ->setTypeIndicateur(TypeIndicateur::EFFET)
            ->setValeurReference('5.00')
            ->setValeurCible('50.00')
            ->setPeriodicite(Periodicite::ANNUELLE)
            ->setProgramme($programmeZonesIndustrielles);
        $manager->persist($indicateurEntreprisesInstallees);

        $realisation2025 = (new RealisationIndicateur())
            ->setIndicateur($indicateurEntreprisesInstallees)
            ->setPeriode('2025')
            ->setValeurRealisee('8.00')
            ->setObservations("Valeur constatée en fin d'exercice 2025, avant le lancement du programme actuel.")
            ->setSaisiParEmail('awa.fall@mincom.sn');
        $manager->persist($realisation2025);

        $indicateurAvancementTravaux = (new Indicateur())
            ->setCode('P1-A1-IND1')
            ->setLibelle('Taux d\'avancement des travaux de viabilisation')
            ->setUnite('%')
            ->setTypeIndicateur(TypeIndicateur::EXTRANT)
            ->setValeurReference('0.00')
            ->setValeurCible('100.00')
            ->setPeriodicite(Periodicite::TRIMESTRIELLE)
            ->setAction($actionAmenagementDiamniadio);
        $manager->persist($indicateurAvancementTravaux);

        $realisationT1 = (new RealisationIndicateur())
            ->setIndicateur($indicateurAvancementTravaux)
            ->setPeriode('2026-T1')
            ->setValeurRealisee('18.00')
            ->setSaisiParEmail('ibrahima.sarr@mincom.sn');
        $manager->persist($realisationT1);

        $realisationT2 = (new RealisationIndicateur())
            ->setIndicateur($indicateurAvancementTravaux)
            ->setPeriode('2026-T2')
            ->setValeurRealisee('30.00')
            ->setSaisiParEmail('ibrahima.sarr@mincom.sn');
        $manager->persist($realisationT2);

        $manager->flush();
    }
}
