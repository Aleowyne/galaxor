export default class ErrorView {
  constructor(template) {
    this.template = template;
  }

  /**
   * Initialisation de la vue
   */
  async init() {
    const templateElement = document.createElement('div');

    const response = await fetch(this.template);
    templateElement.innerHTML = await response.text();

    const template = templateElement.querySelector('template').content.cloneNode(true);

    return template;
  }
}
