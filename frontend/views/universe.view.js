import BaseView from './base.view.js';

export default class UniverseView extends BaseView {
  /**
   * Initialisation de la page
   * @param {UniverseModel} universe Données de l'univers
   * @returns {Promise<Node>} Noeud HTML de la page
   */
  async init(universe) {
    const template = await super.init();

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
   * Affichage des galaxies
   * @param {GalaxyModel[]} galaxies Liste des galaxies
   * @param {Node} target Noeud HTML
   */
  async setGalaxies(galaxies, target = document) {
    const galaxySelect = target.getElementById('galaxy-list');

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
    const solarSystemSelect = target.getElementById('solarsystem-list');

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
    const planetTableBody = target.getElementById('planet-list');

    planetTableBody.innerHTML = '';

    // Tri des planètes par position
    planets.sort((p1, p2) => p1.position > p2.position);

    for (let position = 1; position <= 10; position += 1) {
      const planet = planets.find((planet) => planet.position === position);

      const newRow = planetTableBody.insertRow(-1);
      newRow.classList.add('universe-table-tr');

      for (let column = 0; column < 3; column += 1) {
        let nodeText = '';

        const newCell = newRow.insertCell(column);
        newCell.classList.add('universe-table-td');

        if (planet) {
          newCell.setAttribute('data-planetid', planet.id);
          newCell.setAttribute('data-ownerid', planet.ownerId);
          newCell.setAttribute('data-position', position);
        }

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
