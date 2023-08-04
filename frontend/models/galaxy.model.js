import SolarSystemModel from './solarsystem.model.js';

export default class GalaxyModel {
  constructor(galaxy) {
    this.id = 0;
    this.name = '';
    this.solarSystems = [];

    this.fromArray(galaxy);
  }

  fromArray(galaxy) {
    if (galaxy) {
      this.id = galaxy.id ?? this.id;
      this.name = galaxy.name ?? this.name;

      if (galaxy.solar_systems) {
        this.solarSystems = galaxy.solar_systems.map((solarSystems) => new SolarSystemModel(solarSystems));
      }
    }
  }
}
