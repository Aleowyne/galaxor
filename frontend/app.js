import MainController from './controllers/main.controller.js';
import HomeController from './controllers/home.controller.js';
import UniverseController from './controllers/universe.controller.js';
import StructureController from './controllers/structure.controller.js';
import ResearchController from './controllers/research.controller.js';
import ErrorController from './controllers/error.controller.js';

class App {
  constructor() {
    this.mainController = new MainController();
    this.errorController = new ErrorController();
    this.homeController = new HomeController();
    this.universeController = new UniverseController();
    this.structureController = new StructureController();
    this.researchController = new ResearchController();
    this.content = document.querySelector('.main-content');

    this.controllers = {
      '': this.homeController,
      error: this.errorController,
      universe: this.universeController,
      structure: this.structureController,
      research: this.researchController,
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
    const controller = this.controllers[path] || this.controllers.error;

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
