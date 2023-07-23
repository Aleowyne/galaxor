export default class Config {
  static routes = {
    404: 'frontend/templates/404.html',
    '': 'frontend/templates/home.html',
    universe: 'frontend/templates/universe.html',
  };

  /**
   * Récupération du chemin vers le template HTML pour la page demandée
   * @param {string} path Chemin d'accès de la page
   * @returns Chemin vers le template
   */
  static getTemplate(path) {
    return this.routes[path] || this.routes['404'];
  }
}
