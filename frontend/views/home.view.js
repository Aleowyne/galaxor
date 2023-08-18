import BaseView from './base.view.js';

export default class HomeView extends BaseView {
  /**
   * Initialisation de la page
   * @param {UniverseModel[]} universes Liste des univers
   * @returns {Promise<Node>} Noeud HTML de la page
   */
  async init(universes) {
    const template = await super.init();

    // Alimentation de la liste des univers
    const universeSelect = template.getElementById('login-universe');

    universes.forEach((universe) => {
      const universeOption = document.createElement('option');

      universeOption.value = universe.id;
      universeOption.text = universe.name;
      universeSelect.add(universeOption, null);
    });

    return template;
  }

  /**
   * Ajout d'un univers dans la liste des univers disponibles à la connexion
   * @param {UniverseModel} universe Données de l'univers
   */
  addUniverse(universe) {
    const universeSelect = document.getElementById('login-universe');
    const universeOption = document.createElement('option');

    universeOption.value = universe.id;
    universeOption.text = universe.name;
    universeSelect.add(universeOption, null);
  }
}
