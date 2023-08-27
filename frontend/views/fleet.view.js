import ItemView from './item.view.js';

export default class UnitView extends ItemView {
  /**
   * Initialisation de la page
   * @param {UnitTypeModel[]} unitTypes Liste des types d'unités
   * @param {UniverseModel} universe Données de l'univers
   * @returns {Promise<Node>} Noeud HTML de la page
   */
  async init(unitTypes, universe) {
    const template = await super.init(unitTypes);

    // Liste des galaxies
    this.setGalaxies(universe.galaxies, template);

    // Liste des systèmes solaires
    const { solarSystems } = universe.galaxies[0];
    this.setSolarSystems(solarSystems, template);

    // Liste des planètes
    const { planets } = solarSystems[0];
    this.setPlanets(planets, template);

    return template;
  }

  /**
   * Affichage des types d'unités
   * @param {UnitTypeModel[]} unitTypes Liste des types d'unités
   * @param {Node} target Noeud HTML
   */
  setItems(unitTypes, target = document) {
    const fleetList = target.getElementById('fleet-list');
    const fleetTemplateRow = target.querySelector('.fleet-table-row');
    const fleetRows = target.querySelectorAll('.fleet-table-row');

    fleetTemplateRow.remove();

    unitTypes.forEach((unitType) => {
      const fleetRow = fleetTemplateRow.cloneNode(true);
      const fleetImage = fleetRow.querySelector('.fleet-image');
      const fleetNameTxt = fleetRow.querySelector('.fleet-name');
      const fleetQtyTxt = fleetRow.querySelector('.fleet-qty');
      const fleetLabel = fleetRow.querySelector('.fleet-form-unit-label');
      const fleetInput = fleetRow.querySelector('.fleet-form-unit-input');

      const nbUnits = unitType.units.filter((unit) => !unit.createInProgress).length;

      fleetImage.src = unitType.imgUrl;
      fleetImage.alt = unitType.name;
      fleetQtyTxt.innerHTML = nbUnits;
      fleetNameTxt.innerHTML = unitType.name;

      // Nombre d'unités à envoyer
      fleetLabel.htmlFor = `fleet-${unitType.itemId.toLowerCase()}`;
      fleetInput.id = fleetLabel.htmlFor;
      fleetInput.max = nbUnits;
      fleetInput.value = 0;

      fleetList.appendChild(fleetRow);
    });

    fleetRows.forEach((fleetRow) => {
      fleetRow.remove();
    });
  }

  /**
   * Affichage des galaxies
   * @param {GalaxyModel[]} galaxies Liste des galaxies
   * @param {Node} target Noeud HTML
   */
  async setGalaxies(galaxies, target = document) {
    const galaxySelect = target.getElementById('fleet-galaxy');

    galaxySelect.innerHTML = '';

    galaxies.forEach((galaxy) => {
      const galaxyOption = document.createElement('option');

      galaxyOption.value = galaxy.id;
      galaxyOption.text = galaxy.name;
      galaxySelect.add(galaxyOption, null);
    });
  }

  /**
   * Affichage des systèmes solaires en fonction de la galaxie choisie
   * @param {SolarSystemModel[]} solarSystems Liste des systèmes solaires
   * @param {Node} target Noeud HTML
   */
  async setSolarSystems(solarSystems, target = document) {
    const solarSystemSelect = target.getElementById('fleet-solarsystem');

    solarSystemSelect.innerHTML = '';

    solarSystems.forEach((solarsystem) => {
      const solarSystemOption = document.createElement('option');

      solarSystemOption.value = solarsystem.id;
      solarSystemOption.text = solarsystem.name;
      solarSystemSelect.add(solarSystemOption, null);
    });
  }

  /**
   * Affichage des planètes en fonction du système solaire choisi
   * @param {PlanetModel[]} planets Liste des planètes
   * @param {Node} target Noeud HTML
   */
  async setPlanets(planets, target = document) {
    const planetSelect = target.getElementById('fleet-planet');

    planetSelect.innerHTML = '';

    planets.forEach((planet) => {
      const planetOption = document.createElement('option');

      planetOption.value = planet.id;
      planetOption.text = planet.name;
      planetSelect.add(planetOption, null);
    });
  }
}
