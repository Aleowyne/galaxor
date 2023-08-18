export default class Config {
  static routes = {
    '': 'frontend/templates/home.html',
    error: 'frontend/templates/error.html',
    universe: 'frontend/templates/universe.html',
    structure: 'frontend/templates/structure.html',
    research: 'frontend/templates/research.html',
    unit: 'frontend/templates/unit.html',
  };

  /**
   * Récupération du chemin vers le template HTML pour la page demandée
   * @param {string} path Chemin d'accès de la page
   * @returns {string} Chemin vers le template
   */
  static getTemplate(path) {
    return this.routes[path] || this.routes.error;
  }
}
