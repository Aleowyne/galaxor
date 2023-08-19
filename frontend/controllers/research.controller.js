import ResearchModel from '../models/research.model.js';
import ResearchView from '../views/research.view.js';

export default class ResearchController {
  constructor() {
    this.mainController = null;
    this.view = null;
    this.researches = [];
  }

  /**
   * Construction de la vue
   * @param {MainController} mainController Contrôleur principal
   * @returns {Promise<Node>} Noeud HTML de la page
   */
  async setupView(mainController) {
    this.mainController = mainController;
    this.view = new ResearchView(this.mainController.view);

    if (this.mainController.planet.id && this.mainController.planet.ownerId === this.mainController.user.id) {
      // Récupération des recherches de la planète
      this.researches = await this.getResearchesPlanet(this.mainController.planet.id);
      return this.view.init(this.researches);
    }

    return document.createElement('div');
  }

  /**
   * Traitement
   */
  process() {
    // Gestion de la construction
    this.addEventBuild();
  }

  /**
   * Récupération des recherches de la planète
   * @param {number} planetId Identifiant de la planète
   * @returns Les recherches de la planète
   */
  async getResearchesPlanet(planetId) {
    try {
      // Récupération des recherches de la planète
      const jsonResponse = await this.mainController.requestGet(`/galaxor/api/planets/${planetId}/researches`);
      return jsonResponse.researches.map((research) => new ResearchModel(research));
    }
    catch (error) {
      this.mainController.displayErrorAlert(error);
      return [];
    }
  }

  /**
   * Gestion de l'évènement "construction d'une recherche"
   */
  async addEventBuild() {
    const buildBtns = document.querySelectorAll('.item-build-btn');

    buildBtns.forEach((buildBtn, index) => {
      buildBtn.addEventListener('click', async (event) => {
        event.preventDefault();

        let research = this.researches[index];
        const currentDate = new Date();
        const planetId = this.mainController.planet.id;

        try {
          // Finalisation de la construction
          if (research.upgradeInProgress && research.endTimeUpgrade <= currentDate) {
            const jsonResponse = await this.mainController.requestPut(`/galaxor/api/planets/${planetId}/researches/${research.itemId}/finish`);
            research = new ResearchModel(jsonResponse);
            this.view.refreshItemFinishBuild(research, index);
          }

          // Lancement de la construction
          else {
            const jsonResponse = await this.mainController.requestPut(`/galaxor/api/planets/${planetId}/researches/${research.itemId}/start`);
            research = new ResearchModel(jsonResponse);
            this.view.setButtonInProgressBuild(research, buildBtn);

            // Refresh des ressources
            await this.mainController.refreshResources();
          }

          this.researches[index] = research;
        }
        catch (error) {
          this.mainController.displayErrorAlert(error);
        }
      });
    });
  }
}
