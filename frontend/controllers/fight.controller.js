import FightModel from '../models/fight.model.js';
import FightView from '../views/fight.view.js';

export default class FightController {
  constructor() {
    this.mainController = null;
    this.view = null;
    this.fights = [];
  }

  /**
   * Construction de la vue
   * @param {MainController} mainController Contrôleur principal
   * @returns {Promise<Node>} Noeud HTML de la page
   */
  async setupView(mainController) {
    this.mainController = mainController;
    this.view = new FightView(this.mainController.view);

    // Récupération des combats de la planète
    this.fights = await this.getFightsPlanet();

    return this.view.init(this.fights);
  }

  /**
   * Traitement
   */
  process() {
    // Sélection d'un combat
    this.addEventSelectFight();
  }

  /**
   * Récupération des combats de la planète
   * @returns Les combats de la planète
   */
  async getFightsPlanet() {
    try {
      // Récupération des combats de la planète
      const jsonResponse = await this.mainController.requestGet(`/galaxor/api/planets/${this.mainController.planet.id}/fights`);

      return await Promise.all(jsonResponse.fights.map(async (fight) => {
        const opponentPlanetId = (fight.attack_planet === this.mainController.planet.id) ? fight.defense_planet : fight.attack_planet;
        const opponentPlanet = await this.mainController.getPlanet(opponentPlanetId);

        return new FightModel(fight, opponentPlanet);
      }));
    }
    catch (error) {
      this.mainController.displayErrorAlert(error);
      return [];
    }
  }

  /**
   * Gestion de l'évènement "Sélection d'un combat"
   */
  addEventSelectFight() {
    const fightRows = document.querySelectorAll('.fight-item');

    fightRows.forEach((fightRow) => {
      fightRow.addEventListener('click', async (event) => {
        event.preventDefault();
        const target = event.currentTarget;

        // Récupération du combat
        const fightId = Number(target.getAttribute('data-fightid'));
        const fight = this.fights.find((fight) => fight.id === fightId);

        // Affichage du rapport
        if (fight) {
          this.view.target = document;
          this.view.setReport(fight);
        }
      });
    });
  }
}
