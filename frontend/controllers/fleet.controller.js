import FleetView from '../views/fleet.view.js';

export default class UnitController {
  constructor() {
    this.mainController = null;
    this.view = null;
    this.unitTypes = [];
    this.selectedGalaxyId = 0;
    this.selectedSolarSystemId = 0;
    this.selectedPlanetId = null;
  }

  /**
   * Construction de la vue
   * @param {MainController} mainController Contrôleur principal
   * @returns {Promise<Node>} Noeud HTML de la page
   */
  async setupView(mainController) {
    this.mainController = mainController;
    this.view = new FleetView(this.mainController.view);

    // Initialisation des sélections
    await this.initSelect();

    // Récupération des types d'unités de la planète
    this.unitTypes = await this.mainController.getUnitTypesPlanet();

    return this.view.init(this.unitTypes, this.mainController.universe);
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

    // Sélection d'une planète
    this.addEventSelectPlanet();

    // Lancement du combat
    this.addEventFight();
  }

  /**
   * Initialisation des sélections
   */
  async initSelect() {
    this.selectedGalaxyId = this.mainController.universe.galaxies[0].id;
    this.selectedSolarSystemId = this.mainController.universe.galaxies[0].solarSystems[0].id;
    this.selectedPlanetId = this.mainController.universe.galaxies[0].solarSystems[0].planets[0].id;
  }

  /**
   * Gestion de l'évènement "sélection d'une galaxie"
   */
  addEventSelectGalaxy() {
    const galaxySelect = document.getElementById('fleet-galaxy');

    galaxySelect.addEventListener('change', async (event) => {
      event.preventDefault();

      // Récupération de l'univers mis à jour
      this.mainController.universe = await this.mainController.getUniverse(this.mainController.universe.id);

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
    });
  }

  /**
   * Gestion de l'évènement "sélection d'un système solaire"
   */
  addEventSelectSolarSystem() {
    const solarSystemSelect = document.getElementById('fleet-solarsystem');

    solarSystemSelect.addEventListener('change', async (event) => {
      event.preventDefault();

      // Récupération de l'univers mis à jour
      this.mainController.universe = await this.mainController.getUniverse(this.mainController.universe.id);

      const galaxy = this.mainController.universe.galaxies.find((galaxy) => galaxy.id === this.selectedGalaxyId);

      // Récupération des planètes à partir du système solaire choisi
      this.selectedSolarSystemId = Number(event.target.value);
      const solarSystem = galaxy.solarSystems.find((solarSystem) => solarSystem.id === this.selectedSolarSystemId);

      if (solarSystem) {
        this.view.setPlanets(solarSystem.planets);
      }
    });
  }

  /**
   * Gestion de l'évènement "sélection d'une planète"
   */
  addEventSelectPlanet() {
    const planetSelect = document.getElementById('fleet-planet');

    planetSelect.addEventListener('change', async (event) => {
      event.preventDefault();

      this.selectedPlanetId = Number(event.target.value);
    });
  }

  /**
   * Gestion de l'évènement "attaque"
   */
  addEventFight() {
    const fightBtn = document.querySelector('.fight-btn');
    const fleetForm = document.querySelector('.fleet-form');

    fightBtn.addEventListener('click', async (event) => {
      if (fleetForm.checkValidity()) {
        event.preventDefault();

        const unitQtyInputs = document.querySelectorAll('.fleet-form-unit-input');
        const attackUnits = [];
        const planetId = this.mainController.planet.id;

        // Récupération des identifiants des unités attaquantes
        unitQtyInputs.forEach((unitQtyInput, index) => {
          const qty = unitQtyInput.value;

          attackUnits.push(...this.unitTypes[index].units
            .filter((unit) => !unit.createInProgress)
            .slice(0, qty)
            .map((unit) => unit.id));
        });

        if (attackUnits.length === 0) {
          this.mainController.displayErrorAlert('Aucune unité attaquante sélectionnée');
          return;
        }

        const bodyRequest = {
          defense_planet: this.selectedPlanetId,
          attack_units: attackUnits,
        };

        try {
          this.mainController.loader.style.display = 'flex';

          // Lancement du combat
          const jsonResponse = await this.mainController.requestPost(`/galaxor/api/planets/${planetId}/fights`, bodyRequest);

          switch (jsonResponse.result) {
            case 'WIN':
              this.mainController.displaySuccessAlert('Combat gagné');
              break;

            case 'DRAW':
              this.mainController.displaySuccessAlert('Egalité');
              break;

            case 'LOSE':
              this.mainController.displaySuccessAlert('Combat perdu');
              break;

            default:
              break;
          }

          // Refresh des ressources
          await this.mainController.refreshResources();

          // Récupération des types d'unités de la planète
          this.unitTypes = await this.mainController.getUnitTypesPlanet();

          this.view.setItems(this.unitTypes);
        }
        catch (error) {
          this.mainController.displayErrorAlert(error);
        }

        this.mainController.loader.style.display = 'none';
      }
    });
  }
}
