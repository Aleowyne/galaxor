import UserModel from '../models/user.model.js';

export default class UserController {
  /**
   * Connexion d'un utilisateur
   * @param {string} mailAddress Adresse mail
   * @param {string} password Mot de passe
   * @returns Données de l'utilisateur
   */
  static async login(mailAddress = '', password = '') {
    const response = await fetch('/galaxor/api/users/login', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Access-Control-Allow-Origin': '*',
      },
      body: JSON.stringify({
        mail_address: mailAddress,
        password,
      }),
    });

    const json = await response.json();

    if (response.status !== 200) {
      throw json.error;
    }

    return new UserModel(json);
  }

  /**
   * Inscription d'un utilisateur
   * @param {string} mailAddress Adresse mail
   * @param {string} name Nom
   * @param {string} password Mot de passe
   */
  static async signup(mailAddress = '', name = '', password = '') {
    const response = await fetch('/galaxor/api/users/register', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Access-Control-Allow-Origin': '*',
      },
      body: JSON.stringify({
        mail_address: mailAddress,
        name,
        password,
      }),
    });

    if (response.status !== 200) {
      const json = await response.json();
      throw json.error;
    }
  }

  /**
   * Déconnexion de l'utilisateur
   */
  static async logout() {
    await fetch('/galaxor/api/users/logout', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Access-Control-Allow-Origin': '*',
      },
    });
  }
}
