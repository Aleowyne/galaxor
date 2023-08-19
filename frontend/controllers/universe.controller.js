import UniverseModel from '../models/universe.model.js';
import UniverseView from '../views/universe.view.js';

export default class UniverseController {
  constructor() {
    this.mainController = null;
    this.view = null;
    this.universe = null;
    this.selectedGalaxyId = 0;
    this.selectedSolarSystemId = 0;
    this.selectedPlanetId = 0;
    this.selectedPosition = 0;
  }

  /**
   * Construction de la vue
   * @param {MainController} mainController Contrôleur principal
   * @returns {Promise<Node>} Noeud HTML de la page
   */
  async setupView(mainController) {
    this.mainController = mainController;
    this.view = new UniverseView(this.mainController.view);

    const universeId = Number(localStorage.getItem('universeId'));

    // Récupération des données de l'univers
    this.universe = await this.getUniverse(universeId);

    if (this.universe.id) {
      this.selectedGalaxyId = this.universe.galaxies[0].id;
      this.selectedSolarSystemId = this.universe.galaxies[0].solarSystems[0].id;

      return this.view.init(this.universe);
    }

    return document.createElement('div');
  }

  /**
   * Traitement
   */
  process() {
    // Sélection d'une galaxie : mise à jour de la liste des systèmes solaires
    this.addEventSelectGalaxy();

    // Sélection d'un système solaire : mise à jour de la liste des planètes
    this.addEventSelectSolarSystem();

    // Colonisation d'une planète
    this.addEventSettlePlanet();

    // Gestion de la boite de dialogue
    this.addEventDialog();
  }

  /**
   * Récupération des données de l'univers
   * @param {number} universeId Identifiant de l'univers
   * @returns Les données de l'univers
   */
  async getUniverse(universeId) {
    try {
      // Récupération des données de l'univers
      const jsonResponse = await this.mainController.requestGet(`/galaxor/api/universes/${universeId}`);
      return new UniverseModel(jsonResponse);
    }
    catch (error) {
      this.mainController.displayErrorAlert(error);
      return new UniverseModel();
    }
  }

  /**
   * Gestion de l'évènement "sélection d'une galaxie"
   */
  addEventSelectGalaxy() {
    const galaxySelect = document.getElementById('galaxy-list');

    galaxySelect.addEventListener('change', async (event) => {
      event.preventDefault();

      // Récupération des systèmes solaires à partir de la galaxie choisie
      this.selectedGalaxyId = Number(event.target.value);
      const galaxy = this.universe.galaxies.find((galaxy) => galaxy.id === this.selectedGalaxyId);

      if (galaxy) {
        this.view.setSolarSystems(galaxy.solarSystems);
        const [solarSystem] = galaxy.solarSystems;

        if (solarSystem) {
          this.view.setPlanets(solarSystem.planets);
        }
      }

      // Colonisation d'une planète
      this.addEventSettlePlanet();
    });
  }

  /**
   * Gestion de l'évènement "sélection d'un système solaire"
   */
  addEventSelectSolarSystem() {
    const solarSystemSelect = document.getElementById('solarsystem-list');

    solarSystemSelect.addEventListener('change', async (event) => {
      event.preventDefault();

      // Récupération de l'univers mis à jour
      this.universe = await this.mainController.getUniverse(this.universe.id);

      const galaxy = this.universe.galaxies.find((galaxy) => galaxy.id === this.selectedGalaxyId);

      // Récupération des planètes à partir du système solaire choisi
      this.selectedSolarSystemId = Number(event.target.value);
      const solarSystem = galaxy.solarSystems.find((solarSystem) => solarSystem.id === this.selectedSolarSystemId);

      if (solarSystem) {
        this.view.setPlanets(solarSystem.planets);
      }

      // Colonisation d'une planète
      this.addEventSettlePlanet();
    });
  }

  /**
   * Gestion de l'évènement "colonisation d'une planète"
   */
  addEventSettlePlanet() {
    const planetCells = document.querySelectorAll('.universe-table-td');

    planetCells.forEach((planetCell) => {
      planetCell.addEventListener('click', async (event) => {
        event.preventDefault();

        // Récupération de l'univers mis à jour
        this.universe = await this.getUniverse(this.universe.id);

        const ownerId = Number(event.target.getAttribute('data-ownerid'));
        this.selectedPlanetId = Number(event.target.getAttribute('data-planetid'));
        this.selectedPosition = Number(event.target.getAttribute('data-position'));

        // Planète n'appartenant à personne
        if (ownerId === 0 && this.selectedPlanetId !== 0) {
          this.displayDialog('S\'installer sur cette planète ?');
        }
        // Planète de l'utilisateur
        else if (ownerId === this.mainController.user.id) {
          localStorage.setItem('planetId', this.selectedPlanetId);
          document.location.href = '#structure';
        }
      });
    });
  }

  /**
   * Gestion des évènements sur la boite de dialogue
   */
  addEventDialog() {
    const dialog = document.getElementById('app-dialog');
    const confirmDialog = document.getElementById('app-dialog-confirm');
    const cancelDialog = document.getElementById('app-dialog-cancel');

    cancelDialog.addEventListener('click', async (event) => {
      event.preventDefault();
      dialog.style.display = 'none';

      // Récupération de l'univers mis à jour
      this.universe = await this.getUniverse(this.universe.id);
    });

    confirmDialog.addEventListener('click', async (event) => {
      event.preventDefault();

      const userCell = document.getElementById('planet-list').rows[this.selectedPosition - 1].cells[2];

      const bodyRequest = {
        user_id: this.user.id,
      };

      try {
        // Assignation d'un utilisateur à la planète
        await this.requestPut(`/galaxor/api/planets/${this.selectedPlanetId}`, bodyRequest);
        userCell.innerHTML = this.user.name;
        this.mainController.displaySuccessAlert('Planète conquise');
      }
      catch (error) {
        this.mainController.displayErrorAlert(error);
      }

      dialog.style.display = 'none';

      // Récupération de l'univers mis à jour
      this.universe = await this.getUniverse(this.universe.id);
    });
  }
}
