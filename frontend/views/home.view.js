export default class HomeView {
  constructor(template) {
    this.template = template;
  }

  /**
   * Initialisation de la vue
   * @param {UniverseModel[]} universes Liste des univers
   * @returns {Promise<Node>} Noeud HTML de la page
   */
  async init(universes) {
    const templateElement = document.createElement('div');

    const response = await fetch(this.template);
    templateElement.innerHTML = await response.text();

    const node = templateElement.querySelector('template').content.cloneNode(true);

    // Alimentation de la liste des univers
    const universeSelect = node.getElementById('login-universe');

    universes.forEach((universe) => {
      const universeOption = document.createElement('option');

      universeOption.value = universe.id;
      universeOption.text = universe.name;
      universeSelect.add(universeOption, null);
    });

    return node;
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
