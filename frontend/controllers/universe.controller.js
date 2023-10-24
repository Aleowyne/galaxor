import UniverseView from '../views/universe.view.js';

export default class UniverseController {
  constructor() {
    this.mainController = null;
    this.view = null;
    this.selectedGalaxyId = 0;
    this.selectedSolarSystemId = 0;
    this.selectedPlanet = null;
  }

  /**
   * Construction de la vue
   * @param {MainController} mainController Contrôleur principal
   * @returns {Promise<Node>} Noeud HTML de la page
   */
  async setupView(mainController) {
    this.mainController = mainController;
    this.view = new UniverseView(this.mainController.view);

    // Initialisation des sélections
    await this.initSelect();

    return this.view.init(this.mainController.universe);
  }

  /**
   * Traitement
   */
  process() {
    this.view.target = document;

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
   * Initialisation des sélections
   */
  async initSelect() {
    this.selectedGalaxyId = this.mainController.universe.galaxies[0].id;
    this.selectedSolarSystemId = this.mainController.universe.galaxies[0].solarSystems[0].id;
  }

  /**
   * Gestion de l'évènement "sélection d'une galaxie"
   */
  addEventSelectGalaxy() {
    const galaxySelect = document.getElementById('galaxy-list');

    galaxySelect.addEventListener('change', async (event) => {
      event.preventDefault();

      // Récupération de l'univers mis à jour
      await this.mainController.refreshUniverse();

      // Récupération des systèmes solaires à partir de la galaxie choisie
      this.selectedGalaxyId = Number(event.target.value);
      const galaxy = this.mainController.universe.galaxies.find((galaxy) => galaxy.id === this.selectedGalaxyId);

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
      await this.mainController.refreshUniverse();

      const galaxy = this.mainController.universe.galaxies.find((galaxy) => galaxy.id === this.selectedGalaxyId);

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
    const planetRows = document.querySelectorAll('.universe-table-tr');

    planetRows.forEach((planetRow) => {
      planetRow.addEventListener('click', async (event) => {
        event.preventDefault();
        const target = event.currentTarget;

        // Récupération de l'univers mis à jour
        await this.mainController.refreshUniverse();

        // Récupération des données de la planète
        const planetId = Number(target.getAttribute('data-planetid'));

        if (planetId) {
          this.selectedPlanet = await this.mainController.getPlanet(planetId);

          if (this.selectedPlanet) {
            // Planète n'appartenant à personne
            if (this.selectedPlanet.ownerId === 0) {
              this.mainController.displayDialog('S\'installer sur cette planète ?');
            }
            // Planète de l'utilisateur
            else if (this.selectedPlanet.ownerId === this.mainController.user.id) {
              localStorage.setItem('planetId', this.selectedPlanet.id);
              document.location.href = '#structure';
            }
          }
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
      await this.mainController.refreshUniverse();
    });

    confirmDialog.addEventListener('click', async (event) => {
      event.preventDefault();

      const userCell = document.getElementById('planet-list').rows[this.selectedPlanet.position - 1].cells[2];

      const bodyRequest = {
        user_id: this.mainController.user.id,
      };

      try {
        // Assignation d'un utilisateur à la planète
        await this.mainController.requestPut(`/galaxor/api/planets/${this.selectedPlanet.id}`, bodyRequest);
        userCell.innerHTML = this.mainController.user.name;
        this.mainController.displaySuccessAlert('Planète conquise');
      }
      catch (error) {
        this.mainController.displayErrorAlert(error);
      }

      dialog.style.display = 'none';

      // Récupération de l'univers mis à jour
      await this.mainController.refreshUniverse();
    });
  }
}
