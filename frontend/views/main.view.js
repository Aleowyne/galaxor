import Config from '../config.js';

export default class MainView {
  constructor() {
    this.template = null;
  }

  /**
   * Initialisation de la page
   * @param {string} path Chemin d'accès
   */
  async init(path) {
    const templatePath = Config.getTemplate(path);
    const templateElement = document.createElement('div');
    const response = await fetch(templatePath);
    templateElement.innerHTML = await response.text();

    this.template = templateElement.querySelector('template').content.cloneNode(true);

    // Alimentation de la barre de navigation
    this.setHeaderNav(path);
  }

  /**
   * Alimentation de la barre de navigation
   * @param {string} path Chemin d'accès
   */
  setHeaderNav(path) {
    const headerNav = document.querySelector('header');

    // Header non visible si page d'accueil
    if (path === '') {
      headerNav.style.display = 'none';
      return;
    }

    headerNav.style.display = 'flex';
  }

  /**
   * Affichage des ressources dans la barre de navigation
   * @param {ResourceModel[]} resources Liste des ressources
   */
  displayResources(resources) {
    const headerMetalQtyTxt = document.getElementById('header-metal-qty');
    const headerDeuteriumQtyTxt = document.getElementById('header-deuterium-qty');
    const headerEnergyQtyTxt = document.getElementById('header-energy-qty');

    if (resources.length) {
      headerMetalQtyTxt.innerHTML = resources.find((resource) => resource.id === 1).quantity;
      headerDeuteriumQtyTxt.innerHTML = resources.find((resource) => resource.id === 2).quantity;
      headerEnergyQtyTxt.innerHTML = resources.find((resource) => resource.id === 3).quantity;
    }
    else {
      headerMetalQtyTxt.innerHTML = 0;
      headerDeuteriumQtyTxt.innerHTML = 0;
      headerEnergyQtyTxt.innerHTML = 0;
    }
  }

  /**
   * Transforme un temps en secondes en une chaîne avec un temps en h, min, s
   * @param {number} time Temps en secondes
   * @returns Temps en heures, minutes, secondes
   */
  displayTime(time) {
    const timeDate = new Date(time * 1000);
    const hours = timeDate.getUTCHours();
    const minutes = timeDate.getUTCMinutes();
    const seconds = timeDate.getUTCSeconds();
    const timeText = [];

    if (hours !== 0) {
      timeText.push(`${hours} h`);
    }

    if (minutes !== 0) {
      timeText.push(`${minutes} min`);
    }

    if (seconds !== 0) {
      timeText.push(`${seconds} s`);
    }

    return timeText.join(' ');
  }
}
