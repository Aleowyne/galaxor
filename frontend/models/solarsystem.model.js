import PlanetModel from './planet.model.js';

export default class SolarSystemModel {
  constructor(solarSystem) {
    this.id = 0;
    this.name = '';
    this.planets = [];

    this.fromArray(solarSystem);
  }

  fromArray(solarSystem) {
    if (solarSystem) {
      this.id = solarSystem.id ?? this.id;
      this.name = solarSystem.name ?? this.name;

      if (solarSystem.planets) {
        this.planets = solarSystem.planets.map((planet) => new PlanetModel(planet));
      }
    }
  }
}
