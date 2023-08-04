import MainController from './controllers/main.controller.js';
import HomeController from './controllers/home.controller.js';
import UniverseController from './controllers/universe.controller.js';
import ErrorController from './controllers/error.controller.js';

class App {
  constructor() {
    this.mainController = new MainController();
    this.errorController = new ErrorController();
    this.homeController = new HomeController();
    this.universeController = new UniverseController();
    this.content = document.querySelector('.main-content');

    this.controllers = {
      404: this.errorController,
      '': this.homeController,
      universe: this.universeController,
    };

    window.addEventListener('hashchange', () => {
      this.router();
    });
  }

  /**
   * Routeur
   */
  async router() {
    // Détermination du chemin
    const path = await this.mainController.determinePath();

    if (path === 'redirect') {
      return;
    }

    // Initialisation de la vue du contrôleur principal (header, footer, navigation ...)
    this.mainController.setupView(path);

    // Détermination du contrôleur
    const controller = this.controllers[path] || this.controllers['404'];

    // Construction de la vue du contrôleur
    this.content.innerHTML = '';
    this.content.append(await controller.setupView(path));

    // Traitement sur la page
    if (typeof controller.process === 'function') {
      controller.process();
    }
  }
}

window.onload = () => {
  const app = new App();
  app.router();
};
