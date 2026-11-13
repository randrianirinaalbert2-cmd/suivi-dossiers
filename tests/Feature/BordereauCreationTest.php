<?php

namespace Tests\Feature;

use App\Enums\FamilleMouvement;
use App\Enums\PoleDossier;
use App\Enums\RoleUtilisateur;
use App\Enums\StatutDossier;
use App\Models\Agent;
use App\Models\BordereauCreation;
use App\Models\BordereauRecu;
use App\Models\Destination;
use App\Models\Division;
use App\Models\Dossier;
use App\Models\Lieu;
use App\Models\PieceRequise;
use App\Models\ServiceOrigine;
use App\Models\TypeDossier;
use App\Models\TypePiece;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BordereauCreationTest extends TestCase
{
    use RefreshDatabase;

    private Division $division;

    private User $utilisateur;

    private TypeDossier $typeDossier;

    private array $entete;

    protected function setUp(): void
    {
        parent::setUp();

        $this->division = Division::create(['nom' => 'Solde']);
        $lieu = Lieu::create(['code' => '101', 'nom' => 'Ambositra', 'sigle' => 'AMM']);
        $destination = Destination::create(['nom' => 'Direction Generale']);

        $this->typeDossier = TypeDossier::create([
            'division_id' => $this->division->id,
            'code' => 'CMVT',
            'libelle' => 'Changement de situation',
            'famille' => FamilleMouvement::ModificationSituation,
        ]);

        $this->utilisateur = User::factory()->create([
            'role' => RoleUtilisateur::Administrateur,
            'division_id' => $this->division->id,
        ]);

        $this->entete = [
            'division_id' => $this->division->id,
            'lieu_id' => $lieu->id,
            'destination_id' => $destination->id,
            'chapitre' => '4200',
            'paquet' => '01',
            'annee' => 2026,
            'num_ordre' => 1,
            'date_edition' => '2026-10-15',
            'observation' => 'Transmission pour competence',
        ];
    }

    private function ligne(array $surcharge = []): array
    {
        return array_merge([
            'agent_im' => '123456',
            'agent_nom' => 'RAKOTO',
            'agent_prenoms' => 'Jean',
            'type_dossier_id' => $this->typeDossier->id,
            'observation' => '',
        ], $surcharge);
    }

    public function test_formulaire_de_creation_saffiche(): void
    {
        $this->actingAs($this->utilisateur)
            ->get(route('bordereaux-creation.create'))
            ->assertOk()
            ->assertSee('Ajouter une ligne');
    }

    public function test_creation_avec_saisie_manuelle_de_lagent(): void
    {
        $reponse = $this->actingAs($this->utilisateur)
            ->post(route('bordereaux-creation.store'), $this->entete + [
                'lignes' => [$this->ligne()],
            ]);

        $bordereau = BordereauCreation::firstOrFail();

        $reponse->assertRedirect(route('bordereaux-creation.show', $bordereau));

        $this->assertDatabaseHas('bordereaux_creation', [
            'id' => $bordereau->id,
            'annee' => 2026,
            'statut' => 'valide',
        ]);

        $this->assertDatabaseHas('lignes_bordereau_creation', [
            'bordereau_creation_id' => $bordereau->id,
            'agent_id' => null,
            'agent_im' => '123456',
            'agent_nom' => 'RAKOTO',
            'agent_prenoms' => 'Jean',
            'type_dossier_id' => $this->typeDossier->id,
        ]);
    }

    public function test_lagent_est_obligatoire_sur_chaque_ligne(): void
    {
        $this->actingAs($this->utilisateur)
            ->post(route('bordereaux-creation.store'), $this->entete + [
                'lignes' => [$this->ligne(['agent_im' => '', 'agent_nom' => ''])],
            ])
            ->assertSessionHasErrors(['lignes.0.agent_im', 'lignes.0.agent_nom']);

        $this->assertDatabaseCount('bordereaux_creation', 0);
    }

    public function test_archivage_et_desarchivage(): void
    {
        $this->actingAs($this->utilisateur)
            ->post(route('bordereaux-creation.store'), $this->entete + ['lignes' => [$this->ligne()]]);

        $bordereau = BordereauCreation::firstOrFail();

        $this->actingAs($this->utilisateur)
            ->post(route('bordereaux-creation.archiver', $bordereau))
            ->assertRedirect(route('bordereaux-creation.show', $bordereau));

        $bordereau->refresh();
        $this->assertTrue($bordereau->estArchive());
        $this->assertNotNull($bordereau->archive_le);
        $this->assertSame($this->utilisateur->id, $bordereau->archive_par_id);

        $this->actingAs($this->utilisateur)
            ->post(route('bordereaux-creation.desarchiver', $bordereau));

        $bordereau->refresh();
        $this->assertFalse($bordereau->estArchive());
        $this->assertNull($bordereau->archive_le);
    }

    public function test_fiche_bordereau_saffiche(): void
    {
        $this->actingAs($this->utilisateur)
            ->post(route('bordereaux-creation.store'), $this->entete + ['lignes' => [$this->ligne()]]);

        $bordereau = BordereauCreation::firstOrFail();

        $this->actingAs($this->utilisateur)
            ->get(route('bordereaux-creation.show', $bordereau))
            ->assertOk()
            ->assertSee('123456')
            ->assertSee('RAKOTO');
    }

    public function test_formulaire_de_modification_prefixe_les_lignes(): void
    {
        $this->actingAs($this->utilisateur)
            ->post(route('bordereaux-creation.store'), $this->entete + ['lignes' => [$this->ligne()]]);

        $bordereau = BordereauCreation::firstOrFail();

        $this->actingAs($this->utilisateur)
            ->get(route('bordereaux-creation.edit', $bordereau))
            ->assertOk()
            ->assertSee('123456')
            ->assertSee('RAKOTO')
            ->assertSee('Jean');
    }

    public function test_impression_affiche_im_et_signataire_sans_date(): void
    {
        User::factory()->create([
            'nom' => 'RAZAFINDRASOA Fanjatina Bakoly',
            'role' => RoleUtilisateur::ChefDivision,
            'division_id' => $this->division->id,
        ]);

        $this->actingAs($this->utilisateur)
            ->post(route('bordereaux-creation.store'), $this->entete + ['lignes' => [$this->ligne()]]);

        $bordereau = BordereauCreation::firstOrFail();

        // Le modèle « SOIT TRANSMIS » ne reprend que l'IM des agents : le nom
        // reste dans l'application (bordereau de création), pas sur la note.
        // La note est signée par le chef de division (P.O.).
        $this->actingAs($this->utilisateur)
            ->get(route('bordereaux-creation.soit-transmis', $bordereau))
            ->assertOk()
            ->assertSee('123456')
            ->assertSee('RAZAFINDRASOA Fanjatina Bakoly')
            ->assertDontSee('RAMIALIMIHASO Veloarisoa')
            ->assertSee('Antananarivo le,')
            ->assertDontSee('15/10/2026');
    }

    public function test_impression_du_be_affiche_le_bordereau_d_envoi(): void
    {
        $this->actingAs($this->utilisateur)
            ->post(route('bordereaux-creation.store'), $this->entete + ['lignes' => [$this->ligne()]]);

        $bordereau = BordereauCreation::firstOrFail();

        // Le BE est un document distinct du « Soit transmis » : il liste les
        // agents (nom, IM, CMVT) et se signe par le chef de division.
        $this->actingAs($this->utilisateur)
            ->get(route('bordereaux-creation.imprimer', $bordereau))
            ->assertOk()
            ->assertSee("BORDEREAU D'ENVOI", false)
            ->assertSee('Nom et prénoms')
            ->assertSee('RAKOTO JEAN')
            ->assertSee('123456')
            ->assertSee('CMVT')
            ->assertDontSee('SOIT TRANSMIS')
            ->assertDontSee("Nombre d'actes formatés (facultatif)", false);
    }

    public function test_impression_reprend_le_modele_soit_transmis_en_un_seul_exemplaire(): void
    {
        $this->actingAs($this->utilisateur)
            ->post(route('bordereaux-creation.store'), $this->entete + ['lignes' => [$this->ligne()]]);

        $bordereau = BordereauCreation::firstOrFail();

        $reponse = $this->actingAs($this->utilisateur)
            ->get(route('bordereaux-creation.soit-transmis', $bordereau))
            ->assertOk()
            ->assertSee('SOIT TRANSMIS')
            ->assertSee('POUR ATTRIBUTION')
            ->assertSee("Sigle de l'agent", false)
            ->assertSee('Nombre acte formaté Mouvement');

        $contenu = $reponse->getContent();

        // Un seul bloc « SOIT TRANSMIS » dans la page imprimée.
        $this->assertSame(1, substr_count($contenu, 'SOIT TRANSMIS'));
        $this->assertSame(1, substr_count($contenu, 'POUR ATTRIBUTION'));
    }

    public function test_impression_propose_le_nombre_d_actes_formates_en_option(): void
    {
        $this->actingAs($this->utilisateur)
            ->post(route('bordereaux-creation.store'), $this->entete + ['lignes' => [$this->ligne()]]);

        $bordereau = BordereauCreation::firstOrFail();

        // Sans saisie, la case du modèle reste vide : le nombre d'actes
        // formatés est facultatif.
        $this->actingAs($this->utilisateur)
            ->get(route('bordereaux-creation.soit-transmis', $bordereau))
            ->assertOk()
            ->assertSee("Nombre d'actes formatés (facultatif)", false)
            ->assertSee('id="actes"', false)
            ->assertSee('<td class="case" id="nombre-actes"></td>', false);
    }

    public function test_le_formulaire_des_actes_recharge_la_note_soit_transmis(): void
    {
        $this->actingAs($this->utilisateur)
            ->post(route('bordereaux-creation.store'), $this->entete + ['lignes' => [$this->ligne()]]);

        $bordereau = BordereauCreation::firstOrFail();

        // Valider le champ « actes » ne doit pas renvoyer vers le bordereau
        // d'envoi : la note « Soit transmis » doit se recharger elle-même.
        $this->actingAs($this->utilisateur)
            ->get(route('bordereaux-creation.soit-transmis', $bordereau))
            ->assertOk()
            ->assertSee('action="'.route('bordereaux-creation.soit-transmis', $bordereau).'"', false)
            ->assertDontSee('action="'.route('bordereaux-creation.imprimer', $bordereau).'"', false);
    }

    public function test_impression_reprend_le_nombre_d_actes_formates_saisi(): void
    {
        $this->actingAs($this->utilisateur)
            ->post(route('bordereaux-creation.store'), $this->entete + ['lignes' => [$this->ligne()]]);

        $bordereau = BordereauCreation::firstOrFail();

        $this->actingAs($this->utilisateur)
            ->get(route('bordereaux-creation.soit-transmis', ['bordereau' => $bordereau, 'actes' => 6]))
            ->assertOk()
            ->assertSee('value="6"', false)
            ->assertSee('<td class="case" id="nombre-actes">6</td>', false);
    }

    public function test_impression_laisse_l_acte_formate_vide_pour_les_autres_mouvements(): void
    {
        // Avenant (code 12) : le référentiel prévoit « Acte formaté » x2, mais
        // la case reste vide comme pour le CMVT 03 — aucune somme automatique,
        // la saisie demeure facultative pour tous les mouvements.
        $avenant = TypeDossier::create([
            'division_id' => $this->division->id,
            'code' => '12',
            'libelle' => 'Avenant',
            'famille' => FamilleMouvement::ModificationSituation,
        ]);

        $acteFormate = TypePiece::create(['libelle' => 'Acte formaté']);
        PieceRequise::create([
            'type_dossier_id' => $avenant->id,
            'type_piece_id' => $acteFormate->id,
            'nb_exemplaires' => 2,
        ]);

        $this->actingAs($this->utilisateur)->post(route('bordereaux-creation.store'), $this->entete + [
            'lignes' => [$this->ligne(['type_dossier_id' => $avenant->id])],
        ]);

        $bordereau = BordereauCreation::firstOrFail();

        $this->actingAs($this->utilisateur)
            ->get(route('bordereaux-creation.soit-transmis', $bordereau))
            ->assertOk()
            ->assertSee('<td class="case" id="nombre-actes"></td>', false)
            ->assertDontSee('id="nombre-actes">2</td>', false);

        // La saisie manuelle fonctionne aussi pour ce mouvement.
        $this->actingAs($this->utilisateur)
            ->get(route('bordereaux-creation.soit-transmis', ['bordereau' => $bordereau, 'actes' => 2]))
            ->assertOk()
            ->assertSee('<td class="case" id="nombre-actes">2</td>', false);
    }

    public function test_les_dossiers_de_meme_type_restent_dans_une_seule_note_soit_transmis(): void
    {
        // Deux dossiers du meme type (avancement) et un dossier d'un autre
        // type : la Solde etablit une seule note par bordereau, avec une ligne
        // par dossier — contrairement au Visa qui fait une note par type.
        $avancement = TypeDossier::create([
            'division_id' => $this->division->id,
            'code' => '13',
            'libelle' => 'Avancement de classe / echelon',
            'famille' => FamilleMouvement::ModificationSituation,
        ]);

        $this->actingAs($this->utilisateur)->post(route('bordereaux-creation.store'), $this->entete + [
            'lignes' => [
                $this->ligne(['agent_im' => '111111', 'agent_nom' => 'ALPHA', 'type_dossier_id' => $avancement->id]),
                $this->ligne(['agent_im' => '222222', 'agent_nom' => 'BETA', 'type_dossier_id' => $avancement->id]),
                $this->ligne(['agent_im' => '333333', 'agent_nom' => 'GAMMA', 'type_dossier_id' => $this->typeDossier->id]),
            ],
        ]);

        $bordereau = BordereauCreation::firstOrFail();

        $html = $this->actingAs($this->utilisateur)
            ->get(route('bordereaux-creation.soit-transmis', $bordereau))
            ->assertOk()
            ->getContent();

        // Une seule note pour tout le bordereau.
        $this->assertSame(1, substr_count($html, 'SOIT TRANSMIS'));

        // Les deux dossiers de meme type figurent chacun sur une ligne.
        $this->assertSame(2, substr_count($html, '<td class="case">13</td>'));
        $this->assertSame(1, substr_count($html, '<td class="case">CMVT</td>'));

        // Les trois agents sont bien sur la meme note, avec leur IM.
        foreach (['111111', '222222', '333333'] as $im) {
            $this->assertSame(1, substr_count($html, $im), "IM {$im} absente ou repetee sur la note.");
        }
    }

    public function test_onglet_archives_separe(): void
    {
        $this->actingAs($this->utilisateur)
            ->post(route('bordereaux-creation.store'), $this->entete + ['lignes' => [$this->ligne()]]);

        $bordereau = BordereauCreation::firstOrFail();

        $this->actingAs($this->utilisateur)->post(route('bordereaux-creation.archiver', $bordereau));

        $this->actingAs($this->utilisateur)
            ->get(route('bordereaux-creation.index', ['statut' => 'archives']))
            ->assertOk()
            ->assertSee('2026/0001');

        $this->actingAs($this->utilisateur)
            ->get(route('bordereaux-creation.index', ['statut' => 'actifs']))
            ->assertOk()
            ->assertDontSee('2026/0001');
    }

    public function test_recherche_par_im_et_nom_dagent(): void
    {
        $this->actingAs($this->utilisateur)
            ->post(route('bordereaux-creation.store'), $this->entete + ['lignes' => [$this->ligne()]]);

        $this->actingAs($this->utilisateur)
            ->get(route('bordereaux-creation.index', ['q' => '123456']))
            ->assertOk()
            ->assertSee('2026/0001');

        $this->actingAs($this->utilisateur)
            ->get(route('bordereaux-creation.index', ['q' => 'RAKOTO']))
            ->assertOk()
            ->assertSee('2026/0001');

        // L'agent connaît son nom/prénom mais pas encore son IM :
        // la recherche par nom retrouve le bordereau...
        $this->actingAs($this->utilisateur)
            ->get(route('bordereaux-creation.index', ['q' => 'Jean']))
            ->assertOk()
            ->assertSee('2026/0001');

        // ... et sa fiche révèle bien son IM.
        $this->actingAs($this->utilisateur)
            ->get(route('bordereaux-creation.show', BordereauCreation::firstOrFail()))
            ->assertOk()
            ->assertSee('123456')
            ->assertSee('RAKOTO');

        $this->actingAs($this->utilisateur)
            ->get(route('bordereaux-creation.index', ['q' => 'introuvable']))
            ->assertOk()
            ->assertDontSee('2026/0001');
    }

    /**
     * Prépare un bordereau reçu avec ses dossiers pour la génération du BE.
     */
    private function bordereauRecuAvecDossiers(TypeDossier $type, Agent $agent, ?string $observation = null, StatutDossier $statut = StatutDossier::Traite): BordereauRecu
    {
        $service = ServiceOrigine::create(['nom' => 'DSP']);

        $bordereau = BordereauRecu::create([
            'division_id' => $this->division->id,
            'service_origine_id' => $service->id,
            'utilisateur_id' => $this->utilisateur->id,
            'reference' => 'REF-2026/001',
            'objet' => 'POUR MANDATEMENT',
            'date_bordereau' => '2026-10-10',
            'num_arrivee' => '001',
            'date_arrivee' => '2026-10-10',
            'observation' => $observation,
        ]);

        Dossier::create([
            'bordereau_recu_id' => $bordereau->id,
            'division_id' => $this->division->id,
            'agent_id' => $agent->id,
            'type_dossier_id' => $type->id,
            'responsable_id' => $this->utilisateur->id,
            'statut' => $statut,
            'pole' => PoleDossier::Solde,
        ]);

        return $bordereau;
    }

    public function test_generation_du_be_reprend_automatiquement_les_agents_du_bordereau_recu(): void
    {
        $agent = Agent::create(['im' => '111222', 'nom' => 'RAKOTO', 'prenoms' => 'Jean']);
        $bordereau = $this->bordereauRecuAvecDossiers($this->typeDossier, $agent);
        $dossier = $bordereau->dossiers()->first();

        $this->actingAs($this->utilisateur)
            ->get(route('bordereaux.generer-be', $bordereau))
            ->assertOk()
            ->assertSee('Sélection des dossiers')
            ->assertSee('111222')
            ->assertSee('RAKOTO');

        $this->actingAs($this->utilisateur)
            ->post(route('bordereaux.preparer-be', $bordereau), [
                'chapitre' => '4200',
                'dossiers' => [$dossier->id],
            ])
            ->assertOk()
            ->assertSee('Lignes reprises du bordereau reçu')
            ->assertSee('111222')
            ->assertSee('RAKOTO')
            ->assertSee('Jean')
            ->assertSee('Chapitre 4200');
    }

    public function test_generation_du_be_laisse_la_ligne_cmvt_03_vide_pour_saisie_manuelle(): void
    {
        $typeManuel = TypeDossier::create([
            'division_id' => $this->division->id,
            'code' => '03',
            'libelle' => "Recrutement d'un EFA",
            'famille' => FamilleMouvement::ModificationSituation,
        ]);

        $agentManuel = Agent::create(['im' => '999888', 'nom' => 'NOUVEAU', 'prenoms' => 'Agent']);

        $service = ServiceOrigine::create(['nom' => 'DREN']);
        $bordereau = BordereauRecu::create([
            'division_id' => $this->division->id,
            'service_origine_id' => $service->id,
            'utilisateur_id' => $this->utilisateur->id,
            'reference' => 'REF-2026/002',
            'objet' => 'NOUVELLE RECRUE',
            'date_bordereau' => '2026-10-10',
            'num_arrivee' => '002',
            'date_arrivee' => '2026-10-10',
        ]);

        Dossier::create([
            'bordereau_recu_id' => $bordereau->id,
            'division_id' => $this->division->id,
            'agent_id' => $agentManuel->id,
            'type_dossier_id' => $typeManuel->id,
            'responsable_id' => $this->utilisateur->id,
            'statut' => StatutDossier::Traite,
            'pole' => PoleDossier::Solde,
        ]);

        $dossier = $bordereau->dossiers()->first();

        $this->actingAs($this->utilisateur)
            ->post(route('bordereaux.preparer-be', $bordereau), [
                'chapitre' => '4200',
                'dossiers' => [$dossier->id],
            ])
            ->assertOk()
            ->assertSee('Agent à saisir manuellement')
            ->assertSee("Recrutement d'un EFA")
            ->assertDontSee('999888')
            ->assertDontSee('NOUVEAU');
    }

    public function test_le_bouton_generer_le_be_apparait_sur_le_bordereau_recu(): void
    {
        $agent = Agent::create(['im' => '111222', 'nom' => 'RAKOTO', 'prenoms' => 'Jean']);
        $bordereau = $this->bordereauRecuAvecDossiers($this->typeDossier, $agent);

        $this->actingAs($this->utilisateur)
            ->get(route('bordereaux.show', $bordereau))
            ->assertOk()
            ->assertSee('Générer le BE')
            ->assertSee(route('bordereaux.generer-be', $bordereau), false);
    }

    public function test_generation_du_be_refuse_un_dossier_non_traite(): void
    {
        $agent = Agent::create(['im' => '111222', 'nom' => 'RAKOTO', 'prenoms' => 'Jean']);
        $bordereau = $this->bordereauRecuAvecDossiers($this->typeDossier, $agent, null, StatutDossier::Recu);

        $this->actingAs($this->utilisateur)
            ->get(route('bordereaux.generer-be', $bordereau))
            ->assertRedirect(route('bordereaux.show', $bordereau))
            ->assertSessionHas('erreur');
    }

    public function test_le_be_ne_reprend_que_les_dossiers_traites(): void
    {
        $agentTraite = Agent::create(['im' => '111222', 'nom' => 'RAKOTO', 'prenoms' => 'Jean']);
        $bordereau = $this->bordereauRecuAvecDossiers($this->typeDossier, $agentTraite);

        $agentNonTraite = Agent::create(['im' => '333444', 'nom' => 'RABE', 'prenoms' => 'Paul']);
        $bordereau->dossiers()->create([
            'division_id' => $this->division->id,
            'agent_id' => $agentNonTraite->id,
            'type_dossier_id' => $this->typeDossier->id,
            'responsable_id' => $this->utilisateur->id,
            'statut' => StatutDossier::EnTraitement,
            'pole' => PoleDossier::Solde,
        ]);

        $this->actingAs($this->utilisateur)
            ->get(route('bordereaux.generer-be', $bordereau))
            ->assertOk()
            ->assertSee('111222')
            ->assertDontSee('333444');
    }

    public function test_la_selection_refuse_un_dossier_non_traite(): void
    {
        $agentTraite = Agent::create(['im' => '111222', 'nom' => 'RAKOTO', 'prenoms' => 'Jean']);
        $bordereau = $this->bordereauRecuAvecDossiers($this->typeDossier, $agentTraite);
        $dossierTraite = $bordereau->dossiers()->first();

        $agentNonTraite = Agent::create(['im' => '333444', 'nom' => 'RABE', 'prenoms' => 'Paul']);
        $dossierNonTraite = $bordereau->dossiers()->create([
            'division_id' => $this->division->id,
            'agent_id' => $agentNonTraite->id,
            'type_dossier_id' => $this->typeDossier->id,
            'responsable_id' => $this->utilisateur->id,
            'statut' => StatutDossier::EnTraitement,
            'pole' => PoleDossier::Solde,
        ]);

        $this->actingAs($this->utilisateur)
            ->post(route('bordereaux.preparer-be', $bordereau), [
                'chapitre' => '4200',
                'dossiers' => [$dossierTraite->id, $dossierNonTraite->id],
            ])
            ->assertSessionHasErrors('dossiers.1');
    }

    public function test_le_bouton_generer_le_be_est_masque_si_aucun_dossier_n_est_traite(): void
    {
        $agent = Agent::create(['im' => '111222', 'nom' => 'RAKOTO', 'prenoms' => 'Jean']);
        $bordereau = $this->bordereauRecuAvecDossiers($this->typeDossier, $agent, null, StatutDossier::Recu);

        $this->actingAs($this->utilisateur)
            ->get(route('bordereaux.show', $bordereau))
            ->assertOk()
            ->assertDontSee('Générer le BE')
            ->assertDontSee(route('bordereaux.generer-be', $bordereau), false);
    }
}
