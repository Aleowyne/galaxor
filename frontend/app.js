import MainController from './controllers/main.controller.js';
import ErrorController from './controllers/error.controller.js';
import HomeController from './controllers/home.controller.js';
import UniverseController from './controllers/universe.controller.js';
import StructureController from './controllers/structure.controller.js';
import ResearchController from './controllers/research.controller.js';
import UnitController from './controllers/unit.controller.js';

class App {
  constructor() {
    this.mainController = new MainController();
    this.content = document.querySelector('.main-content');

    this.controllers = {
      '': new HomeController(),
      error: new ErrorController(),
      universe: new UniverseController(),
      structure: new StructureController(),
      research: new ResearchController(),
      unit: new UnitController(),
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

    // Initialisation de la vue du contrôleur principal
    await this.mainController.setupView(path);

    // Détermination du contrôleur
    const controller = this.controllers[path] || this.controllers.error;

    // Construction de la vue du contrôleur
    this.content.innerHTML = '';
    this.content.append(await controller.setupView(this.mainController));

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
