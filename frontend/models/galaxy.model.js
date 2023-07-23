import SolarSystemModel from './solarsystem.model.js';

export default class GalaxyModel {
  constructor(galaxy) {
    this.id = 0;
    this.name = '';
    this.solarsystems = [];

    this.fromArray(galaxy);
  }

  fromArray(galaxy) {
    if (galaxy) {
      this.id = galaxy.id ?? this.id;
      this.name = galaxy.name ?? this.name;

      if (galaxy.solar_systems) {
        this.solarsystems = galaxy.solar_systems.map((solarsystem) => new SolarSystemModel(solarsystem));
      }
    }
  }
}
