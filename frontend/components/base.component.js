import Config from '../config.js';
import AlertComponent from './alert.component.js';
import UserModel from '../models/user.model.js';

export default class BaseComponent {
  constructor() {
    this.alertController = new AlertComponent();
    this.user = new UserModel();
    this.template = '';
    this.loader = document.querySelector('.loader');

    document.body.addEventListener('login', (event) => {
      this.user = event.detail;
    });
  }

  /**
   * Initialisation de la vue
   */
  async setupView(path) {
    this.template = Config.getTemplate(path);
  }
}
