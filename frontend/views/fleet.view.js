import ItemView from './item.view.js';

export default class UnitView extends ItemView {
  /**
   * Initialisation de la page
   * @param {UnitTypeModel[]} unitTypes Liste des types d'unités
   * @param {UniverseModel} universe Données de l'univers
   * @returns {Promise<Node>} Noeud HTML de la page
   */
  async init(unitTypes, universe) {
    await super.init(unitTypes);

    // Liste des galaxies
    this.setGalaxies(universe.galaxies);

    // Liste des systèmes solaires
    const { solarSystems } = universe.galaxies[0];
    this.setSolarSystems(solarSystems);

    // Liste des planètes
    const { planets } = solarSystems[0];
    this.setPlanets(planets);

    return this.target;
  }

  /**
   * Affichage des types d'unités
   * @param {UnitTypeModel[]} unitTypes Liste des types d'unités
   */
  setItems(unitTypes) {
    const fleetList = this.target.querySelector('.fleet-list');
    const fleetTemplate = this.mainView.template.querySelector('.fleet-row');

    fleetList.innerHTML = '';

    if (!unitTypes.length) {
      fleetList.remove();
      return;
    }

    unitTypes.forEach((unitType) => {
      const fleetRow = fleetTemplate.cloneNode(true);
      const nbUnits = unitType.units.filter((unit) => !unit.createInProgress).length;

      fleetRow.innerHTML = fleetTemplate.innerHTML
        .replace('{{name}}', unitType.name)
        .replace('{{quantity}}', nbUnits)
        .replace('{{imageUrl}}', unitType.imgUrl)
        .replace('{{imageTxt}}', unitType.name)
        .replace('{{sendUnitMax}}', nbUnits)
        .replaceAll('{{sendUnitId}}', unitType.itemId.toLowerCase());

      fleetList.appendChild(fleetRow);
    });
  }

  /**
   * Affichage des galaxies
   * @param {GalaxyModel[]} galaxies Liste des galaxies
   */
  async setGalaxies(galaxies) {
    const galaxySelect = this.target.getElementById('fleet-galaxy');

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
   */
  async setSolarSystems(solarSystems) {
    const solarSystemSelect = this.target.getElementById('fleet-solarsystem');

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
   */
  async setPlanets(planets) {
    const planetSelect = this.target.getElementById('fleet-planet');

    planetSelect.innerHTML = '';

    planets.forEach((planet) => {
      const planetOption = document.createElement('option');

      planetOption.value = planet.id;
      planetOption.text = planet.name;
      planetSelect.add(planetOption, null);
    });
  }
}
