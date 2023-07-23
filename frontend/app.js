import MainComponent from './components/main.component.js';
import HomeComponent from './components/home.component.js';
import UniverseComponent from './components/universe.component.js';
import ErrorComponent from './components/error.component.js';

class App {
  constructor() {
    this.mainComponent = new MainComponent();
    this.errorComponent = new ErrorComponent();
    this.homeComponent = new HomeComponent();
    this.universeComponent = new UniverseComponent();
    this.content = document.querySelector('.main-content');

    this.components = {
      404: this.errorComponent,
      '': this.homeComponent,
      universe: this.universeComponent,
    };

    window.addEventListener('hashchange', () => {
      this.router();
    });
  }

  /**
   * Routeur
   */
  async router() {
    // Détermination du composant qui sera appelé
    const path = await this.mainComponent.determinePath();

    if (path === 'redirect') {
      return;
    }

    // Initialisation de la vue du composant principal (header, footer, navigation ...)
    this.mainComponent.setupView(path);

    // Détermination du componsant
    const component = this.components[path] || this.components['404'];

    // Construction de la vue du composant
    this.content.innerHTML = '';
    this.content.append(await component.setupView(path));

    // Traitement sur la page
    if (typeof component.process === 'function') {
      component.process();
    }
  }
}

window.onload = () => {
  const app = new App();
  app.router();
};
