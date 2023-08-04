import Config from '../config.js';
import AlertComponent from './alert.controller.js';
import UserModel from '../models/user.model.js';

export default class BaseController {
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

  /**
   * Affichage d'une boîte de dialogue
   * @param {string} message Message à afficher
   */
  displayDialog(message) {
    const dialog = document.getElementById('app-dialog');
    const txtDialog = document.getElementById('app-dialog-txt');
    txtDialog.innerHTML = message;
    dialog.style.display = 'flex';
  }

  /**
   * Exécution d'une requête GET HTTP
   * @param {string} url URL de la requête
   * @returns {Promise<any>} Données JSON de la réponse
   */
  async requestGet(url) {
    const response = await fetch(url, {
      method: 'GET',
      headers: {
        'Content-Type': 'application/json',
        'Access-Control-Allow-Origin': '*',
      },
    });

    const json = await response.json();

    if (!response.status.toString().startsWith('2')) {
      throw json.error;
    }

    return json;
  }

  /**
   * Exécution d'une requête POST HTTP
   * @param {string} url URL de la requête
   * @param {object} body Corps de la requête
   * @returns {Promise<any>} Données JSON de la réponse
   */
  async requestPost(url, body = {}) {
    const response = await fetch(url, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Access-Control-Allow-Origin': '*',
      },
      body: JSON.stringify(body),
    });

    if (response.status === 204) {
      return Promise.resolve('');
    }

    const json = await response.json();

    if (!response.status.toString().startsWith('2')) {
      throw json.error;
    }

    return json;
  }

  /**
   * Exécution d'une requête PUT HTTP
   * @param {string} url URL de la requête
   * @param {object} body Corps de la requête
   * @returns {Promise<any>} Données JSON de la réponse
   */
  async requestPut(url, body = {}) {
    const response = await fetch(url, {
      method: 'PUT',
      headers: {
        'Content-Type': 'application/json',
        'Access-Control-Allow-Origin': '*',
      },
      body: JSON.stringify(body),
    });

    if (response.status === 204) {
      return Promise.resolve('');
    }

    const json = await response.json();

    if (!response.status.toString().startsWith('2')) {
      throw json.error;
    }

    return json;
  }
}
