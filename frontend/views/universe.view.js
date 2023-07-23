export default class UniverseView {
  constructor(template) {
    this.template = template;
  }

  /**
   * Initialisation de la page
   * @param {UserModel} user Données de l'utilisateur
   * @param {UniverseModel} universe Données de l'univers
   * @returns Le template modifié de la page
   */
  init(user, universe) {
    return `Utilisateur : ${user.name} et univers : ${universe.name}`;
  }
}
