export default class UniverseView {
  constructor(mainView) {
    this.mainView = mainView;
    this.target = this.mainView.template.cloneNode(true);
  }

  /**
   * Initialisation de la page
   * @param {UniverseModel} universe Données de l'univers
   * @returns {Promise<Node>} Noeud HTML de la page
   */
  async init(universe) {
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
   * Affichage des galaxies
   * @param {GalaxyModel[]} galaxies Liste des galaxies
   */
  async setGalaxies(galaxies) {
    const galaxySelect = this.target.getElementById('galaxy-list');

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
    const solarSystemSelect = this.target.getElementById('solarsystem-list');

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
    const planetTableBody = this.target.getElementById('planet-list');

    planetTableBody.innerHTML = '';

    // Tri des planètes par position
    planets.sort((p1, p2) => p1.position > p2.position);

    for (let position = 1; position <= 10; position += 1) {
      const planet = planets.find((planet) => planet.position === position);

      const newRow = planetTableBody.insertRow(-1);
      newRow.classList.add('universe-table-tr');

      if (planet) {
        newRow.setAttribute('data-planetid', planet.id);
      }

      for (let column = 0; column < 3; column += 1) {
        let nodeText = '';

        const newCell = newRow.insertCell(column);
        newCell.classList.add('universe-table-td');

        switch (column) {
          case 0:
            nodeText = position;
            break;

          case 1:
            nodeText = planet?.name ?? '';
            break;

          case 2:
            nodeText = planet?.ownerName ?? '';
            break;

          default:
        }

        const newText = document.createTextNode(nodeText);
        newCell.appendChild(newText);
      }
    }
  }
}
