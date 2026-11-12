
import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.data('piecesDossier', (config) => ({
    url: config.url,
    csrf: config.csrf,
    recues: config.recues,
    exigees: config.exigees,
    enCours: {},
    enCoursTout: false,
    message: '',

    estEnCours(id) {
        return !!this.enCours[id];
    },

    get manquantes() {
        return Math.max(0, this.exigees - this.recues);
    },

    get taux() {
        return this.exigees > 0 ? Math.round((this.recues / this.exigees) * 100) : 100;
    },

    get complet() {
        return this.exigees > 0 && this.manquantes === 0;
    },

    get tousCoches() {
        return this.exigees > 0 && this.recues === this.exigees;
    },

    async basculer(id, caseEl) {
        if (this.enCours[id]) {
            return;
        }

        this.enCours[id] = true;
        const etaitCoche = caseEl.checked;

        try {
            const corps = new FormData();
            corps.append('type_piece_id', id);

            const reponse = await fetch(this.url, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': this.csrf,
                    'X-Requested-With': 'XMLHttpRequest',
                    Accept: 'application/json',
                },
                body: corps,
            });

            if (!reponse.ok) {
                throw new Error('Echec');
            }

            const donnees = await reponse.json();

            this.recues = donnees.completude.recues;
            this.message = donnees.message;
            caseEl.checked = donnees.recue;

            const ligne = caseEl.closest('tr');
            const libelle = ligne?.querySelector('[data-piece-libelle]');

            if (ligne) {
                ligne.classList.toggle('bg-emerald-50/40', donnees.recue);
            }

            if (libelle) {
                libelle.classList.toggle('text-gray-900', donnees.recue);
                libelle.classList.toggle('font-medium', donnees.recue);
                libelle.classList.toggle('text-gray-600', !donnees.recue);
            }
        } catch (e) {
            caseEl.checked = !caseEl.checked;
            this.message = 'Impossible de mettre à jour la pièce. Réessayez.';
        } finally {
            this.enCours[id] = false;
        }
    },

    async toutBasculer(racine) {
        if (this.enCoursTout) {
            return;
        }

        const cible = !this.tousCoches;
        const cases = Array.from(racine.querySelectorAll('[data-piece-case]'));

        this.enCoursTout = true;

        for (const caseEl of cases) {
            if (caseEl.checked !== cible) {
                caseEl.checked = cible;
                await this.basculer(caseEl.dataset.pieceCase, caseEl);
            }
        }

        this.enCoursTout = false;
    },
}));

Alpine.start();
