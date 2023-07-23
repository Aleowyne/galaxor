import UniverseModel from '../models/universe.model.js';

export default class UniverseController {
  /**
   * Récupération des univers
   * @returns Liste des univers
   */
  static async getUniverses() {
    const response = await fetch('/galaxor/api/universes', {
      method: 'GET',
      headers: {
        'Content-Type': 'application/json',
        'Access-Control-Allow-Origin': '*',
      },
    });

    const json = await response.json();

    if (response.status !== 200) {
      throw json.error;
    }

    return json.universes.map((item) => new UniverseModel(item));
  }

  /**
   * Récupération des données d'un univers
   * @param {number} universeId Identifiant de l'univers
   * @returns Données de l'univers
   */
  static async getUniverse(universeId) {
    const response = await fetch(`/galaxor/api/universes/${universeId}`, {
      method: 'GET',
      headers: {
        'Content-Type': 'application/json',
        'Access-Control-Allow-Origin': '*',
      },
    });

    const json = await response.json();

    if (response.status !== 200) {
      throw json.error;
    }

    return new UniverseModel(json);
  }

  /**
   * Création d'un univers
   */
  static async createUniverse() {
    const response = await fetch('/galaxor/api/universes', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Access-Control-Allow-Origin': '*',
      },
    });

    const json = await response.json();

    if (response.status !== 200) {
      throw json.error;
    }

    return new UniverseModel(json);
  }
}
