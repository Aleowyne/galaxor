export default class BaseView {
  constructor(template) {
    this.template = template;
  }

  /**
   * Initialisation de la page
   * @returns {Promise<Node>} Noeud HTML de la page
   */
  async init() {
    const templateElement = document.createElement('div');
    const response = await fetch(this.template);
    templateElement.innerHTML = await response.text();

    return templateElement.querySelector('template').content.cloneNode(true);
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
